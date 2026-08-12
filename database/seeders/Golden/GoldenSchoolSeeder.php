<?php

namespace Database\Seeders\Golden;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Calendar\Models\AcademicCalendar;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\FeePaymentItem;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Fees\Models\FeeStructureItem;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Models\StudentFeeItem;
use App\Modules\Homework\Models\Homework;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Library\Models\Author;
use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Library\Models\Category as LibraryCategory;
use App\Modules\Library\Models\FineSetting;
use App\Modules\Library\Models\Publisher;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Payroll\Models\EmployeePayslip;
use App\Modules\Payroll\Models\EmployeeSalaryStructure;
use App\Modules\Payroll\Models\PayGrade;
use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Payroll\Models\PayrollDesignation;
use App\Modules\Payroll\Models\PayrollItem;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Students\Models\Student;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use Illuminate\Database\Seeder;

/**
 * Enriches the shared base dataset (created by the core seeders) with
 * production-quality audit data: exams, homework, library, payroll,
 * leave types, event calendar, notifications and fee structures.
 */
class GoldenSchoolSeeder extends Seeder
{
    private School $school;
    private AcademicYear $academicYear;

    public function run(): void
    {
        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        $this->academicYear = AcademicYear::query()->where('school_id', $this->school->id)->where('is_active', true)->firstOrFail();

        $this->createFeeData();
        $this->createTeacherAttendance();
        $this->createExams();
        $this->createHomework();
        $this->createLibrary();
        $this->createPayroll();
        $this->createLeaveTypes();
        $this->createAcademicCalendar();
        $this->createNotifications();

        $this->command->info('=== GOLDEN DATASET ENRICHMENT SEEDED SUCCESSFULLY ===');
    }

    private function createFeeData(): void
    {
        $classSections = ClassSection::query()->where('school_id', $this->school->id)->get();
        $categories = FeeCategory::query()->where('school_id', $this->school->id)->get();

        foreach ($classSections as $cs) {
            $structure = FeeStructure::query()->firstOrCreate(
                ['academic_year_id' => $this->academicYear->id, 'class_section_id' => $cs->id],
                ['school_id' => $this->school->id, 'name' => 'Fee Structure - '.$cs->id, 'status' => 'active']
            );

            foreach ($categories as $cat) {
                FeeStructureItem::query()->firstOrCreate(
                    ['fee_structure_id' => $structure->id, 'fee_category_id' => $cat->id],
                    ['amount' => rand(500, 5000), 'sort_order' => $cat->sort_order]
                );
            }

            $students = Student::query()->where('school_id', $this->school->id)
                ->whereHas('sessions', fn ($q) => $q->where('class_section_id', $cs->id))
                ->get();

            foreach ($students as $student) {
                $studentFee = StudentFee::query()->firstOrCreate(
                    ['student_id' => $student->id, 'academic_year_id' => $this->academicYear->id],
                    ['school_id' => $this->school->id, 'fee_structure_id' => $structure->id, 'status' => 'active', 'assigned_at' => now()]
                );

                $items = $structure->items;
                $totalFee = 0;
                foreach ($items as $item) {
                    StudentFeeItem::query()->firstOrCreate(
                        ['student_fee_id' => $studentFee->id, 'fee_category_id' => $item->fee_category_id],
                        ['amount' => $item->amount, 'due_date' => now()->addDays(30)]
                    );
                    $totalFee += $item->amount;
                }

                // Create partial payment for some students
                if (rand(1, 100) > 40 && $items->isNotEmpty()) {
                    $paidAmount = rand(1, 3) * 1000;
                    $receiptNumber = 'RCP-'.$studentFee->id.'-'.$this->academicYear->id;
                    $payment = FeePayment::query()->firstOrCreate(
                        ['receipt_number' => $receiptNumber],
                        [
                            'school_id' => $this->school->id,
                            'student_id' => $student->id,
                            'academic_year_id' => $this->academicYear->id,
                            'payment_mode' => collect(['cash', 'upi', 'bank_transfer', 'cheque'])->random(),
                            'amount' => min($paidAmount, $totalFee),
                            'paid_on' => now()->subDays(rand(1, 30)),
                            'collected_by' => 1,
                        ]
                    );

                    FeePaymentItem::query()->firstOrCreate(
                        ['fee_payment_id' => $payment->id, 'student_fee_item_id' => $items->first()->id],
                        ['amount' => min($paidAmount, $totalFee)]
                    );
                }
            }
        }
    }

