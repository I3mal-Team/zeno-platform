# Contributing

## Branches

| Branch | Role |
|---|---|
| `main` | Production. Every commit is releasable. |
| `develop` | Integration and active development. |

Day-to-day work commits directly to `develop`. `main` only ever receives a
`release/*` or `hotfix/*` merge, so the released state stays distinguishable
from work in progress.

Feature branches are optional and worth it when a change is large enough to
want reviewing as a unit, or risky enough that you may want to abandon it.

| Type | Branches from | Merges into |
|---|---|---|
| `release/*` | `develop` | `main` and `develop` |
| `hotfix/*` | `main` | `main` and `develop` |
| `feature/*` | `develop` | `develop` |

`hotfix` is the only branch type that starts from `main`.

## Before pushing

Run the gates locally. Pushing straight to `develop` means a red pipeline is
already on the integration branch.

```bash
make check      # pint, phpstan, pest
make m-analyze  # flutter analyze
make m-test     # flutter test
```

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

## Rules

- Never force-push `main` or `develop`
- Never commit secrets; they come from CI
- Architecture tests block the merge — they are not advisory
- Documentation changes ship in the same commit as the change they describe
- Changes to `contracts/openapi.yaml` affect both the backend and the app
