# Graph Mail Core — ProgressiveStudios

Send via Microsoft Graph **REST APIs**, **RabbitMQ listener**, **templates**, and a **web UI**.

Both modes can be enabled independently.

---

## Install

```bash
composer require progressivestudios/laravel-graph-mail

php artisan vendor:publish --tag=graph-mail-config
php artisan vendor:publish --tag=graph-mail-migrations
php artisan vendor:publish --tag=graph-mail-views
php artisan vendor:publish --tag=graph-mail-assets

php artisan migrate
```

Configure `.env` with all required `GRAPH_*` and RabbitMQ settings.

---

## Environment Configuration

### Required `.env`

```
GRAPH_TENANT_ID=
GRAPH_CLIENT_ID=
GRAPH_CLIENT_SECRET=

GRAPH_SENDER=

GRAPH_BASE="https://graph.microsoft.com/v1.0"

GRAPH_SAVE_TO_SENT=true

GRAPH_MAIL_LOG_CHANNEL=graph-mail
GRAPH_API_PREFIX=graph-mail
```

---

## REST Endpoints

- POST `/api/{GRAPH_API_PREFIX}/messages`  
  Accepts:
    - `html`, or
    - `template_key` + `data`

- GET `/api/{GRAPH_API_PREFIX}/messages/{id}`  
  Returns message status.

- GET `/api/{GRAPH_API_PREFIX}/health`  
  Connectivity check.

---

## RabbitMQ (Optional)

Enable:

```
GRAPH_RABBIT_ENABLED=true
```

Start consumer:

```bash
php artisan graph-mail:rabbit:consume
```

Messages published to RabbitMQ are handled by the same processing pipeline as REST and UI requests.

---

## Templates

Templates may come from:

- DB (`mail_templates`)
- Blade views under `resources/views/emails/*`
- Custom mailable classes

Message creation supports:

- `template_key`
- `data`
- Optional `subject` override

Seeder example provided at `database/seeders/MailTemplateSeeder.php`.

## Azure App Permissions (Application)

Required:

- `Mail.Send`

Admin consent is required.

---

## Tooling Included

- OpenAPI spec: `openapi.yaml`
- Postman collection: `postman.collection.json`
- Docker compose (RabbitMQ): `docker-compose.yml`
- Template seeder: `MailTemplateSeeder.php`

---

## Pro Version

A separate commercial package adds:

- Multi-tenant support
- Bulk sending
- Advanced routing
- Analytics & dashboards
- High-volume worker optimizations
- Sent/NDR tracking
- Throttling/backoff
- Webhook subscriptions

Available at:

`progressivestudios/laravel-graph-mail-pro`
