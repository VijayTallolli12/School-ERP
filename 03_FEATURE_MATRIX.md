# Feature Matrix

| Area | Implemented | Missing or Unable to Verify |
|---|---|---|
| Authentication | Login, logout, forgot/reset password, API login, throttling on API login, login activity logging. | Production SSO/MFA, enforced password rotation despite `force_password_change` field, complete account lockout policy. |
| Authorization | Spatie Permission teams, role seeder, policies, route middleware, `@can` gates. | Complete role-by-page verification not possible because route list currently fails. |
| Multi-school tenancy | `SchoolContext`, `SetSchoolContext`, `BelongsToSchool`, school-aware migrations. | Exhaustive tenant leak testing not verified across every query. |
| Dashboard | Role dashboard builders and collectors. | Some roles may share generic admin dashboard; widget accuracy not fully verified. |
| Academics | Academic years, classes, sections, class sections, subjects, class subjects, terms. | Promotion automation and full academic planning workflow. |
| Students | CRUD, guardians, documents, sessions, reports, portal/API exposure. | Formal admissions pipeline, promotion, alumni management. |
| Parents | Guardian records, parent portal pages, child attendance/fees/exams/homework/timetable. | Dedicated parent mobile permissions and full communication workflows. |
| Teachers | CRUD, attendance, leaves, documents, subjects, class mapping. | Workload automation validation and teacher payroll integration depth. |
| Attendance | Student and teacher attendance, reports, exports, realtime status. | One realtime status test fails. |
| Fees | Categories, structures, assignments, collections, receipts, reports. | Payment gateway integration, route-list-blocking report binding bug. |
| Transport | Drivers, vehicles, routes, stops, assignments, live location, trips, driver APIs. | External GPS adapter production verification and route optimization. |
| Library | Books, categories, authors, publishers, issues, returns, fine settings, exports. | Barcode/RFID and advanced inventory workflows. |
| Payroll | Departments, designations, components, grades, salary structures, runs, payslips. | Statutory compliance, bank exports, payroll settings migration pending through HR migration. |
| HR | Employees, contracts/documents/payroll settings code. | HR migration pending in current DB; recruitment/performance workflows absent. |
| Reports | Student, attendance, absent students, fees, exams, teachers, parents. | Route list fails; performance/accuracy not proven for production data volumes. |
| APIs | REST APIs for major mobile/portal functions, Sanctum protection. | OpenAPI documentation not verified, inconsistent response pagination likely across endpoints. |
| AI | Assistant services, agent registry/execution, role data scoping docs. | AI query log migration pending; production provider/security configuration unable to verify. |
| Backup | Deployment/backup docs. | No runnable backup UI/job verified. |

