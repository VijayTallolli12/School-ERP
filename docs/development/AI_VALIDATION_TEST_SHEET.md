# AI Intent Validation Test Sheet

## Test Categories

### 1. Intent Classification Accuracy

| # | Query | Expected Intent | Expected Action | Category |
|---|-------|----------------|-----------------|----------|
| 1 | How many students are there? | student.total | query | Students |
| 2 | Show me the total student count | student.total | query | Students |
| 3 | How many students joined this month? | student.admitted_this_month | query | Students |
| 4 | New admissions this month | student.admitted_this_month | query | Students |
| 5 | Students by class | student.by_class | query | Students |
| 6 | Class wise student count | student.by_class | query | Students |
| 7 | Who is absent today? | attendance.absent_today | query | Attendance |
| 8 | Show today's absentees | attendance.absent_today | query | Attendance |
| 9 | Monthly attendance percentage | attendance.monthly_percentage | query | Attendance |
| 10 | What is the attendance rate? | attendance.monthly_percentage | query | Attendance |
| 11 | Students below 75% attendance | attendance.below_75 | query | Attendance |
| 12 | Low attendance students | attendance.below_75 | query | Attendance |
| 13 | Total outstanding fees | fee.outstanding | query | Fees |
| 14 | How much fee is pending? | fee.outstanding | query | Fees |
| 15 | Students with pending fees above 5000 | fee.pending_above | query | Fees |
| 16 | Show defaulters above 10000 | fee.pending_above | query | Fees |
| 17 | Today's fee collection | fee.today_collection | query | Fees |
| 18 | How much was collected today? | fee.today_collection | query | Fees |
| 19 | Top fee defaulters | fee.top_defaulters | query | Fees |
| 20 | List biggest defaulters | fee.top_defaulters | query | Fees |
| 21 | Route occupancy | transport.route_occupancy | query | Transport |
| 22 | Students per route | transport.students_on_route | query | Transport |
| 23 | Vehicle assignments | transport.vehicle_assignments | query | Transport |
| 24 | How many books are issued? | library.books_issued | query | Library |
| 25 | Overdue books | library.overdue_books | query | Library |
| 26 | Library fine collection | library.fine_collection | query | Library |
| 27 | Latest payroll run | payroll.latest_run | query | Payroll |
| 28 | Locked payroll runs | payroll.locked_runs | query | Payroll |
| 29 | Highest salary employees | payroll.highest_salary | query | Payroll |
| 30 | Payroll generated this month | payroll.generated_this_month | query | Payroll |
| 31 | Give me today's school summary | school.summary | query | Reports |

### 2. Action Intent Classification

| # | Query | Expected Intent | Expected Action | Destructive |
|---|-------|----------------|-----------------|-------------|
| 32 | Run payroll for June | payroll.generate | action | Yes |
| 33 | Generate salary for July 2026 | payroll.generate | action | Yes |
| 34 | Send absence notifications | attendance.notify | action | Yes |
| 35 | Notify parents of absentees | attendance.notify | action | Yes |
| 36 | Send fee reminders | fee.send_reminders | action | Yes |
| 37 | Remind parents about fees | fee.send_reminders | action | Yes |
| 38 | Publish exam results | exam.publish | action | Yes |
| 39 | Send a notification to all students | notification.send | action | Yes |

### 3. Parameter Extraction

| # | Query | Expected Parameters |
|---|-------|-------------------|
| 40 | Students with pending fees above 5000 | {amount: 5000} |
| 41 | Top 5 highest salary employees | {limit: 5} |
| 42 | Run payroll for June 2026 | {month: 6, year: 2026} |
| 43 | Send fee reminders for 60 days | {days: 60} |
| 44 | Notify absent students for yesterday | {date: "YYYY-MM-DD"} |
| 45 | Students with pending fees above 10000 | {amount: 10000} |
| 46 | Show me top 3 defaulters | {limit: 3} |

### 4. Synonym Normalization

| # | Query | Expected Mapping |
|---|-------|-----------------|
| 47 | What is the current day's attendance? | attendance.absent_today |
| 48 | Show me the monthly fee report | fee.outstanding |
| 49 | Class-wise student distribution | student.by_class |
| 50 | Section wise attendance | attendance.monthly_percentage |
| 51 | Pending fee report | fee.outstanding |
| 52 | Outstanding fee balance | fee.outstanding |
| 53 | Employee salary status | payroll.generated_this_month |
| 54 | School bus assignments | transport.vehicle_assignments |

### 5. Unknown/Edge Cases

| # | Expected Intent |
|---|----------------|
| 55 | What is the weather today? | unknown |
| 56 | Tell me a joke | unknown |
| 57 | (empty string) | unknown |

### 6. Confirmation Flow

| # | Query | Expected Behavior |
|---|-------|------------------|
| 58 | Run payroll (no confirmed) | confirmation_required: true |
| 59 | Run payroll (confirmed: true) | Execute payroll |
| 60 | Send notifications (no confirmed) | confirmation_required: true |

## Validation Results

| Metric | Target | Status |
|--------|--------|--------|
| Intent Accuracy | 95%+ | Pending live validation |
| Parameter Accuracy | 90%+ | Pending live validation |
| Wrong Agent Routing | 0 | Pending live validation |
| Invalid JSON | 0 | Verified - all paths return valid JSON |

## Notes

- Live validation requires Gemini API key in .env
- Fallback (keyword) validation can be done without API key
- Tests 1-31 cover all query intents
- Tests 32-38 cover all destructive action intents
- Tests 39-46 cover parameter extraction edge cases
- Tests 47-57 cover synonym normalization and edge cases
