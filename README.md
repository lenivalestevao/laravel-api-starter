# 🚀 Laravel API Starter (REST API with Sanctum)

<img src="docs/preview.svg?v=1" alt="Endpoints and a sample JSON response" width="100%">

<sub>Preview: illustration of the endpoints and a sample response. Run the API to see the real thing.</sub>

A free Laravel 12 starter for a JSON REST API: **token authentication with Sanctum, a Projects and Tasks CRUD, Form Request validation, API Resources, Policies for ownership, backed enums, rate limiting and a feature test suite**. SQLite out of the box, MySQL or PostgreSQL with one line in `.env`.

It follows the standard Laravel layout and conventions, so you can read it in ten minutes and make it yours.

## Features

- **Auth**: register, login, logout (revokes only the current token) and `me`, using Sanctum personal access tokens with an expiration
- **Projects**: paginated list with `status`, `search` and `per_page` filters, create, show (with its tasks), update, delete
- **Tasks**: nested under a project for list and create, shallow routes (`/tasks/{task}`) for show, update and delete, with `status` and `priority` filters
- **Ownership**: Policies make sure a user can only touch their own projects and tasks (403 otherwise)
- **Validation**: Form Requests return 422 with per-field errors
- **JSON everywhere**: errors, 401, 403, 404 and 422 are always JSON for `/api/*`
- **Rate limits**: 60 requests per minute for the API, 10 per minute for register and login
- **Enums**: `TaskStatus`, `TaskPriority`, `ProjectStatus` as backed enums cast on the models
- **Seeder**, **factories** and a **`requests.http`** file to try every endpoint from VS Code or PhpStorm
- **Tests**: feature tests for auth, projects and tasks, plus unit tests for the enums

## Requirements

- PHP 8.2 or newer (with the `pdo_sqlite` extension for the default setup)
- Composer 2

## Quick start

```bash
git clone https://github.com/lenivalestevao/laravel-api-starter.git
cd laravel-api-starter

composer install          # also creates .env and the SQLite file
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The API is now at `http://localhost:8000/api`. The seeder creates a demo account:

```
email:    demo@example.com
password: password
```

Try it:

```bash
# 1. Log in and copy the token from the response
curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"demo@example.com","password":"password"}'

# 2. Use it
curl -s http://localhost:8000/api/projects \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Always send `Accept: application/json`. A `requests.http` file with every call is included.

## Endpoints

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| POST | `/api/auth/register` | no | Create an account, returns the user and a token |
| POST | `/api/auth/login` | no | Returns the user and a token |
| POST | `/api/auth/logout` | yes | Revokes the current token |
| GET | `/api/auth/me` | yes | The authenticated user |
| GET | `/api/projects` | yes | Your projects. Query: `status`, `search`, `per_page`, `page` |
| POST | `/api/projects` | yes | Create a project |
| GET | `/api/projects/{project}` | yes | One project with its tasks |
| PATCH | `/api/projects/{project}` | yes | Update a project |
| DELETE | `/api/projects/{project}` | yes | Delete a project and its tasks |
| GET | `/api/projects/{project}/tasks` | yes | Tasks of a project. Query: `status`, `priority`, `per_page`, `page` |
| POST | `/api/projects/{project}/tasks` | yes | Create a task |
| GET | `/api/tasks/{task}` | yes | One task |
| PATCH | `/api/tasks/{task}` | yes | Update a task |
| DELETE | `/api/tasks/{task}` | yes | Delete a task |

Allowed values: project `status` is `active` or `archived`. Task `status` is `todo`, `doing` or `done`. Task `priority` is `low`, `medium` or `high`.

Validation errors use the standard Laravel format:

```json
{
  "message": "The name field is required.",
  "errors": { "name": ["The name field is required."] }
}
```

## Project structure

```
app/
  Enums/                     TaskStatus, TaskPriority, ProjectStatus
  Http/
    Controllers/Api/         AuthController, ProjectController, TaskController
    Requests/                Form Requests (validation + authorization)
    Resources/               UserResource, ProjectResource, TaskResource
  Models/                    User, Project, Task
  Policies/                  ProjectPolicy, TaskPolicy
  Providers/                 AppServiceProvider (rate limiters)
bootstrap/app.php            routing, JSON error rendering
config/                      app, auth, cache, database, logging, queue, session, sanctum...
database/
  factories/                 UserFactory, ProjectFactory, TaskFactory
  migrations/                users, personal_access_tokens, projects, tasks
  seeders/DatabaseSeeder.php demo account with projects and tasks
routes/api.php               all API routes
tests/
  Feature/                   AuthTest, ProjectTest, TaskTest
  Unit/                      EnumsTest
requests.http                try every endpoint from your editor
```

## Tests

```bash
composer test
# or
php artisan test
```

The suite uses an in-memory SQLite database (see `phpunit.xml`), so it never touches your data.

This project was written for Laravel 12. If something does not pass on your machine, please open an issue with your PHP version and the error.

## Make it yours

- **Add a resource**: `php artisan make:model Note -mf --policy`, then a controller, two Form Requests and a Resource, following `Project` as the example. Register it with `Route::apiResource(...)` inside the `auth:sanctum` group in `routes/api.php`.
- **Use MySQL or PostgreSQL**: set `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in `.env`, then `php artisan migrate`.
- **Token lifetime**: `SANCTUM_EXPIRATION` in `.env` (minutes). Leave it empty for tokens that never expire.
- **Rate limits**: edit the two limiters in `app/Providers/AppServiceProvider.php`.

If a template saves you time, a ⭐ on the repo helps a lot!

## License

MIT. Free for personal and commercial use.
