# zeno

A proximity-based job marketplace for Saudi Arabia, focused on operational and
seasonal work: hospitality, cleaning, logistics, events, retail, security,
maintenance and skilled trades.

It connects candidates with employers near them and keeps applying and getting
in touch down to a handful of steps.

## Contents

| Directory | Purpose |
|---|---|
| `backend/` | Laravel API, public site, and two Filament panels |
| `mobile/` | Flutter app for candidates and employers |
| `contracts/` | `openapi.yaml` — the source of truth for the API contract |
| `docs/` | Product and engineering specifications |
| `design/` | Interactive prototype and design handover (reference only) |
| `docker/` | Container definitions |

## Getting started

Requires Docker, and FVM for the mobile app.

```bash
cp backend/.env.example backend/.env
make up
make art c="key:generate"
make migrate
make seed
```

| Service | URL |
|---|---|
| Application | http://localhost:8000 |
| Admin panel | http://localhost:8000/admin |
| Employer dashboard | http://localhost:8000/employer |

Ports are configurable through `.env`; the defaults sit outside the usual range
so the stack can run alongside other projects.

Run `make help` for the full command list.

## Architecture

### Backend

```
Route → FormRequest → Controller → Service → Repository → Model
                          ↓
                    Resource → ApiResponse
```

1. No business logic in controllers
2. No queries in services
3. Validation lives in form requests
4. Responses go through resources and the shared envelope

These are enforced by `tests/Architecture`, which blocks merges. They are not
review conventions.

### Surface separation

Everything above the service layer is split per surface. Everything from the
service down is shared and never duplicated.

```
split:   Routes · Controllers · Requests · Responses · Middleware · Guards · Tests
         ├─ Api/V1   ├─ Site   ├─ Filament/Employer   ├─ Filament/Admin
                              ↓
shared:  Services · Repositories · Models · Data · Enums · Events · Policies
```

Forking the domain per surface is what produced three contradictory schemas in
the prototype, so `app/Services/Api` and friends are rejected by a test.

### Mobile

Feature-first structure, Cubit for state, `get_it` for injection,
`Either<Failure, T>` at repository boundaries, `go_router` for navigation, and a
single `ApiConsumer` abstraction over HTTP. The full rulebook is in
`CLAUDE_TEMPLATE.md`.

## Documentation

Specifications live in `docs/`, covering the domain model and database schema,
architecture and stack, the sprint roadmap, and the open decisions register.

Source material ranks in this order when two sources disagree:

1. The client project description governs business rules
2. The design handover governs UI and design tokens
3. The prototype is mocked throughout and is not production code

Silence in a higher-ranked source is not agreement with a lower one; it means
the decision is still open.

## Workflow

Git Flow. `main` is production, `develop` is integration, and work happens on
`feature/*` branches. See `CONTRIBUTING.md`.

```bash
make check      # pint, phpstan, pest
make m-analyze  # flutter analyze
```

## Confidentiality

Private repository. It contains client-owned specifications and design assets.

Secrets never enter the repository: environment files, signing keys and service
account credentials all come from CI secrets.
