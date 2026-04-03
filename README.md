# TicketDesk

A simple ticketing system built with Laravel. Users can submit, view, filter, and update support tickets with AI-powered summaries.

See [ARCHITECTURE.md](ARCHITECTURE.md) for key decisions, trade-offs, and API usage.

## Prerequisites

- Docker & Docker Compose
- Composer

**No Node.js or npm required** — the frontend uses Tailwind CSS via CDN.

## Quick Start (Laravel Sail)

```bash
# 1. Clone and install dependencies
composer install

# 2. Set up environment
cp .env.example .env    # if .env doesn't exist
php artisan key:generate

# 3. Start containers
./vendor/bin/sail up -d

# 4. Run migrations and seed sample data
./vendor/bin/sail artisan migrate --seed

# 5. Open in browser
open http://localhost:8083
```

## Quick Start (Without Docker)

```bash
# 1. Install dependencies
composer install

# 2. Set up environment
cp .env.example .env
php artisan key:generate

# 3. Configure .env with your database credentials
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_DATABASE=alpha
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Run migrations and seed
php artisan migrate --seed

# 5. Start the server
php artisan serve
```

## AI Setup (Optional)

Set `AI_PROVIDER` in `.env` to enable AI-powered ticket summaries. Without it, the app works fully using a rule-based fallback.

```env
AI_PROVIDER=claude
ANTHROPIC_API_KEY=sk-ant-...
```

Start the queue worker to process AI jobs in the background:

```bash
./vendor/bin/sail artisan queue:work
```

## Running Tests

```bash
# Set up the testing database (first time only)
./vendor/bin/sail artisan migrate:fresh --database=testing

# Run all tests
./vendor/bin/sail artisan test
```

Configure testing database credentials in `.env`:

```env
TEST_DB_HOST=host.docker.internal
TEST_DB_PORT=3306
TEST_DB_DATABASE=cb_testing
TEST_DB_USERNAME=root
TEST_DB_PASSWORD=
```

## Login

| Email | Password | Role |
|:------|:---------|:-----|
| `admin@example.com` | `password` | Super Admin (sees all tickets) |

Other seeded users have password `password` and can only see their own tickets.
