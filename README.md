# DELOX Messenger

A web-based real-time messenger application similar to Telegram, built with PHP 8.1+ and MySQL using a custom lightweight MVC framework — no third-party PHP framework required.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Running Locally](#running-locally)
- [Programming Principles](#programming-principles)
- [Design Patterns](#design-patterns)
- [Refactoring Techniques](#refactoring-techniques)

---

## Features

- **User Authentication** — Registration, login, and logout with bcrypt password hashing
- **User Profiles** — View and edit profile info, upload a custom avatar
- **User Search** — Search other users by username or display name
- **Private & Group Chats** — Start one-on-one conversations or group chats
- **Real-Time Messaging** — Long-polling mechanism delivers new messages without page refresh
- **Message History** — Paginated retrieval of chat history with sender details
- **Account Settings** — Change email, password, or permanently delete your account
- **Flash Messages** — Session-based one-time notifications for user feedback
- **Access Control** — Route guards prevent unauthenticated access and redirect authenticated users away from auth pages

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.1+, strict types |
| Database | MySQL 8.0+, PDO with prepared statements |
| Architecture | Custom MVC (no framework) |
| Frontend | Vanilla JavaScript, HTML5, CSS3 |
| Server | Apache with `mod_rewrite` (MAMP) |
| Sessions | PHP native sessions |
| Password hashing | `PASSWORD_BCRYPT` |

---

## Project Structure

```
DELOX/
├── app/
│   ├── Core/               # Framework core (Router, Application, DB, base Controller/Model)
│   ├── Controllers/        # HTTP request handlers
│   ├── Models/             # Domain entities (User, Chat, Message)
│   ├── Repositories/
│   │   ├── Contracts/      # Repository interfaces
│   │   ├── UserRepository.php
│   │   ├── ChatRepository.php
│   │   └── MessageRepository.php
│   ├── Services/           # Business logic layer
│   ├── Middleware/         # Route guards (Auth, Guest)
│   └── Helpers/            # Validator, Session, DateHelper
├── config/                 # App and database configuration
├── public/                 # Web root — entry point (index.php), CSS, JS
├── routes/
│   ├── web.php             # HTML routes
│   └── api.php             # JSON API routes
├── storage/                # Uploaded avatars and media
└── views/                  # PHP HTML templates
    ├── layouts/
    ├── auth/
    ├── chats/
    ├── profile/
    ├── settings/
    └── errors/
```

---

## Running Locally

### Prerequisites

- [MAMP](https://www.mamp.info/) (Apache + MySQL + PHP 8.1+) or any equivalent stack
- The project folder must be placed inside `htdocs/` so it is accessible at `http://localhost/DELOX/`

### Step-by-step

**1. Clone the repository**

```bash
git clone https://github.com/your-username/DELOX.git C:/MAMP/htdocs/DELOX
```

**2. Configure the database connection**

```bash
cp config/database.example.php config/database.local.php
```

Open `config/database.local.php` and fill in your credentials:

```php
return [
    'host'     => 'localhost',
    'port'     => 3306,
    'database' => 'delox_messenger',
    'username' => 'root',
    'password' => 'root',
    'charset'  => 'utf8mb4',
];
```

**3. Create the database and import the schema**

```sql
CREATE DATABASE delox_messenger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import:

```bash
mysql -u root -p delox_messenger < database/schema.sql
```

**4. Enable `mod_rewrite` in Apache**

In MAMP → Preferences → PHP, make sure the Apache version supports `.htaccess`. The repo already ships an `.htaccess` file that rewrites all requests through `public/index.php`.

**5. Start MAMP and open the app**

```
http://localhost/DELOX/
```

Register a new account and start chatting.

---

## Programming Principles

### 1. Single Responsibility Principle (SRP)

Every class in the application has exactly one reason to change. Controllers only handle HTTP input/output; all business rules live in Services; all database queries live in Repositories. For example, `AuthController` delegates login logic entirely to `AuthService`, and `AuthService` delegates persistence to `UserRepository`.

### 2. Dependency Inversion Principle (DIP)

High-level modules (Services) do not depend on low-level modules (concrete Repositories). Both depend on abstractions. Each repository implements a contract defined in `app/Repositories/Contracts/` (e.g., `UserRepositoryInterface`), so a Service only needs to know the interface, not the concrete class.

### 3. DRY — Don't Repeat Yourself

Common functionality is extracted into shared base classes and helpers. All controllers extend `app/Core/Controller.php` to gain shared `view()`, `json()`, and `redirect()` helpers. All models extend `app/Core/Model.php` for shared PDO access. Form validation logic is centralised in `app/Helpers/Validator.php` with a fluent API.

### 4. Open/Closed Principle (OCP)

The routing system and middleware pipeline are open for extension without modifying existing code. New routes are added to `routes/web.php` or `routes/api.php`; new middleware is created as a standalone class and attached to a route — the `Router` and `Application` core classes need no changes.

### 5. Separation of Concerns (SoC)

The application is split into clearly separated layers: routing, middleware, controllers, services, repositories, and views. A view template never queries the database; a repository never knows about HTTP; a controller never contains SQL. This keeps each layer independently testable and replaceable.

### 6. KISS — Keep It Simple, Stupid

The project deliberately avoids over-engineering. There is no ORM, no IoC container, no event bus — just plain PHP classes with explicit constructor injection. The long-polling mechanism in `public/js/chat.js` uses a simple `setInterval` loop rather than WebSockets, which would be disproportionate for a learning project.

### 7. YAGNI — You Aren't Gonna Need It

Only features explicitly required were implemented. For instance, the `Message` model exposes a `toArray()` method for JSON serialisation, but no serialisation framework or annotation system was introduced because a single utility method is sufficient for the current scope.

---

## Design Patterns

### 1. Singleton — [`app/Core/Database.php`](app/Core/Database.php)

The `Database` class uses the Singleton pattern to ensure that only one PDO connection is created for the entire request lifecycle. All repositories and models receive the same connection instance via `Database::getInstance()`, preventing the overhead of multiple connections and avoiding connection-limit errors under concurrent load.

```php

public static function getInstance(): self
{
    if (self::$instance === null) {
        self::$instance = new self();
    }
    return self::$instance;
}
```

### 2. Repository — [`app/Repositories/`](app/Repositories/)

The Repository pattern abstracts all data access behind interface contracts defined in [`app/Repositories/Contracts/`](app/Repositories/Contracts/). Services depend on interfaces (`UserRepositoryInterface`, `ChatRepositoryInterface`, `MessageRepositoryInterface`), not on concrete classes. This decouples business logic from SQL, makes repositories independently testable with mock implementations, and allows swapping the data source without touching service code.

```
UserRepositoryInterface  ←  UserRepository  ← (MySQL/PDO)
ChatRepositoryInterface  ←  ChatRepository
MessageRepositoryInterface ← MessageRepository
```

### 3. Front Controller — [`app/Core/Application.php`](app/Core/Application.php) + [`app/Core/Router.php`](app/Core/Router.php)

All HTTP requests are funnelled through a single entry point — `public/index.php` — which bootstraps the `Application` and invokes the `Router`. The `Router` matches the request URI against registered routes using regex patterns and dispatches to the appropriate controller action, applying middleware before the controller runs. No page is accessible without passing through this central dispatcher.

### 4. Factory Method — [`app/Models/User.php`](app/Models/User.php), [`app/Models/Chat.php`](app/Models/Chat.php), [`app/Models/Message.php`](app/Models/Message.php)

Each domain model exposes a static `fromArray(array $data)` factory method that constructs a typed, readonly model object from a raw database row. This centralises object creation, hides property-mapping details from callers, and ensures that all code receives a consistent, type-safe model rather than a plain associative array.

```php
public static function fromArray(array $data): self
{
    return new self(
        id: (int) $data['id'],
        username: $data['username'],
    );
}
```

### 5. Template Method — [`app/Core/Controller.php`](app/Core/Controller.php)

The abstract base `Controller` defines the common skeleton for all HTTP handlers: it provides `view()`, `json()`, and `redirect()` helper methods that every concrete controller inherits. Concrete controllers fill in only the handler-specific logic; the infrastructure (response construction, view rendering) never needs to be repeated.

---

## Refactoring Techniques

### 1. Extract Method

Long procedural sequences were broken into focused, named methods. For example, avatar upload validation in `UserService` was extracted from a large `updateProfile()` procedure into a dedicated `uploadAvatar()` method, making each operation independently readable and testable.

### 2. Extract Class

Business logic that initially lived inside controllers was extracted into dedicated Service classes (`AuthService`, `ChatService`, `MessageService`, `UserService`, `SettingsService`). Similarly, raw SQL that started in controllers was moved to Repository classes. Each extracted class has a single focused responsibility.

### 3. Introduce Interface / Program to an Abstraction

Concrete repository classes were refactored to implement interfaces (`UserRepositoryInterface`, `ChatRepositoryInterface`, `MessageRepositoryInterface`). Services now depend on the interface type, not the concrete class — enabling substitution with mock or alternative implementations without changing service code.

### 4. Replace Magic Number with Named Constant

Inline numeric literals were replaced with named constants or clearly named variables. The maximum allowed message length (`4096` characters) is validated in `MessageService` with an explicit limit check, making the constraint self-documenting and easy to locate and change.

### 5. Move Method to Appropriate Class

Formatting and display logic was moved from views and controllers into the domain models and helpers where it belongs. For example, `Chat::displayName()`, `Chat::avatarUrl()`, and `Chat::initial()` were moved into the `Chat` model so views can call a clean interface rather than duplicating formatting logic.

### 6. Introduce Parameter Object / Value Object

Raw `$_POST` and `$_FILES` data is wrapped in a `Request` object (`app/Core/Request.php`) that provides typed accessors (`input()`, `file()`, `isPost()`, etc.). Controllers receive a clean `Request` instead of reading superglobals directly, making request handling uniform and easier to test.

### 7. Replace Conditional with Guard Clause

Nested `if` chains inside controllers and services were flattened using early-return guard clauses. Middleware classes (`AuthMiddleware`, `GuestMiddleware`) apply a single redirect guard at the top of the request and exit immediately, avoiding deep nesting for the happy path.

### 8. Consolidate Duplicate Conditional Fragments

Repetitive flash-message and redirect patterns in controllers were consolidated by centralising session flash handling in `app/Helpers/Session.php` with `Session::flash()` and `Session::getFlash()`, eliminating scattered one-off `$_SESSION` writes throughout the codebase.
