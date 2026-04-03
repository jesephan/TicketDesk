# TicketDesk - Architecture & Key Decisions

## Architecture Overview

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── ShowLoginFormController.php     # GET  /login
│   │   │   ├── LoginController.php             # POST /login
│   │   │   └── LogoutController.php            # POST /logout
│   │   └── Ticket/
│   │       ├── IndexTicketController.php       # GET  /
│   │       ├── CreateTicketController.php      # GET  /tickets/create
│   │       ├── StoreTicketController.php       # POST /tickets
│   │       ├── ShowTicketController.php        # GET  /tickets/{ticket}
│   │       ├── EditTicketController.php        # GET  /tickets/{ticket}/edit
│   │       └── UpdateTicketController.php      # PUT  /tickets/{ticket}
│   ├── Requests/
│   │   ├── StoreTicketRequest.php              # Validation for ticket creation
│   │   └── UpdateTicketRequest.php             # Validation for ticket updates
│   └── Resources/
│       └── TicketResource.php                  # API response formatting
├── Jobs/
│   └── GenerateTicketSummaryJob.php            # Queued job for AI summary generation
├── Models/
│   ├── Ticket.php                              # Ticket model (ULIDs, $guarded = [])
│   └── User.php                                # User model with is_super_admin role
├── Modules/
│   ├── Helpers/
│   │   └── EscalationHelper.php                # Escalation rules + shouldEscalate logic
│   └── Services/
│       ├── AI/
│       │   ├── AiServiceInterface.php          # Contract for all AI providers
│       │   ├── ClaudeAiService.php             # Claude API (Anthropic SDK)
│       │   ├── OpenAiService.php               # OpenAI API (openai-php/client)
│       │   └── FallbackAiService.php           # Rule-based fallback (no external API)
│       └── TicketService.php                   # Core ticket business logic (DB operations)
└── Providers/
    └── AppServiceProvider.php                  # Binds AiServiceInterface to provider via config
```

## Key Decisions

1. **Single-Action (Invokable) Controllers**: Every controller has exactly one `__invoke()` method and handles one route. This was a deliberate refactor from a traditional resource controller. Benefits:
   - **Single Responsibility**: Each controller does one thing — easy to read, reason about, and test in isolation.
   - **Painless Refactoring**: Adding middleware, rate limiting, or authorization to a single endpoint means editing one file, not guarding against side effects in a shared class.
   - **No Constructor Bloat**: Dependencies are injected per-method via `__invoke()`, so each controller only resolves what it actually needs — a view-only controller doesn't pull in `TicketService`.
   - **Flat Navigation**: File names map directly to actions (`StoreTicketController`, `IndexTicketController`), making it trivial to locate the code for any route.
   - **Safe Deletion**: Removing an endpoint means deleting one file and one route line — no risk of breaking sibling methods in a shared controller.

2. **Modules Structure (`app/Modules/Services` & `app/Modules/Helpers`)**: Services handle DB/external API interactions (TicketService, AI services). Helpers are stateless utility classes with static methods containing pure business logic (escalation rules). This separates side effects from logic and removes unnecessary instantiation.

3. **AI Provider Interface with Container Resolution**: All AI services implement `AiServiceInterface`. The `AppServiceProvider` resolves the correct implementation based on `AI_PROVIDER` env var (`claude`, `openai`, or default to `FallbackAiService`). Claude and OpenAI services gracefully fall back to `FallbackAiService` on API errors.

4. **Queued AI Processing**: AI summary generation runs in a queued job (`GenerateTicketSummaryJob`) so ticket creation/updates are not blocked by external API calls. The job has 3 retries with 30s backoff.

5. **Fallback AI Service**: When no AI provider is configured (or `AI_PROVIDER` is unset/unknown), `FallbackAiService` generates rule-based summaries using config-driven priority actions and category prefixes (`config/ticket.php`).

6. **Dual Response (HTML + JSON)**: Controllers use `$request->wantsJson()` to serve both the web UI and a clean JSON API from the same routes. The `TicketResource` formats API responses consistently. Store returns `201` for JSON.

7. **Role-Based Ticket Scoping**: Users with `is_super_admin = true` see all tickets. Regular users see only their own tickets. This is enforced in `TicketService::list()`.

8. **Escalation Logic** (in `EscalationHelper`): Tickets auto-escalate when: (a) priority is `critical`, (b) priority is `high` and past due date, or (c) open for more than 3 days. Both `shouldEscalate()` and `checkAndEscalate()` are static methods — called directly without instantiation (e.g. `EscalationHelper::checkAndEscalate($ticket)`).

9. **Simple Auth**: No registration or password reset — just session-based login. The `auth` middleware protects all ticket routes.

10. **Simple Pagination**: Uses `simplePaginate()` for better performance (no total count query). Per-page value is capped at 500 by `TicketService`. UI supports per-page selection with Previous/Next navigation.

11. **CDN-based Frontend**: Tailwind CSS loaded via CDN (`cdn.tailwindcss.com`). No `npm install` or build step required — just `composer install` and go.

12. **ULIDs as Primary Keys (Security via Obscurity)**: Tickets use ULIDs (`HasUlids` trait) instead of auto-incrementing integers. With sequential IDs, an attacker viewing `/tickets/42` can trivially enumerate `/tickets/1` through `/tickets/41` — revealing total ticket count, creation rate, and potentially accessing unauthorized records via IDOR (Insecure Direct Object Reference). ULIDs like `01JRC5XKAB3E7QZ...` are 26-character, non-guessable identifiers that eliminate casual enumeration. This is not a substitute for proper authorization (which should still exist), but it removes a low-effort attack surface. ULIDs also remain time-sortable (unlike UUIDv4), making them practical as primary keys without sacrificing index performance from random insertion.

13. **FormRequest Validation**: `StoreTicketRequest` and `UpdateTicketRequest` handle validation with proper error messages, keeping controllers clean. JSON requests return `422` with structured validation errors.

## Sample Login

- **Email:** admin@example.com (super admin — sees all tickets)
- **Password:** password

Regular seeded users can only see their own tickets.

## Environment Configuration

```env
AI_PROVIDER=claude          # Options: claude, openai, or omit for fallback
ANTHROPIC_API_KEY=          # Required when AI_PROVIDER=claude
OPENAI_API_KEY=             # Required when AI_PROVIDER=openai
```

## API Usage

All ticket routes accept `Accept: application/json` header to return JSON responses.

```bash
# List tickets (with optional filters and per_page)
curl -H "Accept: application/json" -b cookies.txt \
  "http://localhost:8083/?status=open&priority=high&per_page=25"

