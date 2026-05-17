<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\ClassSubject;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Repositories\AcademicRepositoryInterface;
use App\Modules\Academics\Requests\AssignClassSubjectRequest;
use App\Modules\Academics\Requests\SaveAcademicYearRequest;
use App\Modules\Academics\Requests\SaveClassRequest;
use App\Modules\Academics\Requests\SaveClassSectionRequest;
use App\Modules\Academics\Requests\SaveSectionRequest;
use App\Modules\Academics\Requests\SaveSubjectRequest;
use App\Modules\Academics\Services\AcademicService;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class AcademicController extends Controller
{
    public function __construct(
        private readonly AcademicRepositoryInterface $academics,
        private readonly AcademicService $service,
    ) {
    }

    public function index()
    {
        return view('modules.academics.index', [
            'academicYears' => $this->academics->academicYears()->get(),
            'classes' => $this->academics->classes()->get(),
            'sections' => $this->academics->sections()->get(),
            'subjects' => $this->academics->subjects()->get(),
            'teachers' => User::query()->role('Teacher')->orderBy('name')->get(),
        ]);
    }

    public function academicYearsData(): JsonResponse
    {
        return DataTables::of($this->academics->academicYears())
            ->editColumn('starts_on', fn (AcademicYear $year) => $year->starts_on?->format('d M Y'))
            ->editColumn('ends_on', fn (AcademicYear $year) => $year->ends_on?->format('d M Y'))
            ->addColumn('active_badge', fn (AcademicYear $year) => $year->is_active ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">No</span>')
            ->addColumn('actions', fn (AcademicYear $year) => view('modules.academics._actions', ['type' => 'academic-year', 'model' => $year])->render())
            ->rawColumns(['active_badge', 'actions'])
            ->toJson();
    }

    public function classesData(): JsonResponse
    {
        return DataTables::of($this->academics->classes())
            ->addColumn('actions', fn (SchoolClass $class) => view('modules.academics._actions', ['type' => 'class', 'model' => $class])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function sectionsData(): JsonResponse
    {
        return DataTables::of($this->academics->sections())
            ->addColumn('actions', fn (Section $section) => view('modules.academics._actions', ['type' => 'section', 'model' => $section])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function subjectsData(): JsonResponse
    {
        return DataTables::of($this->academics->subjects())
            ->addColumn('type_label', fn (Subject $subject) => str($subject->type)->replace('_', ' ')->headline())
            ->addColumn('actions', fn (Subject $subject) => view('modules.academics._actions', ['type' => 'subject', 'model' => $subject])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function classSubjectsData(): JsonResponse
    {
        return DataTables::of($this->academics->classSubjects())
            ->addColumn('academic_year', fn (ClassSubject $classSubject) => $classSubject->academicYear?->name)
            ->addColumn('class_name', fn (ClassSubject $classSubject) => $classSubject->schoolClass?->name)
            ->addColumn('subject_name', fn (ClassSubject $classSubject) => $classSubject->subject?->name)
            ->addColumn('teacher_name', fn (ClassSubject $classSubject) => $classSubject->teacher?->name ?? '-')
            ->addColumn('actions', fn (ClassSubject $classSubject) => view('modules.academics._actions', ['type' => 'class-subject', 'model' => $classSubject])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function classSectionsData(): JsonResponse
    {
        return DataTables::of($this->academics->classSections())
            ->addColumn('class_name', fn (ClassSection $classSection) => $classSection->schoolClass?->name)
            ->addColumn('section_name', fn (ClassSection $classSection) => $classSection->section?->name)
            ->addColumn('teacher_name', fn (ClassSection $classSection) => $classSection->classTeacher?->name ?? '-')
            ->addColumn('actions', fn (ClassSection $classSection) => view('modules.academics._actions', ['type' => 'class-section', 'model' => $classSection])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function storeAcademicYear(SaveAcademicYearRequest $request): JsonResponse
    {
        return $this->jsonCreated('Academic year created successfully.', $this->service->createAcademicYear($request->validated()));
    }

    public function showAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        return $this->jsonData([
            'id' => $academicYear->id,
            'name' => $academicYear->name,
            'starts_on' => $academicYear->starts_on?->toDateString(),
            'ends_on' => $academicYear->ends_on?->toDateString(),
            'is_active' => $academicYear->is_active,
            'status' => $academicYear->status,
        ]);
    }

    public function updateAcademicYear(SaveAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        return $this->jsonCreated('Academic year updated successfully.', $this->service->updateAcademicYear($academicYear, $request->validated()));
    }

    public function destroyAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        $this->authorize('delete', $academicYear);
        $academicYear->delete();

        return $this->jsonMessage('Academic year deleted successfully.');
    }

    public function storeClass(SaveClassRequest $request): JsonResponse
    {
        return $this->jsonCreated('Class created successfully.', $this->service->createClass($request->validated()));
    }

    public function showClass(SchoolClass $class): JsonResponse
    {
        return $this->jsonData($class);
    }

    public function updateClass(SaveClassRequest $request, SchoolClass $class): JsonResponse
    {
        return $this->jsonCreated('Class updated successfully.', $this->service->updateClass($class, $request->validated()));
    }

    public function destroyClass(SchoolClass $class): JsonResponse
    {
        $this->authorize('delete', $class);
        $class->delete();

        return $this->jsonMessage('Class deleted successfully.');
    }

    public function storeSection(SaveSectionRequest $request): JsonResponse
    {
        return $this->jsonCreated('Section created successfully.', $this->service->createSection($request->validated()));
    }

    public function showSection(Section $section): JsonResponse
    {
        return $this->jsonData($section);
    }

    public function updateSection(SaveSectionRequest $request, Section $section): JsonResponse
    {
        return $this->jsonCreated('Section updated successfully.', $this->service->updateSection($section, $request->validated()));
    }

    public function destroySection(Section $section): JsonResponse
    {
        $this->authorize('delete', $section);
        $section->delete();

        return $this->jsonMessage('Section deleted successfully.');
    }

    public function storeClassSection(SaveClassSectionRequest $request): JsonResponse
    {
        return $this->jsonCreated('Class section created successfully.', $this->service->createClassSection($request->validated()));
    }

    public function showClassSection(ClassSection $classSection): JsonResponse
    {
        return $this->jsonData($classSection);
    }

    public function updateClassSection(SaveClassSectionRequest $request, ClassSection $classSection): JsonResponse
    {
        return $this->jsonCreated('Class section updated successfully.', $this->service->updateClassSection($classSection, $request->validated()));
    }

    public function destroyClassSection(ClassSection $classSection): JsonResponse
    {
        $this->authorize('delete', $classSection);
        $this->service->deleteClassSection($classSection);

        return $this->jsonMessage('Class section deleted successfully.');
    }

    public function storeSubject(SaveSubjectRequest $request): JsonResponse
    {
        return $this->jsonCreated('Subject created successfully.', $this->service->createSubject($request->validated()));
    }

    public function showSubject(Subject $subject): JsonResponse
    {
        return $this->jsonData($subject);
    }

    public function updateSubject(SaveSubjectRequest $request, Subject $subject): JsonResponse
    {
        return $this->jsonCreated('Subject updated successfully.', $this->service->updateSubject($subject, $request->validated()));
    }

    public function destroySubject(Subject $subject): JsonResponse
    {
        $this->authorize('delete', $subject);
        $subject->delete();

        return $this->jsonMessage('Subject deleted successfully.');
    }

    public function assignSubject(AssignClassSubjectRequest $request): JsonResponse
    {
        return $this->jsonCreated('Subject assigned successfully.', $this->service->assignSubject($request->validated()));
    }

    public function destroyClassSubject(ClassSubject $classSubject): JsonResponse
    {
        $classSubject->delete();

        return $this->jsonMessage('Class subject removed successfully.');
    }

    private function jsonCreated(string $message, mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    private function jsonData(mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }

    private function jsonMessage(string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message]);
    }
}