    private function createTeacherAttendance(): void
    {
        $teachers = Teacher::query()->where('school_id', $this->school->id)->get();

        for ($day = 90; $day >= 0; $day--) {
            $date = now()->subDays($day)->toDateString();
            $dayOfWeek = now()->subDays($day)->dayOfWeek;
            if (in_array($dayOfWeek, [0, 6])) {
                continue;
            }

            foreach ($teachers as $teacher) {
                TeacherAttendance::query()->firstOrCreate(
                    ['teacher_id' => $teacher->id, 'attendance_date' => $date],
                    [
                        'status' => rand(1, 100) > 85 ? 'absent' : 'present',
                        'marked_by' => 1,
                    ]
                );
            }
        }
    }

    private function createExams(): void
    {
        $classSections = ClassSection::query()->where('school_id', $this->school->id)->get();
        $subjects = Subject::query()->where('school_id', $this->school->id)->get();

        foreach ($classSections as $cs) {
            foreach ($subjects as $subject) {
                $exam = Exam::query()->updateOrCreate(
                    [
                        'school_id' => $this->school->id,
                        'academic_year_id' => $this->academicYear->id,
                        'class_section_id' => $cs->id,
                        'subject_id' => $subject->id,
                        'exam_name' => 'Mid Term Exam',
                    ],
                    [
                        'exam_type' => 'mid_term',
                        'exam_date' => now()->subDays(rand(10, 60)),
                        'maximum_marks' => 100,
                        'pass_marks' => 40,
                        'status' => 'completed',
                        'is_published' => true,
                        'created_by' => 1,
                    ]
                );

                $students = Student::query()->where('school_id', $this->school->id)
                    ->whereHas('sessions', fn ($q) => $q->where('class_section_id', $cs->id))
                    ->get();

                foreach ($students as $student) {
                    $marks = rand(20, 100);
                    ExamResult::query()->firstOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $student->id],
                        [
                            'school_id' => $this->school->id,
                            'marks_obtained' => $marks,
                            'grade' => $marks >= 90 ? 'A+' : ($marks >= 75 ? 'A' : ($marks >= 60 ? 'B' : ($marks >= 40 ? 'C' : 'F'))),
                            'status' => 'published',
                        ]
                    );
                }
            }
        }
    }

    private function createHomework(): void
    {
        $classSections = ClassSection::query()->where('school_id', $this->school->id)->get();
        $subjects = Subject::query()->where('school_id', $this->school->id)->get();

        foreach ($classSections as $cs) {
            foreach ($subjects as $subject) {
                Homework::query()->updateOrCreate(
                    [
                        'school_id' => $this->school->id,
                        'academic_year_id' => $this->academicYear->id,
                        'class_section_id' => $cs->id,
                        'subject_id' => $subject->id,
                        'title' => $subject->name.' Assignment',
                    ],
                    [
                        'description' => 'Complete the exercises from Chapter '.rand(1, 10).'.',
                        'assigned_date' => now()->subDays(rand(1, 14)),
                        'due_date' => now()->addDays(rand(1, 7)),
                        'status' => 'active',
                        'created_by' => 1,
                    ]
                );
            }
        }
    }

    private function createLibrary(): void
    {
        $cat = LibraryCategory::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Academic'], ['sort_order' => 1, 'status' => 'active']);
        $cat2 = LibraryCategory::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Reference'], ['sort_order' => 2, 'status' => 'active']);

        $author = Author::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'R.K. Narayan'], ['status' => 'active']);
        $author2 = Author::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'J.K. Rowling'], ['status' => 'active']);

        $publisher = Publisher::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Oxford Press'], ['contact' => 'info@oxfordpress.com', 'status' => 'active']);
        $publisher2 = Publisher::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Scholastic'], ['contact' => 'info@scholastic.com', 'status' => 'active']);

        FineSetting::query()->firstOrCreate(['school_id' => $this->school->id], ['fine_per_day' => 5.00, 'max_fine' => 500.00, 'grace_period_days' => 3, 'status' => 'active']);

        $books = [
            ['title' => 'Mathematics for Class 5', 'isbn' => '978-0-19-123456-7', 'category_id' => $cat->id, 'author_id' => $author->id, 'publisher_id' => $publisher->id, 'quantity' => 10, 'available_copies' => 8],
            ['title' => 'English Grammar Guide', 'isbn' => '978-0-19-234567-8', 'category_id' => $cat->id, 'author_id' => $author2->id, 'publisher_id' => $publisher2->id, 'quantity' => 15, 'available_copies' => 12],
            ['title' => 'Science Encyclopedia', 'isbn' => '978-0-19-345678-9', 'category_id' => $cat2->id, 'author_id' => $author->id, 'publisher_id' => $publisher->id, 'quantity' => 5, 'available_copies' => 3],
            ['title' => 'World History Atlas', 'isbn' => '978-0-19-456789-0', 'category_id' => $cat2->id, 'author_id' => $author2->id, 'publisher_id' => $publisher2->id, 'quantity' => 8, 'available_copies' => 6],
            ['title' => 'Computer Science Basics', 'isbn' => '978-0-19-567890-1', 'category_id' => $cat->id, 'author_id' => $author->id, 'publisher_id' => $publisher->id, 'quantity' => 12, 'available_copies' => 10],
        ];

        $students = Student::query()->where('school_id', $this->school->id)->get();

        foreach ($books as $bookData) {
            $book = Book::query()->firstOrCreate(
                ['school_id' => $this->school->id, 'isbn' => $bookData['isbn']],
                array_merge($bookData, ['school_id' => $this->school->id, 'language' => 'English', 'status' => 'active'])
            );

            // Create some book issues
            if ($students->isNotEmpty() && rand(1, 100) > 50) {
                $student = $students->random();
                BookIssue::query()->firstOrCreate(
                    ['school_id' => $this->school->id, 'book_id' => $book->id, 'issueable_type' => 'App\\Modules\\Students\\Models\\Student', 'issueable_id' => $student->id],
                    [
                        'issue_date' => now()->subDays(rand(5, 20)),
                        'due_date' => now()->subDays(rand(0, 5)),
                        'status' => 'issued',
                    ]
                );
            }
        }
    }

    private function createPayroll(): void
    {
        $teacher = Teacher::query()->where('school_id', $this->school->id)->first();

        $dept = PayrollDepartment::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Teaching Staff'], ['sort_order' => 1, 'status' => 'active']);

        $designation = PayrollDesignation::query()->firstOrCreate([
            'school_id' => $this->school->id, 'department_id' => $dept->id,
            'name' => 'Senior Teacher',
        ], ['status' => 'active']);

        // Salary components
        SalaryComponent::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'basic'], ['name_display' => 'Basic Salary', 'component_type' => 'earning', 'calculation_type' => 'fixed', 'value' => 35000, 'sort_order' => 1, 'status' => 'active']);
        SalaryComponent::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'hra'], ['name_display' => 'House Rent Allowance', 'component_type' => 'earning', 'calculation_type' => 'percentage', 'value' => 10, 'sort_order' => 2, 'status' => 'active']);
        SalaryComponent::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'da'], ['name_display' => 'Dearness Allowance', 'component_type' => 'earning', 'calculation_type' => 'percentage', 'value' => 5, 'sort_order' => 3, 'status' => 'active']);
        SalaryComponent::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'pf'], ['name_display' => 'Provident Fund', 'component_type' => 'deduction', 'calculation_type' => 'percentage', 'value' => 12, 'sort_order' => 1, 'status' => 'active']);
        SalaryComponent::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'tax'], ['name_display' => 'Income Tax', 'component_type' => 'deduction', 'calculation_type' => 'fixed', 'value' => 2500, 'sort_order' => 2, 'status' => 'active']);

        $grade = PayGrade::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Grade A'], ['min_salary' => 30000, 'max_salary' => 50000, 'status' => 'active']);

        if ($teacher) {
            EmployeeSalaryStructure::query()->firstOrCreate(
                [
                    'school_id' => $this->school->id,
                    'employee_id' => (string) $teacher->employee_id,
                    'employee_type' => 'teacher',
                ],
                [
                    'pay_grade_id' => $grade->id,
                    'effective_from' => $this->academicYear->starts_on,
                    'total_ctc' => 420000,
                    'status' => 'active',
                ]
            );
        }

        // Create payroll run
        $run = PayrollRun::query()->firstOrCreate(
            [
                'school_id' => $this->school->id,
                'month' => (int) now()->subMonth()->month,
                'year' => (int) now()->subMonth()->year,
            ],
            [
                'status' => 'locked',
                'generated_by' => 1,
                'generated_at' => now(),
                'notes' => 'Monthly payroll run',
            ]
        );

        if ($teacher) {
            $item = PayrollItem::query()->firstOrCreate(
                [
                    'school_id' => $this->school->id,
                    'payroll_run_id' => $run->id,
                    'employee_type' => 'teacher',
                    'employee_id' => (string) $teacher->employee_id,
                ],
                [
                    'gross_salary' => 38500,
                    'total_deductions' => 6700,
                    'net_salary' => 31800,
                    'status' => 'active',
                ]
            );

            EmployeePayslip::query()->firstOrCreate(
                [
                    'payroll_run_id' => $run->id,
                    'payroll_item_id' => $item->id,
                ],
                [
                    'school_id' => $this->school->id,
                    'payslip_number' => 'PSL-'.$run->id.'-'.$item->id,
                    'employee_type' => 'teacher',
                    'employee_id' => (string) $teacher->employee_id,
                    'employee_name' => $teacher->first_name.' '.$teacher->last_name,
                    'department_name' => $dept->name,
                    'designation_name' => $designation->name,
                    'earnings_json' => [['name' => 'Basic Salary', 'amount' => 35000], ['name' => 'HRA', 'amount' => 3500]],
                    'deductions_json' => [['name' => 'Provident Fund', 'amount' => 4200], ['name' => 'Income Tax', 'amount' => 2500]],
                    'gross_salary' => 38500,
                    'total_deductions' => 6700,
                    'net_salary' => 31800,
                    'generated_by' => 1,
                    'generated_at' => now(),
                ]
            );
        }
    }

    private function createLeaveTypes(): void
    {
        LeaveType::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Sick Leave'], ['is_active' => true, 'created_by' => 1]);
        LeaveType::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Casual Leave'], ['is_active' => true, 'created_by' => 1]);
        LeaveType::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Annual Leave'], ['is_active' => true, 'created_by' => 1]);
        LeaveType::query()->firstOrCreate(['school_id' => $this->school->id, 'name' => 'Emergency Leave'], ['is_active' => true, 'created_by' => 1]);
    }

    private function createAcademicCalendar(): void
    {
        AcademicCalendar::query()->firstOrCreate(
            ['school_id' => $this->school->id, 'academic_year_id' => $this->academicYear->id, 'title' => 'Independence Day Celebration'],
            [
                'event_type' => 'holiday',
                'start_date' => now()->addMonths(2),
                'description' => 'School will remain closed for Independence Day.',
                'audience' => 'all',
                'is_published' => true,
                'created_by' => 1,
            ]
        );
        AcademicCalendar::query()->firstOrCreate(
            ['school_id' => $this->school->id, 'academic_year_id' => $this->academicYear->id, 'title' => 'Parent-Teacher Meeting'],
            [
                'event_type' => 'meeting',
                'start_date' => now()->addMonth(),
                'description' => 'PTM for all classes.',
                'audience' => 'parents',
                'is_published' => true,
                'created_by' => 1,
            ]
        );
    }

    private function createNotifications(): void
    {
        $users = User::query()->whereIn('email', [
            'superadmin@school.com',
            'admin@school.com',
            'teacher@school.com',
            'parent@school.com',
            'driver@school.com',
        ])->get();

        $notification = Notification::query()->firstOrCreate(
            ['school_id' => $this->school->id, 'title' => 'Welcome to Demo Public School'],
            [
                'message' => 'Your account has been created successfully. Please login to access the portal.',
                'type' => 'announcement',
                'priority' => 'high',
                'status' => 'sent',
                'target_type' => 'all',
                'channel' => 'in_app',
                'sent_at' => now(),
                'created_by' => 1,
            ]
        );

        foreach ($users as $user) {
            $notification->users()->attach($user->id, [
                'is_read' => false,
                'delivery_status' => 'delivered',
            ]);
        }
    }
}