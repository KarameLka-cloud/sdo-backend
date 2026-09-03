<?php

namespace App\Services\Ldap;

use LdapRecord\Container;
use LdapRecord\LdapInterface;
use RuntimeException;

/**
 * Directory search on top of the shared LdapRecord connection.
 *
 * Only the filter building and result shaping live here; connecting,
 * binding and host failover are delegated to LdapRecord so the directory
 * and LDAP authentication share one configuration.
 */
class LDAPNavigator
{
    private array $attributeList = [
        'cn' => 'Имя:',
        'description' => 'Должность:',
        'department' => 'Отдел:',
        'l' => 'Город:',
        'streetaddress' => 'Адрес:',
        'telephonenumber' => 'Телефон:',
        'mail' => 'Эл. почта:',
    ];

    private const ENGLISH_KEYBOARD = 'qwertyuiop[]asdfghjkl;\'zxcvbnm,./QWERTYUIOP{}ASDFGHJKL:"ZXCVBNM<>?';

    private const RUSSIAN_KEYBOARD = 'йцукенгшщзхъфывапролджэячсмитьбю.ЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ,';

    /** @var array<string, int>|null */
    private static ?array $charOrderMap = null;

    /** @var array<string, string>|null */
    private static ?array $enToRuMap = null;

    /**
     * "Менеджер + Иркутск 1" → [["Менеджер"], ["Иркутск", "1"]]
     * "Менеджер Иркутск Гоголя" → [["Менеджер"], ["Иркутск"], ["Гоголя"]]
     *
     * Сегменты через + — AND. Слова внутри сегмента должны быть в одном атрибуте.
     *
     * @return list<list<string>>
     */
    public function parseSearchGroups(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $hasSeparators = (bool) preg_match('/[+;|]/u', $query);

        if ($hasSeparators) {
            $segments = preg_split('/\s*[+;|]+\s*/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } else {
            $segments = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $groups = [];
        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }

            if ($hasSeparators) {
                $words = preg_split('/\s+/u', $segment, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if ($words !== []) {
                    $groups[] = array_values($words);
                }
            } else {
                $groups[] = [$segment];
            }
        }

        return $groups;
    }

    private function enToRuMap(): array
    {
        if (self::$enToRuMap === null) {
            $english = preg_split('//u', self::ENGLISH_KEYBOARD, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $russian = preg_split('//u', self::RUSSIAN_KEYBOARD, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            self::$enToRuMap = array_combine($english, $russian) ?: [];
        }

        return self::$enToRuMap;
    }

    private function changeEnglishKeyboardLayout(string $search): string
    {
        $map = $this->enToRuMap();
        $letters = preg_split('//u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode('', array_map(
            static fn (string $letter): string => $map[$letter] ?? $letter,
            $letters
        ));
    }

    private function testEnglishKeyboardLayout(string $search): bool
    {
        $pattern = '/^['.preg_quote(self::ENGLISH_KEYBOARD, '/').']*$/';

        return (bool) preg_match($pattern, $search);
    }

    private function escapeFilterValue(string $search): string
    {
        return ldap_escape($search, '', LDAP_ESCAPE_FILTER);
    }

    private function createCondition($name, $search)
    {
        if ($this->testEnglishKeyboardLayout($search)) {
            return sprintf(
                '(|(%s=*%s*)(%s=*%s*))',
                $name,
                $this->escapeFilterValue($search),
                $name,
                $this->escapeFilterValue($this->changeEnglishKeyboardLayout($search))
            );
        }

        return sprintf('(%s=*%s*)', $name, $this->escapeFilterValue($search));
    }

    /**
     * @return list<string>
     */
    private function searchAttributeNames(): array
    {
        return [
            'cn',
            'givenname',
            'telephonenumber',
            'description',
            'department',
            'streetAddress',
            'l',
            'mail',
            'samaccountname',
        ];
    }

    /**
     * Одно слово — в любом атрибуте.
     * Несколько слов сегмента ("Иркутск 1") — все слова в одном атрибуте
     * или целая фраза целиком.
     *
     * @param  list<string>  $words
     */
    private function createGroupCondition(array $words): string
    {
        $words = array_values(array_filter($words, static fn ($word) => $word !== ''));
        if ($words === []) {
            return '';
        }

        $attributeFilters = [];

        foreach ($this->searchAttributeNames() as $attribute) {
            if (count($words) === 1) {
                $attributeFilters[] = $this->createCondition($attribute, $words[0]);

                continue;
            }

            $phrase = implode(' ', $words);
            $andParts = [];
            foreach ($words as $word) {
                $andParts[] = $this->createCondition($attribute, $word);
            }

            $attributeFilters[] = '(|'
                .$this->createCondition($attribute, $phrase)
                .'(&'.implode('', $andParts).')'
                .')';
        }

        return '(|'.implode('', $attributeFilters).')';
    }

    /**
     * @param  list<string>|list<list<string>>  $searchList
     *
     * @throws \Exception
     */
    public function search(array $searchList, $withPhoto)
    {
        $searchStringList = [];

        foreach ($searchList as $search) {
            $words = is_array($search) ? $search : [$search];
            $condition = $this->createGroupCondition($words);
            if ($condition !== '') {
                $searchStringList[] = $condition;
            }
        }

        $value = '(&(objectcategory=person)'
            .'(|(objectclass=user)(objectclass=contact))'
            .'(!(userAccountControl:1.2.840.113556.1.4.803:=2))'
            .'(!(samaccountname=adm.*))'
            .implode('', $searchStringList)
            .'(|(telephonenumber=*)(mail=*))'
            .')';

        $attributes = array_keys($this->attributeList);

        if ($withPhoto) {
            $attributes[] = 'thumbnailphoto';
        }

        $ldap = $this->connection();

        $searchResult = $ldap->search($this->baseDn(), $value, $attributes);

        if (! $searchResult) {
            throw new RuntimeException($ldap->getLastError() ?? 'LDAP search failed.');
        }

        $entries = $ldap->getEntries($searchResult);

        unset($entries['count']);

        usort($entries, function ($a, $b) {
            return $this->compare($a['cn'][0] ?? '', $b['cn'][0] ?? '');
        });

        return $this->formatEntriesForJson($entries, $withPhoto);
    }

    private function formatEntriesForJson(array $entries, bool $withPhoto): array
    {
        $formatted = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $item = [];

            foreach (array_keys($this->attributeList) as $attribute) {
                if (! isset($entry[$attribute][0])) {
                    continue;
                }
                $item[$attribute] = $this->toUtf8String($entry[$attribute][0]);
            }

            if ($withPhoto && isset($entry['thumbnailphoto'][0])) {
                $item['thumbnailphoto'] = base64_encode($entry['thumbnailphoto'][0]);
            }

            if ($item !== []) {
                $formatted[] = $item;
            }
        }

        return $formatted;
    }

    private function toUtf8String(mixed $value): string
    {
        if (! is_string($value)) {
            return (string) $value;
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        foreach (['Windows-1251', 'ISO-8859-1'] as $encoding) {
            $converted = @mb_convert_encoding($value, 'UTF-8', $encoding);
            if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return $cleaned !== false ? $cleaned : '';
    }

    /** @return array<string, int> */
    private static function charOrderMap(): array
    {
        if (self::$charOrderMap === null) {
            $chars = [
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                'а', 'б', 'в', 'г', 'д', 'е', 'ё',
                'ж', 'з', 'и', 'й', 'к', 'л', 'м',
                'н', 'о', 'п', 'р', 'с', 'т', 'у',
                'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ',
                'ы', 'ь', 'э', 'ю', 'я',
            ];
            self::$charOrderMap = array_flip($chars);
        }

        return self::$charOrderMap;
    }

    private static function compare($a, $b): int
    {
        $orderMap = self::charOrderMap();

        $a = mb_strtolower($a);
        $b = mb_strtolower($b);

        $aLength = mb_strlen($a);
        $bLength = mb_strlen($b);

        for ($i = 0; $i < $aLength && $i < $bLength; $i++) {
            $aChar = mb_substr($a, $i, 1);
            $bChar = mb_substr($b, $i, 1);
            $aCharOrder = $orderMap[$aChar] ?? PHP_INT_MAX;
            $bCharOrder = $orderMap[$bChar] ?? PHP_INT_MAX;

            if ($aCharOrder !== $bCharOrder) {
                return $aCharOrder - $bCharOrder;
            }
        }

        return $aLength - $bLength;
    }

    /**
     * Bound connection configured by `config/ldap.php`, shared with LDAP auth.
     */
    private function connection(): LdapInterface
    {
        $connection = Container::getConnection('default');

        if (! $connection->isConnected()) {
            $connection->connect();
        }

        return $connection->getLdapConnection();
    }

    private function baseDn(): string
    {
        return (string) Container::getConnection('default')
            ->getConfiguration()
            ->get('base_dn');
    }

    public function getAttributeList(): array
    {
        return $this->attributeList;
    }
}
