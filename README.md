# Название проекта: СДО

Бэкенд часть сервиса СДО

## 🚀 **Технологии и стек**

- **Фреймворк**: [Laravel](https://laravel.com/)
- **БД**: [MySQL](https://www.mysql.com/)
- **Auth**: [Sanctum](https://laravel.com/docs/12.x/sanctum#main-content)
- **LDAP**: [LdapRecord](https://ldaprecord.com/)

## 📂 **Структура проекта**

```
├── app/                    
│   ├── Http/               
│   │   └── Controllers/    # Контроллеры 
│   ├── Models/             # Модели
│   │   ├── Edo/            
│   │   ├── Education/      
│   │   └── User/           
├── routes/                 
│   └── api.php/            # Роутинг
└── .env                    # Настройки БД и сервера
```

## ⚙️ **Установка и запуск**

1. Клонировать репозиторий:

   ```bash
   git clone git@gitlab.com:KarameLka_xd/sdo_frontend.git
   ```

2. Установить зависимости:

   ```bash
   npm install
   ```

3. Запустить dev-сервер:

   ```bash
   npm run dev
   ```

4. Собрать production-версию:
   ```bash
   npm run build
   ```