# Create ticket
curl -X POST -H "Accept: application/json" -b cookies.txt http://localhost:8083/tickets \
  -d "title=Bug&description=Something broke badly&priority=high&category=bug"

# View ticket (ULID)
curl -H "Accept: application/json" -b cookies.txt http://localhost:8083/tickets/01JRC5X...

# Update ticket (ULID)
curl -X PUT -H "Accept: application/json" -b cookies.txt http://localhost:8083/tickets/01JRC5X... \
  -d "status=in_progress"
```

## Test Coverage

82 tests with 180 assertions covering:

- **Unit**: Ticket model casts/relations, EscalationHelper (shouldEscalate + checkAndEscalate), AI services (Claude, OpenAI, Fallback, container resolution), GenerateTicketSummaryJob
- **Feature**: Auth (login/logout/redirect), Ticket CRUD (HTML + JSON), validation (required fields, enums, date rules), filtering (status/category/priority/combined), pagination (per_page, simple pagination), role-based scoping (super admin vs regular user)

## Trade-offs

### ULIDs vs Auto-Incrementing IDs
**Chose ULIDs.** They are time-sortable (unlike UUIDv4), URL-safe, and don't leak business information (record count, creation rate). The trade-off is slightly larger storage (26 chars vs 8 bytes for bigint), slower index lookups on very large tables due to string comparison, and less human-friendly identifiers. For a ticketing system at this scale, the security and portability benefits outweigh the performance cost. If tickets needed cross-system references or eventual database merges, ULIDs make that trivial.

### Database as Search vs Dedicated Search Engine
**Chose database `WHERE` clauses.** Filtering by status, category, and priority uses simple indexed enum columns — no full-text search required. This avoids the operational overhead of Meilisearch/Elasticsearch for what are exact-match filters. The trade-off: free-text search across ticket titles and descriptions would perform poorly with `LIKE '%term%'` at scale. If full-text search becomes a requirement, Meilisearch (already in the Docker stack) should be integrated.

### CDN Tailwind vs Build Pipeline
**Chose CDN.** Eliminates the `npm install` / `vite build` step entirely, making setup a single `composer install`. The trade-off: the CDN script is ~300KB (larger than a purged production build), not suitable for production, and doesn't support custom Tailwind config. For a prototype/demo app, zero-config convenience wins. For production, switch to the Vite build pipeline already configured in the project.

### Simple Pagination vs Full Pagination
**Chose `simplePaginate()`.** Avoids the `COUNT(*)` query that `paginate()` runs on every request — meaningful at scale since count queries can be expensive on large InnoDB tables. The trade-off: no total count or "page X of Y" in the UI, just Previous/Next. For a ticket list that users browse sequentially, this is the right fit.

### Queued AI vs Synchronous AI
**Chose queued.** AI API calls (Claude/OpenAI) can take 1-5 seconds. Running them synchronously would block ticket creation and degrade UX. The trade-off: tickets are created without AI summaries initially — the summary appears after the job processes. For a ticketing system where summaries are informational (not blocking), eventual consistency is acceptable.

### Single `is_super_admin` Flag vs RBAC
**Chose a simple boolean.** The app has exactly two permission levels: see all tickets, or see your own. A full RBAC system (roles table, permissions table, pivot tables) would be overengineered. The trade-off: adding a third role (e.g. "team lead sees team tickets") would require a refactor. If roles grow beyond two, migrate to Spatie permissions or a policy-based approach.

### Static Helpers vs Injectable Services
**Chose static methods for helpers.** Helpers like `EscalationHelper` are pure functions — no state, no dependencies, no side effects worth mocking. Static calls are simpler and avoid unnecessary container resolution. The trade-off: static methods are harder to mock in tests if you need to stub their behavior. Since escalation logic is deterministic and fast, testing it directly (not mocking) is the better approach.

### Invokable Controllers vs Resource Controllers
**Chose single-action controllers.** Each file handles one route, one responsibility. The trade-off: more files (9 controllers vs 2), and route definitions are explicit instead of using `Route::resource()`. For a small app this is more files than needed, but as the app grows, each endpoint can evolve independently without merge conflicts or bloated classes.

## What I Would Improve With More Time

- **Authentication**: Add API token auth (Laravel Sanctum) for proper API access
- **Authorization**: Add policies so users can only edit their own tickets
- **Search**: Integrate Meilisearch or opensearch for full-text ticket search
- **Notifications**: Email/Slack notifications on ticket status changes or escalation
- **Audit log**: Track all ticket changes with a polymorphic activity log
- **Ticket comments**: Allow threaded discussions on each ticket
