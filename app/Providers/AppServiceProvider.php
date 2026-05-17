<?php

namespace App\Providers;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Policies\AcademicYearPolicy;
use App\Modules\Academics\Policies\ClassSectionPolicy;
use App\Modules\Academics\Policies\SchoolClassPolicy;
use App\Modules\Academics\Policies\SectionPolicy;
use App\Modules\Academics\Policies\SubjectPolicy;
use App\Modules\Academics\Repositories\AcademicRepository;
use App\Modules\Academics\Repositories\AcademicRepositoryInterface;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Policies\AttendancePolicy;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Policies\FeeCategoryPolicy;
use App\Modules\Fees\Policies\FeePaymentPolicy;
use App\Modules\Fees\Policies\FeeStructurePolicy;
use App\Modules\Fees\Policies\StudentFeePolicy;
use App\Modules\Fees\Repositories\FeeRepository;
use App\Modules\Fees\Repositories\FeeRepositoryInterface;
use App\Modules\RBAC\Policies\PermissionPolicy;
use App\Modules\RBAC\Policies\RolePolicy;
use App\Modules\RBAC\Repositories\PermissionRepository;
use App\Modules\RBAC\Repositories\PermissionRepositoryInterface;
use App\Modules\RBAC\Repositories\RoleRepository;
use App\Modules\RBAC\Repositories\RoleRepositoryInterface;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Policies\StudentPolicy;
use App\Modules\Students\Repositories\StudentRepository;
use App\Modules\Students\Repositories\StudentRepositoryInterface;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Parents\Policies\ParentPolicy;
use App\Modules\Parents\Repositories\ParentRepository;
use App\Modules\Parents\Repositories\ParentRepositoryInterface;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Policies\ExamPolicy;
use App\Modules\Exams\Repositories\ExamRepository;
use App\Modules\Exams\Repositories\ExamRepositoryInterface;
use App\Modules\Reports\Repositories\AttendanceReportRepository;
use App\Modules\Reports\Repositories\AttendanceReportRepositoryInterface;
use App\Modules\Reports\Repositories\ExamReportRepository;
use App\Modules\Reports\Repositories\ExamReportRepositoryInterface;
use App\Modules\Reports\Repositories\ParentReportRepository;
use App\Modules\Reports\Repositories\ParentReportRepositoryInterface;
use App\Modules\Reports\Repositories\StudentReportRepository;
use App\Modules\Reports\Repositories\StudentReportRepositoryInterface;
use App\Modules\Reports\Repositories\TeacherReportRepository;
use App\Modules\Reports\Repositories\TeacherReportRepositoryInterface;
use App\Modules\Settings\Repositories\SettingsRepository;
use App\Modules\Settings\Repositories\SettingsRepositoryInterface;
use App\Modules\Teachers\Policies\TeacherPolicy;
use App\Modules\Teachers\Repositories\TeacherRepository;
use App\Modules\Teachers\Repositories\TeacherRepositoryInterface;
use App\Modules\Timetable\Models\TimetableSlot;
use App\Modules\Timetable\Policies\TimetableSlotPolicy;
use App\Modules\Timetable\Repositories\TimetableRepository;
use App\Modules\Timetable\Repositories\TimetableRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SchoolContext::class);

        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(ParentRepositoryInterface::class, ParentRepository::class);
        $this->app->bind(AcademicRepositoryInterface::class, AcademicRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(FeeRepositoryInterface::class, FeeRepository::class);
        $this->app->bind(TeacherRepositoryInterface::class, TeacherRepository::class);
        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
        $this->app->bind(TimetableRepositoryInterface::class, TimetableRepository::class);
        $this->app->bind(\App\Modules\Notifications\Repositories\NotificationRepositoryInterface::class, \App\Modules\Notifications\Repositories\NotificationRepository::class);
        $this->app->bind(StudentReportRepositoryInterface::class, StudentReportRepository::class);
        $this->app->bind(AttendanceReportRepositoryInterface::class, AttendanceReportRepository::class);
        $this->app->bind(ExamReportRepositoryInterface::class, ExamReportRepository::class);
        $this->app->bind(TeacherReportRepositoryInterface::class, TeacherReportRepository::class);
        $this->app->bind(ParentReportRepositoryInterface::class, ParentReportRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->addNamespace('Reports', app_path('Modules/Reports/Views'));

        Gate::before(function ($user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Guardian::class, ParentPolicy::class);
        Gate::policy(AcademicYear::class, AcademicYearPolicy::class);
        Gate::policy(SchoolClass::class, SchoolClassPolicy::class);
        Gate::policy(Section::class, SectionPolicy::class);
        Gate::policy(Subject::class, SubjectPolicy::class);
        Gate::policy(\App\Modules\Academics\Models\ClassSection::class, ClassSectionPolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(Teacher::class, TeacherPolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(TimetableSlot::class, TimetableSlotPolicy::class);
        Gate::policy(FeeCategory::class, FeeCategoryPolicy::class);
        Gate::policy(FeeStructure::class, FeeStructurePolicy::class);
        Gate::policy(StudentFee::class, StudentFeePolicy::class);
        Gate::policy(FeePayment::class, FeePaymentPolicy::class);
        Gate::policy(\App\Modules\Notifications\Models\Notification::class, \App\Modules\Notifications\Policies\NotificationPolicy::class);
    }
}
