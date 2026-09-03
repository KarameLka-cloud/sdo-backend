# СДО — бэкенд

REST API сервиса СДО: адаптация стажёров, обучающие материалы, справочник
сотрудников и управление ролями.

## 🚀 Технологии и стек

- **Фреймворк**: [Laravel](https://laravel.com/)
- **БД**: [MySQL](https://www.mysql.com/) (в тестах — SQLite)
- **Auth**: [Sanctum](https://laravel.com/docs/12.x/sanctum#main-content) (токены)
- **LDAP**: [LdapRecord](https://ldaprecord.com/) — и аутентификация, и справочник
- **Тесты**: [Pest](https://pestphp.com/)
- **Стиль кода**: [Pint](https://laravel.com/docs/pint)

## 📂 Структура проекта

```
sdo-backend/
├── app/
│   ├── Enums/                  # Статусы задач, дней, роли (единый источник значений)
│   ├── Http/
│   │   ├── Controllers/        # Контроллеры (Auth, User, Mentorship, ...)
│   │   ├── Middleware/         # RolePermission — доступ по роли
│   │   └── Requests/           # Валидация входящих данных
│   ├── Models/
│   │   ├── Mentorship/         # Планы адаптации, шаблоны, дни, задачи
│   │   └── User/               # Пользователи, роли, отделы, должности
│   ├── Policies/               # Права на конкретные записи
│   └── Services/
│       ├── Ldap/               # Поиск по справочнику сотрудников
│       ├── Mentorship/         # Логика планов адаптации
│       └── User/               # Разрешение роли пользователя
├── database/
│   ├── migrations/             # Схема БД
│   └── seeders/                # Роли, отделы, должности
├── routes/
│   └── api.php                 # Все эндпоинты API
└── .env                        # Настройки БД, LDAP и сервера
```

## ⚙️ Установка и запуск

1. Клонировать репозиторий и перейти в папку бэкенда:

   ```bash
   git clone <repo-url>
   cd sdo/sdo-backend
   ```

2. Установить зависимости:

   ```bash
   composer install
   ```

3. Создать `.env` и сгенерировать ключ приложения:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Далее заполнить в `.env` доступы к БД (`DB_*`) и LDAP (`LDAP_*`).

4. Применить миграции и заполнить справочники:

   ```bash
   php artisan migrate --seed
   ```

5. Запустить dev-сервер:

   ```bash
   php artisan serve
   ```

   Вариант с очередью в одном процессе: `composer dev`.

## 🔐 Аутентификация

Вход выполняется по логину LDAP (`samaccountname`) через `POST /api/auth/login`.
В ответ приходит токен Sanctum, который нужно передавать в заголовке
`Authorization: Bearer <token>`.

Срок жизни токена задаётся переменной `SANCTUM_TOKEN_EXPIRATION` (в минутах,
по умолчанию 7 дней). Пустое значение отключает истечение токенов.

## 🧪 Проверки

```bash
./vendor/bin/pest              # тесты
./vendor/bin/pint app/ routes/ # автоформатирование
php artisan route:list --path=api
```
