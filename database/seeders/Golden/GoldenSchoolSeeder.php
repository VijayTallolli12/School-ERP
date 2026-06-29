<?php

namespace Database\Seeders\Golden;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
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
use App\Modules\Parents\Models\Guardian;
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
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class GoldenSchoolSeeder extends Seeder
{
    private School $school;
    private AcademicYear $academicYear;
    private User $superAdmin;
    private User $schoolAdmin;
    private User $teacher1User;
    private User $teacher2User;
    private User $parent1User;
    private User $parent2User;

    public function run(): void
    {
        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        $this->academicYear = AcademicYear::query()->where('school_id', $this->school->id)->where('is_active', true)->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

        $this->createUsers();
        $this->createAcademicStructure();
        $this->createStudents();
        $this->createTeachers();
        $this->createParents();
        $this->createTimetable();
        $this->createStudentAttendance();
        $this->createTeacherAttendance();
        $this->createFeeData();
        $this->createExams();
        $this->createHomework();
        $this->createTransport();
        $this->createLibrary();
        $this->createPayroll();
        $this->createLeaveTypes();
        $this->createAcademicCalendar();
        $this->createNotifications();

        $this->command->info('=== GOLDEN DATASET SEEDED SUCCESSFULLY ===');
    }

    private function createUsers(): void
    {
        $this->teacher1User = User::factory()->create([
            'name' => 'Aisha Khan',
            'email' => 'aisha.khan@example.com',
            'password' => Hash::make('password'),
            'current_school_id' => $this->school->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $this->teacher1User->assignRole('Teacher');

        $this->teacher2User = User::factory()->create([
            'name' => 'Rahul Mehta',
            'email' => 'rahul.mehta@example.com',
            'password' => Hash::make('password'),
            'current_school_id' => $this->school->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $this->teacher2User->assignRole('Teacher');

        $this->parent1User = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password'),
            'current_school_id' => $this->school->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $this->parent1User->assignRole('Parent');

        $this->parent2User = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => Hash::make('password'),
            'current_school_id' => $this->school->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $this->parent2User->assignRole('Parent');
    }

    private function createAcademicStructure(): void
    {
        $this->call(\Database\Seeders\AcademicStructureSeeder::class);
    }

    private function createStudents(): void
    {
        $classSections = ClassSection::query()->where('school_id', $this->school->id)->get();

        $studentData = [
            ['admission_no' => 'ADM0001', 'first_name' => 'Arjun', 'last_name' => 'Verma'],
            ['admission_no' => 'ADM0002', 'first_name' => 'Priya', 'last_name' => 'Patel'],
            ['admission_no' => 'ADM0003', 'first_name' => 'Rohit', 'last_name' => 'Sharma'],
            ['admission_no' => 'ADM0004', 'first_name' => 'Sneha', 'last_name' => 'Reddy'],
            ['admission_no' => 'ADM0005', 'first_name' => 'Amit', 'last_name' => 'Singh'],
            ['admission_no' => 'ADM0006', 'first_name' => 'Neha', 'last_name' => 'Gupta'],
            ['admission_no' => 'ADM0007', 'first_name' => 'Vikram', 'last_name' => 'Joshi'],
            ['admission_no' => 'ADM0008', 'first_name' => 'Pooja', 'last_name' => 'Nair'],
            ['admission_no' => 'ADM0009', 'first_name' => 'Karan', 'last_name' => 'Mehta'],
            ['admission_no' => 'ADM0010', 'first_name' => 'Divya', 'last_name' => 'Kapoor'],
            ['admission_no' => 'ADM0011', 'first_name' => 'Ravi', 'last_name' => 'Desai'],
            ['admission_no' => 'ADM0012', 'first_name' => 'Anjali', 'last_name' => 'Menon'],
        ];

        foreach ($studentData as $i => $data) {
            $student = Student::factory()->create([
                'school_id' => $this->school->id,
                'admission_no' => $data['admission_no'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ]);

            $student->sessions()->create([
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'class_section_id' => $classSections[$i % count($classSections)]->id,
                'roll_no' => (string) ($i + 1),
                'joined_on' => $student->admission_date,
                'status' => 'active',
            ]);

            $student->guardians()->create([
                'school_id' => $this->school->id,
                'relation' => $i < 6 ? 'Father' : 'Mother',
                'name' => fake()->name('male'),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
                'is_primary' => true,
                'can_pickup' => true,
            ]);

            // Assign student role to user
            $stuUser = User::factory()->create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => 'student.' . strtolower($data['first_name']) . '.' . strtolower($data['last_name']) . '@example.com',
                'password' => Hash::make('password'),
                'current_school_id' => $this->school->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $stuUser->assignRole('Student');

            $student->update(['user_id' => $stuUser->id]);
        }
    }

    private function createTeachers(): void
    {
        $subjects = Subject::query()->where('school_id', $this->school->id)->get();
        $classSections = ClassSection::query()->where('school_id', $this->school->id)->get();

        $teachers = [
            [
                'user_id' => $this->teacher1User->id,
                'employee_id' => 'T-1001',
                'first_name' => 'Aisha',
                'last_name' => 'Khan',
                'gender' => 'female',
                'qualification' => 'M.Sc. Mathematics',
                'experience_years' => 8,
                'joining_date' => now()->subYears(5)->toDateString(),
                'phone' => '9876543210',
                'email' => 'aisha.khan@example.com',
                'address' => '12 Rose Lane, Demo City',
                'status' => 'active',
                'subjects' => [0, 1],
            ],
            [
                'user_id' => $this->teacher2User->id,
                'employee_id' => 'T-1002',
                'first_name' => 'Rahul',
                'last_name' => 'Mehta',
                'gender' => 'male',
                'qualification' => 'M.A. English',
                'experience_years' => 6,
                'joining_date' => now()->subYears(4)->toDateString(),
                'phone' => '9123456780',
                'email' => 'rahul.mehta@example.com',
                'address' => '8 Garden Street, Demo City',
                'status' => 'active',
                'subjects' => [2, 3],
            ],
        ];

        foreach ($teachers as $data) {
            $subjIds = $data['subjects'];
            unset($data['subjects']);

            $teacher = Teacher::query()->firstOrCreate(
                ['school_id' => $this->school->id, 'employee_id' => $data['employee_id']],
                array_merge($data, ['school_id' => $this->school->id, 'uuid' => (string) Str::uuid()])
            );

            $teacher->subjects()->syncWithoutDetaching(
                collect($subjIds)->mapWithKeys(fn(int $idx) => [
                    $subjects[$idx]->id => ['school_id' => $this->school->id]
                ])->all()
            );

            $teacher->classSections()->syncWithoutDetaching([
                $classSections[0]->id => ['is_class_teacher' => true, 'school_id' => $this->school->id],
            ]);
        }
    }

    private function createParents(): void
    {
        $students = Student::query()->where('school_id', $this->school->id)->get();

        $parentData = [
            [
                'user_id' => $this->parent1User->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'occupation' => 'Engineer',
                'address' => '123 Main St, City, State',
                'status' => 'active',
                'children' => [0, 1],
            ],
            [
                'user_id' => $this->parent2User->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1234567891',
                'occupation' => 'Teacher',
                'address' => '456 Oak Ave, City, State',
                'status' => 'active',
                'children' => [2, 3],
            ],
        ];

        foreach ($parentData as $i => $data) {
            $childIdx = $data['children'];
            unset($data['children']);

            Guardian::query()->firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['school_id' => $this->school->id, 'uuid' => (string) Str::uuid()])
            );

            $guardian = Guardian::where('email', $data['email'])->first();
            foreach ($childIdx as $ci) {
                if (isset($students[$ci])) {
                    $guardian->students()->syncWithoutDetaching([
                        $students[$ci]->id => ['relationship' => 'father', 'is_primary' => $ci === $childIdx[0]],
                    ]);
                }
            }
        }
    }

    private function createTimetable(): void
    {
        $classSection = ClassSection::query()->where('school_id', $this->school->id)->first();
        $subject = Subject::query()->where('school_id', $this->school->id)->first();
        $teacher = Teacher::query()->where('school_id', $this->school->id)->first();

        if (!$classSection || !$subject || !$teacher) return;

        $slots = [
            ['day_of_week' => 1, 'period_number' => 1, 'period_label' => 'Period 1', 'start_time' => '08:30', 'end_time' => '09:15', 'room' => 'A1'],
            ['day_of_week' => 1, 'period_number' => 2, 'period_label' => 'Period 2', 'start_time' => '09:20', 'end_time' => '10:05', 'room' => 'A1'],
            ['day_of_week' => 2, 'period_number' => 1, 'period_label' => 'Period 1', 'start_time' => '08:30', 'end_time' => '09:15', 'room' => 'A1'],
        ];

        foreach ($slots as $slot) {
            $slot['teacher_id'] = $teacher->id;
            $slot['class_section_id'] = $classSection->id;
            $slot['subject_id'] = $subject->id;
            $slot['academic_year_id'] = $this->academicYear->id;
            $slot['school_id'] = $this->school->id;
            $slot['status'] = 'active';

            \App\Modules\Timetable\Models\TimetableSlot::query()->updateOrCreate(
                [
                    'academic_year_id' => $this->academicYear->id,
                    'class_section_id' => $classSection->id,
                    'teacher_id' => $teacher->id,
                    'day_of_week' => $slot['day_of_week'],
                    'period_number' => $slot['period_number'],
                ],
                $slot
            );
        }
    }

    private function createStudentAttendance(): void
    {
        $students = Student::query()->where('school_id', $this->school->id)
            ->whereHas('sessions', fn($q) => $q->where('status', 'active'))
            ->with('sessions')
            ->get();

        $statuses = ['present', 'absent', 'late', 'half_day', 'excused'];

        for ($day = 90; $day >= 0; $day--) {
            $date = now()->subDays($day)->toDateString();
            $dayOfWeek = now()->subDays($day)->dayOfWeek;
            if (in_array($dayOfWeek, [0, 6])) continue;

            foreach ($students as $student) {
                $session = $student->sessions->firstWhere('status', 'active');
                if (!$session) continue;

                $status = rand(1, 100) > 80 ? $statuses[rand(0, 4)] : 'present';

                Attendance::query()->firstOrCreate(
                    ['school_id' => $this->school->id, 'student_id' => $student->id, 'attendance_date' => $date],
                    [
                        'school_id' => $this->school->id,
                        'class_section_id' => $session->class_section_id,
                        'academic_year_id' => $session->academic_year_id,
                        'status' => $status,
                        'marked_by' => 1,
                        'remarks' => $status !== 'present' ? fake()->sentence() : null,
                    ]
                );
            }
        }
    }

    private function createTeacherAttendance(): void
    {
        $teachers = Teacher::query()->where('school_id', $this->school->id)->get();

        for ($day = 90; $day >= 0; $day--) {
            $date = now()->subDays($day)->toDateString();
            $dayOfWeek = now()->subDays($day)->dayOfWeek;
            if (in_array($dayOfWeek, [0, 6])) continue;

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

    private function createFeeData(): void
    {
        $this->call(\Database\Seeders\FeeCategorySeeder::class);

        $classSections = ClassSection::query()->where('school_id', $this->school->id)->get();
        $categories = FeeCategory::query()->where('school_id', $this->school->id)->get();

        foreach ($classSections as $cs) {
            $structure = FeeStructure::query()->firstOrCreate(
                ['academic_year_id' => $this->academicYear->id, 'class_section_id' => $cs->id],
                ['school_id' => $this->school->id, 'name' => 'Fee Structure - ' . $cs->id, 'status' => 'active']
            );

            foreach ($categories as $cat) {
                FeeStructureItem::query()->firstOrCreate(
                    ['fee_structure_id' => $structure->id, 'fee_category_id' => $cat->id],
                    ['amount' => rand(500, 5000), 'sort_order' => $cat->sort_order]
                );
            }

            $students = Student::query()->where('school_id', $this->school->id)
                ->whereHas('sessions', fn($q) => $q->where('class_section_id', $cs->id))
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
                if (rand(1, 100) > 40) {
                    $paidAmount = rand(1, 3) * 1000;
                    $payment = FeePayment::query()->create([
                        'school_id' => $this->school->id,
                        'student_id' => $student->id,
                        'academic_year_id' => $this->academicYear->id,
                        'receipt_number' => 'RCP-' . str_pad((string) StudentFee::count(), 6, '0', STR_PAD_LEFT),
                        'payment_mode' => collect(['cash', 'upi', 'bank_transfer', 'cheque'])->random(),
                        'amount' => min($paidAmount, $totalFee),
                        'paid_on' => now()->subDays(rand(1, 30)),
                        'collected_by' => 1,
                    ]);

                    FeePaymentItem::query()->create([
                        'fee_payment_id' => $payment->id,
                        'student_fee_item_id' => $items->first()->id,
                        'amount' => min($paidAmount, $totalFee),
                    ]);
                }
            }
        }
    }

    private function createExams(): void
    {
        $classSections = ClassSection::query()->where('school_id', $this->school->id)->get();
        $subjects = Subject::query()->where('school_id', $this->school->id)->get();

        foreach ($classSections as $cs) {
            foreach ($subjects as $subject) {
                $exam = Exam::query()->create([
                    'school_id' => $this->school->id,
                    'academic_year_id' => $this->academicYear->id,
                    'class_section_id' => $cs->id,
                    'subject_id' => $subject->id,
                    'exam_name' => 'Mid Term Exam',
                    'exam_type' => 'mid_term',
                    'exam_date' => now()->subDays(rand(10, 60)),
                    'maximum_marks' => 100,
                    'pass_marks' => 40,
                    'status' => 'completed',
                    'is_published' => true,
                    'created_by' => 1,
                ]);

                $students = Student::query()->where('school_id', $this->school->id)
                    ->whereHas('sessions', fn($q) => $q->where('class_section_id', $cs->id))
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
                Homework::query()->create([
                    'school_id' => $this->school->id,
                    'academic_year_id' => $this->academicYear->id,
                    'class_section_id' => $cs->id,
                    'subject_id' => $subject->id,
                    'title' => $subject->name . ' Assignment',
                    'description' => 'Complete the exercises from Chapter ' . rand(1, 10) . '.',
                    'assigned_date' => now()->subDays(rand(1, 14)),
                    'due_date' => now()->addDays(rand(1, 7)),
                    'status' => 'active',
                    'created_by' => 1,
                ]);
            }
        }
    }

    private function createTransport(): void
    {
        $driver = Driver::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Rajesh Kumar',
            'mobile' => '9876500001',
            'license_number' => 'DL-2024-IND-001',
            'license_expiry_date' => now()->addYears(5),
            'status' => 'active',
        ]);

        $vehicle = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'KA-01-AB-1234',
            'vehicle_name' => 'School Bus 1',
            'vehicle_type' => 'bus',
            'capacity' => 40,
            'driver_id' => $driver->id,
            'status' => 'active',
        ]);

        $route = Route::query()->create([
            'school_id' => $this->school->id,
            'route_name' => 'Route A - North Campus',
            'start_point' => 'North Gate',
            'end_point' => 'School Main',
            'distance' => 12.5,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'status' => 'active',
        ]);

        $stops = [];
        foreach (['Station Road', 'City Center', 'Market Square', 'Lake View'] as $i => $stopName) {
            $stops[] = RouteStop::query()->create([
                'school_id' => $this->school->id,
                'route_id' => $route->id,
                'stop_name' => $stopName,
                'pickup_time' => sprintf('%02d:%02d', 7 + $i, 30),
                'drop_time' => sprintf('%02d:%02d', 15 + $i, 30),
                'sequence' => $i + 1,
            ]);
        }

        $students = Student::query()->where('school_id', $this->school->id)->get();
        foreach ($students as $i => $student) {
            if ($i < 6) {
                TransportAssignment::query()->firstOrCreate(
                    ['school_id' => $this->school->id, 'student_id' => $student->id],
                    [
                        'route_id' => $route->id,
                        'route_stop_id' => $stops[$i % count($stops)]->id,
                        'vehicle_id' => $vehicle->id,
                        'pickup_point' => $stops[$i % count($stops)]->stop_name,
                        'monthly_fee' => 1500,
                        'status' => 'active',
                    ]
                );
            }
        }
    }

    private function createLibrary(): void
    {
        $cat = LibraryCategory::query()->create(['school_id' => $this->school->id, 'name' => 'Academic', 'sort_order' => 1, 'status' => 'active']);
        $cat2 = LibraryCategory::query()->create(['school_id' => $this->school->id, 'name' => 'Reference', 'sort_order' => 2, 'status' => 'active']);

        $author = Author::query()->create(['school_id' => $this->school->id, 'name' => 'R.K. Narayan', 'status' => 'active']);
        $author2 = Author::query()->create(['school_id' => $this->school->id, 'name' => 'J.K. Rowling', 'status' => 'active']);

        $publisher = Publisher::query()->create(['school_id' => $this->school->id, 'name' => 'Oxford Press', 'contact' => 'info@oxfordpress.com', 'status' => 'active']);
        $publisher2 = Publisher::query()->create(['school_id' => $this->school->id, 'name' => 'Scholastic', 'contact' => 'info@scholastic.com', 'status' => 'active']);

        FineSetting::query()->create(['school_id' => $this->school->id, 'fine_per_day' => 5.00, 'max_fine' => 500.00, 'grace_period_days' => 3, 'status' => 'active']);

        $books = [
            ['title' => 'Mathematics for Class 5', 'isbn' => '978-0-19-123456-7', 'category_id' => $cat->id, 'author_id' => $author->id, 'publisher_id' => $publisher->id, 'quantity' => 10, 'available_copies' => 8],
            ['title' => 'English Grammar Guide', 'isbn' => '978-0-19-234567-8', 'category_id' => $cat->id, 'author_id' => $author2->id, 'publisher_id' => $publisher2->id, 'quantity' => 15, 'available_copies' => 12],
            ['title' => 'Science Encyclopedia', 'isbn' => '978-0-19-345678-9', 'category_id' => $cat2->id, 'author_id' => $author->id, 'publisher_id' => $publisher->id, 'quantity' => 5, 'available_copies' => 3],
            ['title' => 'World History Atlas', 'isbn' => '978-0-19-456789-0', 'category_id' => $cat2->id, 'author_id' => $author2->id, 'publisher_id' => $publisher2->id, 'quantity' => 8, 'available_copies' => 6],
            ['title' => 'Computer Science Basics', 'isbn' => '978-0-19-567890-1', 'category_id' => $cat->id, 'author_id' => $author->id, 'publisher_id' => $publisher->id, 'quantity' => 12, 'available_copies' => 10],
        ];

        $students = Student::query()->where('school_id', $this->school->id)->get();

        foreach ($books as $bookData) {
            $book = Book::query()->create(array_merge($bookData, ['school_id' => $this->school->id, 'language' => 'English', 'status' => 'active']));

            // Create some book issues
            if (rand(1, 100) > 50) {
                $student = $students->random();
                BookIssue::query()->create([
                    'school_id' => $this->school->id,
                    'book_id' => $book->id,
                    'issueable_type' => 'App\\Modules\\Students\\Models\\Student',
                    'issueable_id' => $student->id,
                    'issue_date' => now()->subDays(rand(5, 20)),
                    'due_date' => now()->subDays(rand(0, 5)),
                    'status' => 'issued',
                ]);
            }
        }
    }

    private function createPayroll(): void
    {
        $teacher = Teacher::query()->where('school_id', $this->school->id)->first();

        $dept = PayrollDepartment::query()->create(['school_id' => $this->school->id, 'name' => 'Teaching Staff', 'sort_order' => 1, 'status' => 'active']);

        $designation = PayrollDesignation::query()->create([
            'school_id' => $this->school->id, 'department_id' => $dept->id,
            'name' => 'Senior Teacher', 'status' => 'active',
        ]);

        // Salary components
        SalaryComponent::query()->create(['school_id' => $this->school->id, 'name' => 'basic', 'name_display' => 'Basic Salary', 'component_type' => 'earning', 'calculation_type' => 'fixed', 'value' => 35000, 'sort_order' => 1, 'status' => 'active']);
        SalaryComponent::query()->create(['school_id' => $this->school->id, 'name' => 'hra', 'name_display' => 'House Rent Allowance', 'component_type' => 'earning', 'calculation_type' => 'percentage', 'value' => 10, 'sort_order' => 2, 'status' => 'active']);
        SalaryComponent::query()->create(['school_id' => $this->school->id, 'name' => 'da', 'name_display' => 'Dearness Allowance', 'component_type' => 'earning', 'calculation_type' => 'percentage', 'value' => 5, 'sort_order' => 3, 'status' => 'active']);
        SalaryComponent::query()->create(['school_id' => $this->school->id, 'name' => 'pf', 'name_display' => 'Provident Fund', 'component_type' => 'deduction', 'calculation_type' => 'percentage', 'value' => 12, 'sort_order' => 1, 'status' => 'active']);
        SalaryComponent::query()->create(['school_id' => $this->school->id, 'name' => 'tax', 'name_display' => 'Income Tax', 'component_type' => 'deduction', 'calculation_type' => 'fixed', 'value' => 2500, 'sort_order' => 2, 'status' => 'active']);

        $grade = PayGrade::query()->create(['school_id' => $this->school->id, 'name' => 'Grade A', 'min_salary' => 30000, 'max_salary' => 50000, 'status' => 'active']);

        if ($teacher) {
            EmployeeSalaryStructure::query()->create([
                'school_id' => $this->school->id,
                'employee_id' => (string) $teacher->employee_id,
                'employee_type' => 'teacher',
                'pay_grade_id' => $grade->id,
                'effective_from' => $this->academicYear->starts_on,
                'total_ctc' => 420000,
                'status' => 'active',
            ]);
        }

        // Create payroll run
        $run = PayrollRun::query()->create([
            'school_id' => $this->school->id,
            'month' => (int) now()->subMonth()->month,
            'year' => (int) now()->subMonth()->year,
            'status' => 'locked',
            'generated_by' => 1,
            'generated_at' => now(),
            'notes' => 'Monthly payroll run',
        ]);

        if ($teacher) {
            $item = PayrollItem::query()->create([
                'school_id' => $this->school->id,
                'payroll_run_id' => $run->id,
                'employee_type' => 'teacher',
                'employee_id' => (string) $teacher->employee_id,
                'gross_salary' => 38500,
                'total_deductions' => 6700,
                'net_salary' => 31800,
                'status' => 'active',
            ]);

            EmployeePayslip::query()->create([
                'school_id' => $this->school->id,
                'payroll_run_id' => $run->id,
                'payroll_item_id' => $item->id,
                'payslip_number' => 'PSL-' . $run->id . '-' . $item->id,
                'employee_type' => 'teacher',
                'employee_id' => (string) $teacher->employee_id,
                'employee_name' => $teacher->first_name . ' ' . $teacher->last_name,
                'department_name' => $dept->name,
                'designation_name' => $designation->name,
                'earnings_json' => [['name' => 'Basic Salary', 'amount' => 35000], ['name' => 'HRA', 'amount' => 3500]],
                'deductions_json' => [['name' => 'Provident Fund', 'amount' => 4200], ['name' => 'Income Tax', 'amount' => 2500]],
                'gross_salary' => 38500,
                'total_deductions' => 6700,
                'net_salary' => 31800,
                'generated_by' => 1,
                'generated_at' => now(),
            ]);
        }
    }

    private function createLeaveTypes(): void
    {
        LeaveType::query()->create(['school_id' => $this->school->id, 'name' => 'Sick Leave', 'is_active' => true, 'created_by' => 1]);
        LeaveType::query()->create(['school_id' => $this->school->id, 'name' => 'Casual Leave', 'is_active' => true, 'created_by' => 1]);
        LeaveType::query()->create(['school_id' => $this->school->id, 'name' => 'Annual Leave', 'is_active' => true, 'created_by' => 1]);
        LeaveType::query()->create(['school_id' => $this->school->id, 'name' => 'Emergency Leave', 'is_active' => true, 'created_by' => 1]);
    }

    private function createAcademicCalendar(): void
    {
        AcademicCalendar::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Independence Day Celebration',
            'event_type' => 'holiday',
            'start_date' => now()->addMonths(2),
            'description' => 'School will remain closed for Independence Day.',
            'audience' => 'all',
            'is_published' => true,
            'created_by' => 1,
        ]);
        AcademicCalendar::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Parent-Teacher Meeting',
            'event_type' => 'meeting',
            'start_date' => now()->addMonth(),
            'description' => 'PTM for all classes.',
            'audience' => 'parents',
            'is_published' => true,
            'created_by' => 1,
        ]);
    }

    private function createNotifications(): void
    {
        $users = User::query()->whereIn('email', [
            'superadmin@example.com',
            'admin@example.com',
            'john.doe@example.com',
            'jane.smith@example.com',
        ])->get();

        $notification = Notification::query()->create([
            'school_id' => $this->school->id,
            'title' => 'Welcome to Demo Public School',
            'message' => 'Your account has been created successfully. Please login to access the portal.',
            'type' => 'announcement',
            'priority' => 'high',
            'status' => 'sent',
            'target_type' => 'all',
            'channel' => 'in_app',
            'sent_at' => now(),
            'created_by' => 1,
        ]);

        foreach ($users as $user) {
            $notification->users()->attach($user->id, [
                'is_read' => false,
                'delivery_status' => 'delivered',
            ]);
        }
    }
}
