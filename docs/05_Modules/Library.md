# Library Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The library module supports book management, categories, authors, publishers, and library operations.

## Architecture

Implemented via LibraryController, LibraryService, LibraryRepository, and library policies.

## Database Tables

- library_books
- library_categories
- library_authors
- library_publishers
- library_issues

## Models

- App\Modules\Library\Models\Book
- App\Modules\Library\Models\BookIssue

## Controllers

- LibraryController

## Services

- LibraryService

## Routes

- /admin/library
- /admin/library/books
- /admin/library/categories
- /admin/library/authors
- /admin/library/publishers

## Policies

- BookPolicy
- BookIssuePolicy

## Permissions

- library.view
- library.create
- library.update
- library.delete

## Business Rules

- Library records are school-scoped.
- Book issues are linked to borrowers and depend on the current school context.

## Workflow

1. Create catalog entries such as books and authors.
2. Manage issuing and return workflows.
3. Use library reports and dashboards.

## Common Issues

- Missing borrower or book records can block issue workflows.
- Permission errors can prevent catalog updates.

## Troubleshooting

- Confirm the book and borrower records exist.
- Validate the user has library privileges.
