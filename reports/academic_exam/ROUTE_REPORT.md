# Route Report — Phase 05: Academic Exam Workflow

All new routes are defined in `routes/modules/exams.php`.

## Grade Scale Routes
Prefix: `/grade-scales` — Name: `grade-scales.` — Middleware: `permission:exams.view`

| Method | URI | Action | Permission |
|--------|-----|--------|------------|
| GET | `/grade-scales` | `GradeScaleController@index` | exams.view |
| GET | `/grade-scales/data` | `GradeScaleController@data` | exams.view |
| POST | `/grade-scales` | `GradeScaleController@store` | exams.create |
| GET | `/grade-scales/{gradeScale}` | `GradeScaleController@show` | exams.view |
| PUT | `/grade-scales/{gradeScale}` | `GradeScaleController@update` | exams.update |
| DELETE | `/grade-scales/{gradeScale}` | `GradeScaleController@destroy` | exams.delete |

## Exam Schedule Routes
Prefix: `/exams/{exam}/schedules` — Name: `exams.schedules.` — Middleware: `permission:exams.view`

| Method | URI | Action | Permission |
|--------|-----|--------|------------|
| GET | `/exams/{exam}/schedules` | `ExamScheduleController@index` | exams.view |
| GET | `/exams/{exam}/schedules/data` | `ExamScheduleController@data` | exams.view |
| POST | `/exams/{exam}/schedules` | `ExamScheduleController@store` | exams.update |
| GET | `/exams/{exam}/schedules/{schedule}` | `ExamScheduleController@show` | exams.view |
| PUT | `/exams/{exam}/schedules/{schedule}` | `ExamScheduleController@update` | exams.update |
| DELETE | `/exams/{exam}/schedules/{schedule}` | `ExamScheduleController@destroy` | exams.delete |

## Exam Mark Routes
Prefix: `/exam-schedules/{schedule}/marks` — Name: `exams.schedules.marks.` — Middleware: `permission:exams.view`

| Method | URI | Action | Permission |
|--------|-----|--------|------------|
| GET | `/exam-schedules/{schedule}/marks` | `ExamMarkController@index` | exams.view |
| GET | `/exam-schedules/{schedule}/marks/data` | `ExamMarkController@data` | exams.view |
| POST | `/exam-schedules/{schedule}/marks/bulk-save` | `ExamMarkController@bulkSave` | exams.update |
| GET | `/exam-schedules/{schedule}/marks/{mark}` | `ExamMarkController@show` | exams.view |
| PUT | `/exam-schedules/{schedule}/marks/{mark}` | `ExamMarkController@update` | exams.update |

## Authorisation Scheme

- All route groups are wrapped in `permission:exams.view` middleware (base access).
- Mutating routes additionally require `permission:exams.create`, `permission:exams.update`, or `permission:exams.delete` middleware.
- Individual controller actions also call `$this->authorize()` for policy-level enforcement.
