<?php

namespace App\Modules\Library\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Library\Exports\LibraryReportExport;
use App\Modules\Library\Models\Author;
use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Library\Models\Category;
use App\Modules\Library\Models\FineSetting;
use App\Modules\Library\Models\Publisher;
use App\Modules\Library\Repositories\LibraryRepositoryInterface;
use App\Modules\Library\Requests\IssueBookRequest;
use App\Modules\Library\Requests\ReturnBookRequest;
use App\Modules\Library\Requests\StoreAuthorRequest;
use App\Modules\Library\Requests\StoreBookRequest;
use App\Modules\Library\Requests\StoreCategoryRequest;
use App\Modules\Library\Requests\StoreFineSettingRequest;
use App\Modules\Library\Requests\StorePublisherRequest;
use App\Modules\Library\Requests\UpdateAuthorRequest;
use App\Modules\Library\Requests\UpdateBookRequest;
use App\Modules\Library\Requests\UpdateCategoryRequest;
use App\Modules\Library\Requests\UpdatePublisherRequest;
use App\Modules\Library\Services\LibraryService;
use App\Modules\Students\Models\Student;
use App\Modules\Teachers\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LibraryController extends Controller
{
    public function __construct(
        private readonly LibraryRepositoryInterface $library,
        private readonly LibraryService $service,
    ) {
    }

    public function index()
    {
        return view('modules.library.index', [
            'categories' => Category::query()->orderBy('name')->get(),
            'authors' => Author::query()->orderBy('name')->get(),
            'publishers' => Publisher::query()->orderBy('name')->get(),
            'books' => Book::query()->with('category')->orderBy('title')->get(),
            'fineSetting' => FineSetting::query()->where('status', 'active')->first(),
            'students' => Student::query()->orderBy('first_name')->get(),
            'teachers' => Teacher::query()->orderBy('first_name')->get(),
        ]);
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $limit = min((int) $request->get('limit', 20), 50);

        $students = Student::query()
            ->where(function ($query) use ($q): void {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('admission_no', 'like', "%{$q}%");
            })
            ->orderBy('first_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $students->map(fn (Student $s) => [
                'id' => $s->id,
                'text' => sprintf('%s (%s)', $s->full_name, $s->admission_no),
            ]),
        ]);
    }

    public function searchTeachers(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $limit = min((int) $request->get('limit', 20), 50);

        $teachers = Teacher::query()
            ->where(function ($query) use ($q): void {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('employee_code', 'like', "%{$q}%");
            })
            ->orderBy('first_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $teachers->map(fn (Teacher $t) => [
                'id' => $t->id,
                'text' => sprintf('%s (%s)', $t->full_name, $t->employee_code ?? 'N/A'),
            ]),
        ]);
    }

    // ─── Books ──────────────────────────────────────────────────────────

    public function booksData(): JsonResponse
    {
        return DataTables::of($this->library->books())
            ->addColumn('category_name', fn (Book $b) => $b->category?->name ?? '-')
            ->addColumn('author_name', fn (Book $b) => $b->author?->name ?? '-')
            ->addColumn('publisher_name', fn (Book $b) => $b->publisher?->name ?? '-')
            ->editColumn('status', fn (Book $b) => '<span class="badge bg-'.($b->status === 'active' ? 'success' : 'secondary').'">'.$b->status.'</span>')
            ->addColumn('actions', fn (Book $b) => view('modules.library._actions', ['type' => 'book', 'model' => $b])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeBook(StoreBookRequest $request): JsonResponse
    {
        return $this->jsonCreated('Book created successfully.', $this->service->createBook($request->validated()));
    }

    public function showBook(Book $book): JsonResponse
    {
        return $this->jsonData($book->load(['category', 'author', 'publisher']));
    }

    public function updateBook(UpdateBookRequest $request, Book $book): JsonResponse
    {
        return $this->jsonCreated('Book updated successfully.', $this->service->updateBook($book, $request->validated()));
    }

    public function destroyBook(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);
        $book->delete();

        return $this->jsonMessage('Book deleted successfully.');
    }

    // ─── Categories ──────────────────────────────────────────────────────

    public function categoriesData(): JsonResponse
    {
        return DataTables::of($this->library->categories())
            ->editColumn('status', fn (Category $c) => '<span class="badge bg-'.($c->status === 'active' ? 'success' : 'secondary').'">'.$c->status.'</span>')
            ->addColumn('actions', fn (Category $c) => view('modules.library._actions', ['type' => 'category', 'model' => $c])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeCategory(StoreCategoryRequest $request): JsonResponse
    {
        return $this->jsonCreated('Category created successfully.', $this->service->createCategory($request->validated()));
    }

    public function showCategory(Category $category): JsonResponse
    {
        return $this->jsonData($category);
    }

    public function updateCategory(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        return $this->jsonCreated('Category updated successfully.', $this->service->updateCategory($category, $request->validated()));
    }

    public function destroyCategory(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);
        $category->delete();

        return $this->jsonMessage('Category deleted successfully.');
    }

    // ─── Authors ─────────────────────────────────────────────────────────

    public function authorsData(): JsonResponse
    {
        return DataTables::of($this->library->authors())
            ->editColumn('status', fn (Author $a) => '<span class="badge bg-'.($a->status === 'active' ? 'success' : 'secondary').'">'.$a->status.'</span>')
            ->addColumn('actions', fn (Author $a) => view('modules.library._actions', ['type' => 'author', 'model' => $a])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeAuthor(StoreAuthorRequest $request): JsonResponse
    {
        return $this->jsonCreated('Author created successfully.', $this->service->createAuthor($request->validated()));
    }

    public function showAuthor(Author $author): JsonResponse
    {
        return $this->jsonData($author);
    }

    public function updateAuthor(UpdateAuthorRequest $request, Author $author): JsonResponse
    {
        return $this->jsonCreated('Author updated successfully.', $this->service->updateAuthor($author, $request->validated()));
    }

    public function destroyAuthor(Author $author): JsonResponse
    {
        $this->authorize('delete', $author);
        $author->delete();

        return $this->jsonMessage('Author deleted successfully.');
    }

    // ─── Publishers ──────────────────────────────────────────────────────

    public function publishersData(): JsonResponse
    {
        return DataTables::of($this->library->publishers())
            ->editColumn('status', fn (Publisher $p) => '<span class="badge bg-'.($p->status === 'active' ? 'success' : 'secondary').'">'.$p->status.'</span>')
            ->addColumn('actions', fn (Publisher $p) => view('modules.library._actions', ['type' => 'publisher', 'model' => $p])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storePublisher(StorePublisherRequest $request): JsonResponse
    {
        return $this->jsonCreated('Publisher created successfully.', $this->service->createPublisher($request->validated()));
    }

    public function showPublisher(Publisher $publisher): JsonResponse
    {
        return $this->jsonData($publisher);
    }

    public function updatePublisher(UpdatePublisherRequest $request, Publisher $publisher): JsonResponse
    {
        return $this->jsonCreated('Publisher updated successfully.', $this->service->updatePublisher($publisher, $request->validated()));
    }

    public function destroyPublisher(Publisher $publisher): JsonResponse
    {
        $this->authorize('delete', $publisher);
        $publisher->delete();

        return $this->jsonMessage('Publisher deleted successfully.');
    }

    // ─── Book Issues ─────────────────────────────────────────────────────

    public function issuesData(): JsonResponse
    {
        return DataTables::of($this->library->issues())
            ->addColumn('book_title', fn (BookIssue $i) => $i->book?->title ?? '-')
            ->addColumn('borrower', fn (BookIssue $i) => $this->getBorrowerName($i))
            ->addColumn('is_overdue', fn (BookIssue $i) => $i->status === 'issued' && now()->isAfter($i->due_date)
                ? '<span class="badge bg-danger">Yes</span>'
                : '<span class="badge bg-success">No</span>')
            ->editColumn('status', fn (BookIssue $i) => '<span class="badge bg-'.($i->status === 'issued' ? 'primary' : ($i->status === 'returned' ? 'success' : 'secondary')).'">'.$i->status.'</span>')
            ->editColumn('fine_amount', fn (BookIssue $i) => '<span class="text-end d-block">'.number_format((float) $i->fine_amount, 2).'</span>')
            ->addColumn('actions', fn (BookIssue $i) => view('modules.library._actions', ['type' => 'issue', 'model' => $i])->render())
            ->rawColumns(['is_overdue', 'status', 'fine_amount', 'actions'])
            ->toJson();
    }

    private function getBorrowerName(BookIssue $issue): string
    {
        if (!$issue->issueable) {
            return '<span class="text-secondary">Unknown</span>';
        }

        return $issue->issueable->full_name;
    }

    public function issueBook(IssueBookRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['issueable_type'] = match ($data['issueable_type']) {
                'student' => \App\Modules\Students\Models\Student::class,
                'teacher' => \App\Modules\Teachers\Models\Teacher::class,
                default => $data['issueable_type'],
            };
            $issue = $this->service->issueBook($data);

            return $this->jsonCreated('Book issued successfully.', $issue);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function showIssue(BookIssue $issue): JsonResponse
    {
        return $this->jsonData($issue->load(['book', 'issueable']));
    }

    public function returnBook(ReturnBookRequest $request, BookIssue $issue): JsonResponse
    {
        try {
            $issue = $this->service->returnBook($issue, $request->validated());

            return $this->jsonCreated('Book returned successfully. Fine: ₹'.number_format((float) $issue->fine_amount, 2), $issue);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroyIssue(BookIssue $issue): JsonResponse
    {
        $this->authorize('delete', $issue);
        $issue->delete();

        return $this->jsonMessage('Issue record deleted successfully.');
    }

    // ─── Fine Settings ───────────────────────────────────────────────────

    public function fineSettingsData(): JsonResponse
    {
        return DataTables::of($this->library->fineSettings())
            ->editColumn('fine_per_day', fn (FineSetting $f) => number_format((float) $f->fine_per_day, 2))
            ->editColumn('max_fine', fn (FineSetting $f) => $f->max_fine !== null ? number_format((float) $f->max_fine, 2) : '-')
            ->editColumn('status', fn (FineSetting $f) => '<span class="badge bg-'.($f->status === 'active' ? 'success' : 'secondary').'">'.$f->status.'</span>')
            ->addColumn('actions', fn (FineSetting $f) => view('modules.library._actions', ['type' => 'fine-setting', 'model' => $f])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeFineSetting(StoreFineSettingRequest $request): JsonResponse
    {
        return $this->jsonCreated('Fine setting saved successfully.', $this->service->createFineSetting($request->validated()));
    }

    public function showFineSetting(FineSetting $fineSetting): JsonResponse
    {
        return $this->jsonData($fineSetting);
    }

    public function updateFineSetting(StoreFineSettingRequest $request, FineSetting $fineSetting): JsonResponse
    {
        return $this->jsonCreated('Fine setting updated successfully.', $this->service->updateFineSetting($fineSetting, $request->validated()));
    }

    public function destroyFineSetting(FineSetting $fineSetting): JsonResponse
    {
        $fineSetting->delete();

        return $this->jsonMessage('Fine setting deleted successfully.');
    }

    // ─── Reports ─────────────────────────────────────────────────────────

    public function reports()
    {
        return view('modules.library.reports', [
            'categories' => Category::query()->orderBy('name')->get(),
            'books' => Book::query()->orderBy('title')->get(),
            'students' => Student::query()->orderBy('first_name')->get(),
            'teachers' => Teacher::query()->orderBy('first_name')->get(),
        ]);
    }

    public function booksReportData(Request $request): JsonResponse
    {
        $query = Book::query()->with(['category', 'author', 'publisher']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        return DataTables::of($query)
            ->addColumn('category_name', fn (Book $b) => $b->category?->name ?? '-')
            ->addColumn('author_name', fn (Book $b) => $b->author?->name ?? '-')
            ->addColumn('publisher_name', fn (Book $b) => $b->publisher?->name ?? '-')
            ->editColumn('status', fn (Book $b) => '<span class="badge bg-'.($b->status === 'active' ? 'success' : 'secondary').'">'.$b->status.'</span>')
            ->rawColumns(['status'])
            ->toJson();
    }

    public function issuedBooksReportData(Request $request): JsonResponse
    {
        $query = BookIssue::query()->with(['book', 'issueable'])->where('status', 'issued');

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }
        if ($request->filled('borrower_type')) {
            $modelClass = $request->borrower_type === 'student' ? Student::class : Teacher::class;
            $query->where('issueable_type', $modelClass);
        }

        return DataTables::of($query)
            ->addColumn('book_title', fn (BookIssue $i) => $i->book?->title ?? '-')
            ->addColumn('borrower', fn (BookIssue $i) => $i->issueable?->full_name ?? '-')
            ->editColumn('issueable_type', fn (BookIssue $i) => class_basename($i->issueable_type))
            ->addColumn('overdue_days', fn (BookIssue $i) => now()->isAfter($i->due_date) ? now()->diffInDays($i->due_date) : 0)
            ->editColumn('status', fn (BookIssue $i) => '<span class="badge bg-primary">'.$i->status.'</span>')
            ->rawColumns(['status'])
            ->toJson();
    }

    public function overdueBooksReportData(Request $request): JsonResponse
    {
        $query = BookIssue::query()->with(['book', 'issueable'])
            ->where('status', 'issued')
            ->where('due_date', '<', now());

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        return DataTables::of($query)
            ->addColumn('book_title', fn (BookIssue $i) => $i->book?->title ?? '-')
            ->addColumn('borrower', fn (BookIssue $i) => $i->issueable?->full_name ?? '-')
            ->addColumn('overdue_days', fn (BookIssue $i) => now()->diffInDays($i->due_date))
            ->editColumn('status', fn (BookIssue $i) => '<span class="badge bg-danger">Overdue</span>')
            ->rawColumns(['status'])
            ->toJson();
    }

    public function fineCollectionReportData(Request $request): JsonResponse
    {
        $query = BookIssue::query()->with(['book', 'issueable'])
            ->where('fine_amount', '>', 0);

        if ($request->filled('from_date')) {
            $query->whereDate('return_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('return_date', '<=', $request->to_date);
        }

        return DataTables::of($query)
            ->addColumn('book_title', fn (BookIssue $i) => $i->book?->title ?? '-')
            ->addColumn('borrower', fn (BookIssue $i) => $i->issueable?->full_name ?? '-')
            ->editColumn('fine_amount', fn (BookIssue $i) => '<span class="text-end d-block">'.number_format((float) $i->fine_amount, 2).'</span>')
            ->editColumn('fine_paid', fn (BookIssue $i) => $i->fine_paid
                ? '<span class="badge bg-success">Paid</span>'
                : '<span class="badge bg-warning">Unpaid</span>')
            ->rawColumns(['fine_amount', 'fine_paid'])
            ->toJson();
    }

    public function studentHistoryReportData(Request $request): JsonResponse
    {
        $query = BookIssue::query()->with(['book'])
            ->where('issueable_type', Student::class);

        if ($request->filled('student_id')) {
            $query->where('issueable_id', $request->student_id);
        }

        return DataTables::of($query)
            ->addColumn('student', fn (BookIssue $i) => $i->issueable?->full_name ?? '-')
            ->addColumn('book_title', fn (BookIssue $i) => $i->book?->title ?? '-')
            ->addColumn('isbn', fn (BookIssue $i) => $i->book?->isbn ?? '-')
            ->editColumn('fine_amount', fn (BookIssue $i) => '<span class="text-end d-block">'.number_format((float) $i->fine_amount, 2).'</span>')
            ->editColumn('status', fn (BookIssue $i) => '<span class="badge bg-'.($i->status === 'returned' ? 'success' : 'primary').'">'.$i->status.'</span>')
            ->rawColumns(['fine_amount', 'status'])
            ->toJson();
    }

    public function teacherHistoryReportData(Request $request): JsonResponse
    {
        $query = BookIssue::query()->with(['book'])
            ->where('issueable_type', Teacher::class);

        if ($request->filled('teacher_id')) {
            $query->where('issueable_id', $request->teacher_id);
        }

        return DataTables::of($query)
            ->addColumn('teacher', fn (BookIssue $i) => $i->issueable?->full_name ?? '-')
            ->addColumn('book_title', fn (BookIssue $i) => $i->book?->title ?? '-')
            ->addColumn('isbn', fn (BookIssue $i) => $i->book?->isbn ?? '-')
            ->editColumn('fine_amount', fn (BookIssue $i) => '<span class="text-end d-block">'.number_format((float) $i->fine_amount, 2).'</span>')
            ->editColumn('status', fn (BookIssue $i) => '<span class="badge bg-'.($i->status === 'returned' ? 'success' : 'primary').'">'.$i->status.'</span>')
            ->rawColumns(['fine_amount', 'status'])
            ->toJson();
    }

    // ─── Exports ─────────────────────────────────────────────────────────

    public function exportExcel(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);

        return Excel::download(
            new LibraryReportExport($data, $report),
            "library_{$report}_".now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportPdf(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);
        $title = str($report)->replace('_', ' ')->headline().' Report';

        return Pdf::loadView('modules.library.reports_pdf', compact('data', 'title', 'report'))
            ->setPaper('a4', 'landscape')
            ->download("library_{$report}_".now()->format('Ymd_His').'.pdf');
    }

    public function printReport(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);
        $title = str($report)->replace('_', ' ')->headline().' Report';

        return view('modules.library.reports_print', compact('data', 'title', 'report'));
    }

    private function getReportData(Request $request, string $report): array
    {
        return match ($report) {
            'books_inventory' => Book::query()->with(['category', 'author', 'publisher'])
                ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('language'), fn ($q) => $q->where('language', $request->language))
                ->get()
                ->map(fn (Book $b) => [
                    'isbn' => $b->isbn ?? '-',
                    'title' => $b->title,
                    'category' => $b->category?->name ?? '-',
                    'author' => $b->author?->name ?? '-',
                    'publisher' => $b->publisher?->name ?? '-',
                    'edition' => $b->edition ?? '-',
                    'language' => $b->language,
                    'rack_number' => $b->rack_number ?? '-',
                    'quantity' => $b->quantity,
                    'available_copies' => $b->available_copies,
                    'status' => $b->status,
                ])->toArray(),

            'issued_books' => BookIssue::query()->with(['book', 'issueable'])->where('status', 'issued')
                ->when($request->filled('book_id'), fn ($q) => $q->where('book_id', $request->book_id))
                ->get()
                ->map(fn (BookIssue $i) => [
                    'book' => $i->book?->title ?? '-',
                    'borrower' => $i->issueable?->full_name ?? '-',
                    'borrower_type' => class_basename($i->issueable_type),
                    'issue_date' => $i->issue_date->format('d M Y'),
                    'due_date' => $i->due_date->format('d M Y'),
                    'overdue_days' => now()->isAfter($i->due_date) ? now()->diffInDays($i->due_date) : 0,
                ])->toArray(),

            'overdue_books' => BookIssue::query()->with(['book', 'issueable'])
                ->where('status', 'issued')->where('due_date', '<', now())
                ->get()
                ->map(fn (BookIssue $i) => [
                    'book' => $i->book?->title ?? '-',
                    'borrower' => $i->issueable?->full_name ?? '-',
                    'issue_date' => $i->issue_date->format('d M Y'),
                    'due_date' => $i->due_date->format('d M Y'),
                    'overdue_days' => now()->diffInDays($i->due_date),
                ])->toArray(),

            'fine_collection' => BookIssue::query()->with(['book', 'issueable'])
                ->where('fine_amount', '>', 0)
                ->when($request->filled('from_date'), fn ($q) => $q->whereDate('return_date', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn ($q) => $q->whereDate('return_date', '<=', $request->to_date))
                ->get()
                ->map(fn (BookIssue $i) => [
                    'book' => $i->book?->title ?? '-',
                    'borrower' => $i->issueable?->full_name ?? '-',
                    'return_date' => $i->return_date?->format('d M Y') ?? '-',
                    'fine_amount' => number_format((float) $i->fine_amount, 2),
                    'fine_paid' => $i->fine_paid ? 'Paid' : 'Unpaid',
                ])->toArray(),

            'student_history' => BookIssue::query()->with(['book'])
                ->where('issueable_type', Student::class)
                ->when($request->filled('student_id'), fn ($q) => $q->where('issueable_id', $request->student_id))
                ->get()
                ->map(fn (BookIssue $i) => [
                    'student' => $i->issueable?->full_name ?? '-',
                    'book' => $i->book?->title ?? '-',
                    'isbn' => $i->book?->isbn ?? '-',
                    'issue_date' => $i->issue_date->format('d M Y'),
                    'due_date' => $i->due_date->format('d M Y'),
                    'return_date' => $i->return_date?->format('d M Y') ?? 'Not returned',
                    'fine' => number_format((float) $i->fine_amount, 2),
                    'status' => $i->status,
                ])->toArray(),

            'teacher_history' => BookIssue::query()->with(['book'])
                ->where('issueable_type', Teacher::class)
                ->when($request->filled('teacher_id'), fn ($q) => $q->where('issueable_id', $request->teacher_id))
                ->get()
                ->map(fn (BookIssue $i) => [
                    'teacher' => $i->issueable?->full_name ?? '-',
                    'book' => $i->book?->title ?? '-',
                    'isbn' => $i->book?->isbn ?? '-',
                    'issue_date' => $i->issue_date->format('d M Y'),
                    'due_date' => $i->due_date->format('d M Y'),
                    'return_date' => $i->return_date?->format('d M Y') ?? 'Not returned',
                    'fine' => number_format((float) $i->fine_amount, 2),
                    'status' => $i->status,
                ])->toArray(),

            default => [],
        };
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

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
