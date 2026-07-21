# Contributing

## Branches

| Branch | Role | Merges from |
|---|---|---|
| `main` | Production. Every commit is releasable. | `release/*`, `hotfix/*` |
| `develop` | Integration. Latest completed work. | `feature/*` |

Never push directly to `main` or `develop`. Everything goes through a pull
request.

| Type | Branches from | Merges into |
|---|---|---|
| `feature/*` | `develop` | `develop` |
| `release/*` | `develop` | `main` and `develop` |
| `hotfix/*` | `main` | `main` and `develop` |

`fix/`, `refactor/`, `chore/`, `docs/` and `test/` follow the feature path.
`hotfix` is the only branch type that starts from `main`.

## Feature workflow

```bash
git checkout develop && git pull
git checkout -b feature/otp-authentication

make check
git push -u origin feature/otp-authentication
```

Open a pull request into `develop`. Squash on merge, then delete the branch.

## Releases

```bash
git checkout -b release/1.0.0 develop
# stabilise only, no new features
# merge into main, then tag
git tag backend-v1.0.0
git tag mobile-v1.0.0+1
# merge main back into develop
```

## Commits

`type(scope): imperative summary`

| Type | When |
|---|---|
| `feat` | New capability |
| `fix` | Bug fix |
| `refactor` | Restructure without behaviour change |
| `chore` | Tooling and infrastructure |
| `docs` | Documentation |
| `test` | Tests |

Scopes start with the area: `api/*`, `site/*`, `admin/*`, `employer/*`,
`mobile/*`, `db`, `ci`, `docs`.

```
feat(api/jobs): add nearby search with radius filter
fix(mobile/auth): stop otp resend timer leaking after dispose
```

One logical change per commit. The subject is imperative and unpunctuated. The
body explains why; the diff already shows what.

## Pull requests

- Describe the change and the reasoning
- Screenshots or a recording for any UI change
- `make check` green for backend work
- `make m-analyze` and `make m-test` green for mobile work
- Architecture tests pass — they block the merge
- No commented-out code or leftover debug logging
- Documentation updated in the same pull request when architecture changes

Changes to `contracts/openapi.yaml` require review from both the backend and
mobile sides.

## Rules

- Never force-push a shared branch
- Never commit secrets; they come from CI
- Rebase onto `develop` before opening a pull request and resolve conflicts
  locally
- Rebase long-running branches onto `develop` regularly
