# DELOX Messenger

A web-based messenger application similar to Telegram, built with PHP + MySQL.

## Tech Stack

- **Backend:** PHP 8.1+, MVC architecture
- **Database:** MySQL 8.0+
- **Frontend:** Vanilla JavaScript, HTML5, CSS3
- **Patterns:** Singleton, Repository, Service Layer, Front Controller

## Setup

1. Clone the repository into your MAMP `htdocs` directory
2. Copy `config/database.example.php` to `config/database.local.php` and fill in credentials
3. Import `database/schema.sql` into MySQL
4. Make sure mod_rewrite is enabled in Apache
5. Access via `http://localhost/DELOX/`

## Project Structure

```
DELOX/
├── app/
│   ├── Core/           # Framework core (Router, DB, Controller...)
│   ├── Controllers/    # Request handlers
│   ├── Models/         # Domain entities
│   ├── Repositories/   # Data access layer
│   ├── Services/       # Business logic
│   └── Middleware/     # HTTP middleware
├── config/             # Configuration files
├── database/           # SQL schema and migrations
├── public/             # Web root (entry point)
├── routes/             # Route definitions
├── storage/            # Uploaded files
└── views/              # HTML templates
```

## Features

- User authentication (register / login / logout)
- User profiles with avatars
- Contacts / user search
- Private and group chats
- Real-time messaging (long polling)
- Account settings
