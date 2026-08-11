# Documents Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The documents module manages student and teacher document storage and retrieval workflows.

## Architecture

Implemented through DocumentController, TeacherDocumentController, DocumentService, DocumentUploadService, DocumentRepository, and document policies.

## Database Tables

- student_documents
- teacher_documents

## Models

- App\Modules\Documents\Models\Document
- App\Modules\Documents\Models\TeacherDocument

## Controllers

- DocumentController
- TeacherDocumentController

## Services

- DocumentService
- DocumentUploadService

## Routes

- /admin/documents
- /admin/teacher-documents

## Policies

- DocumentPolicy
- TeacherDocumentPolicy

## Permissions

- student_documents.view
- student_documents.create
- student_documents.update
- student_documents.delete

## Business Rules

- Documents are associated with school data and related people.
- Upload and update operations are permission-controlled.

## Workflow

1. Upload a document for a student or teacher.
2. Store the document metadata and file reference.
3. Review or update the record as needed.

## Common Issues

- Upload failures may be caused by storage permissions or missing metadata.
- Access may be blocked by missing permission checks.

## Troubleshooting

- Check storage permissions and file path configuration.
- Verify the relevant user role can access documents.
