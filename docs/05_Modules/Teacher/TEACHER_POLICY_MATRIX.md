# Teacher Policy Matrix

## Legend
- **T** = Teacher
- **A** = Admin (School Admin / Super Admin)
- **P** = Principal
- **-** = No access

| Resource | View | Create | Update | Delete | Approve | Notes |
|----------|------|--------|--------|--------|---------|-------|
| **Attendance** | T, A, P | T, A | T, A | A | - | T scoped to own class sections |
| **Homework** | T, A, P | T, A | T, A | T, A | - | T scoped to own class sections |
| **Exams** | T, A, P | T, A | T, A | A | - | T scoped to own class sections; publish requires permission |
| **Leave** | T (own), A (all), P (all) | T | - | - | A, P | T can view only own requests; approve by Admin/Principal |
| **Documents** | T (scoped), A (all) | T (scoped), A | - | A | - | T scoped to students in their class sections |
| **Payroll** | T (own), A (all), P (all) | A | A | - | A | T can view own payslips only (`payroll.view_own`) |
| **Students** | T (scoped), A (all) | A | A | A | - | T can view students in their class sections |
| **Teachers** | A, P | A | A | A | - | T cannot view other teachers |
| **Notifications** | T, A, P | A, P | - | - | - | T can receive, not send |
| **Timetable** | T (own), A (all), P (all) | A | A | A | - | T can view own schedule |
| **Calendar** | T, A, P | A | A | A | - | T can view academic calendar events |
| **Fees** | A, P | A | A | A | - | T has no fee access |
| **Transport** | A, P | A | A | A | - | T has no transport access |
| **Library** | A, P | A | A | A | - | T has no library access |
| **Users** | A | A | A | A | - | T has no user management access |
| **Settings** | A | A | A | A | - | T has no settings access |
| **Reports** | A, P | - | - | - | - | T has no analytics/reports access |

## AI Access Matrix

| AI Feature | Teacher | Admin | Principal | Notes |
|------------|---------|-------|-----------|-------|
| Ask ERP | Yes | Yes | Yes | T restricted to allowed intents |
| Executive Copilot | No | Yes | Yes | Hidden from sidebar for T |
| AI Agents | No | Yes | Yes | Hidden from sidebar for T |
| Execution History | No | Yes | Yes | Hidden from sidebar for T |
| AI Administration | No | Yes | Yes | Hidden from sidebar for T |
