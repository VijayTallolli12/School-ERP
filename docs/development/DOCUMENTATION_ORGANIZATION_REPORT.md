# Documentation Organization Report

Date: 2026-08-04
Scope: Reorganization of all project `.md` documentation (excluding `README.md`, `LICENSE`, `CHANGELOG.md`, `CONTRIBUTING.md`) into a numbered `docs/` structure.
Type: Documentation-only change. **No code, tests, or business logic were modified.**

## Summary

All markdown documentation was moved into a 10-section numbered structure under `docs/`. Root-level loose `.md` files were absorbed into sections; legacy `docs/` subfolders were folded into the new tree; `phase/`, `reports/`, `e2e/`, and `AI_RELEASE/` were split into their logical sections. Project root now contains only `README.md` as a loose markdown file.

## New structure

| Folder | Purpose |
| --- | --- |
| `docs/01_Project/` | Overviews, business rules, guides, constitution, AI conventions |
| `docs/02_Architecture/` | System architecture, design, database schema |
| `docs/03_Release/` | Release status, phase plans, phase reports, release notes |
| `docs/04_Audits/` | Security, performance, RBAC, UI, database, AI audits |
| `docs/05_Modules/` | Module specs, reviews, completion reports, app specs |
| `docs/06_Testing/` | Testing guide, regression/UAT/E2E reports |
| `docs/07_Reports/` | Bug/completion/risk/health reports |
| `docs/08_API/` | API reference and API audits |
| `docs/09_Deployment/` | Deployment, infrastructure, readiness |
| `docs/10_Archive/` | Superseded/duplicate docs + document inventory |

## Files moved

- **~340 markdown files** relocated in total (full catalog in [DOCUMENT_INVENTORY](../10_Archive/DOCUMENT_INVENTORY.md)).
- Root loose files (~60): project/roadmap docs → `01_Project`; audits/reports → `04_Audits` / `07_Reports`; security/performance/RBAC/UI → `04_Audits` subfolders; module docs → `05_Modules`; testing → `06_Testing`; deployment → `09_Deployment`.
- Legacy `docs/` subfolders: `Business`, `CONSTITUTION`, `AI`, `api`, `Security`, `Testing`, `ReleaseNotes`, `Modules`, `Deployment`, `Architecture`, `Database`, `UserGuide`, `AdminGuide`, `Developer` → folded into numbered sections (empty originals removed).
- `phase/` → `docs/03_Release/Phase/` (20 files).
- `reports/` → `docs/03_Release/PhaseReports/` (12 phase folders, 75 files).
- `e2e/` → `docs/06_Testing/e2e/`.
- `AI_RELEASE/` fully split: `MASTER_PROMPT.md` + `RELEASE_STATUS.md` → `03_Release`; module specs/reviews/completion reports → `05_Modules/*`; app specs → `05_Modules/{Parent,Teacher,Student,Driver}`; performance docs → `04_Audits/Performance`. Only `AI_RELEASE/README.docx` (non-markdown) remains in place.

## Folders created

`docs/01_Project` (with `Guides/{UserGuide,AdminGuide,Developer}`, `Constitution`, `AI`), `docs/02_Architecture` (with `Database`), `docs/03_Release` (with `Phase`, `PhaseReports`, `ReleaseNotes`), `docs/04_Audits` (with `Security`, `Performance`, `RBAC`, `UI`), `docs/05_Modules` (with 12 module subfolders), `docs/06_Testing` (with `e2e`), `docs/07_Reports`, `docs/08_API`, `docs/09_Deployment`, `docs/10_Archive`.

## Broken links fixed

10 UserGuide files referenced a relative `../assets/screenshots/` path that broke after the UserGuide folder was moved one level deeper:

| File | Fix |
| --- | --- |
| `Guides/UserGuide/Accountant.md` | `../assets/screenshots/` → `../../assets/screenshots/` |
| `Guides/UserGuide/HR.md` | same |
| `Guides/UserGuide/Librarian.md` | same |
| `Guides/UserGuide/Parent.md` | same |
| `Guides/UserGuide/PayrollManager.md` | same |
| `Guides/UserGuide/Principal.md` | same |
| `Guides/UserGuide/Receptionist.md` | same |
| `Guides/UserGuide/SchoolAdmin.md` | same |
| `Guides/UserGuide/Student.md` | same |
| `Guides/UserGuide/Teacher.md` | same |

All other relative `.md`/`./` links in moved documents were checked; none reference moved paths. Pre-existing code-path references (e.g. `app/Core/Tenant/BelongsToSchool.php:11` in `04_Audits/database-audit-report.md`) are code annotations, not document links, and were left untouched.

## Indexes created

| File | Purpose |
| --- | --- |
| `docs/README.md` | Master index — links to every document in all 10 sections |
| `docs/05_Modules/README.md` | Module index — all module docs and app specs |
| `docs/03_Release/PROJECT_RELEASE_STATUS.md` | Release status summary (completed/pending modules, testing, readiness) |
| `docs/10_Archive/DOCUMENT_INVENTORY.md` | Original → new path catalog with reasons for every move |

## Verification

- **Count check:** 342 `.md` files accounted for (341 in `docs/` + root `README.md`) vs. pre-move inventory — nothing lost.
- **Duplicate check:** 5 suspected duplicate pairs compared via MD5 — all distinct content; no accidental overwrites.
- **Root check:** only `README.md` remains as loose `.md` at project root.
- **Link check:** no new broken relative links introduced (only pre-existing `../assets/screenshots/` placeholders and code-path annotations).

## Remaining documentation debt

- `docs/assets/` does not exist — screenshot links in UserGuide files are placeholders (pre-existing).
- Duplicate-ish pairs intentionally archived (root loose copies differ in content from canonical versions): `BUSINESS_WORKFLOWS.md`, `DEPLOYMENT_GUIDE.md`, `PERFORMANCE_REPORT.md` — canonical copies live in `01_Project/`, `09_Deployment/`, `04_Audits/Performance/`; loose originals are retained in `10_Archive/`.
- ~21 loose pre-existing module overview files sit at `docs/05_Modules/` root; they could later be organized into subfolders.
- `AI_RELEASE/README.docx` remains in the old folder (non-markdown, outside scope).

## Next steps

- None required for this phase. Approval required before starting Payroll work.
