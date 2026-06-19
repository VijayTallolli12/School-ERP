<?php

namespace App\Modules\Library\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Library\Models\FineSetting;
use App\Modules\Library\Repositories\LibraryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class LibraryService
{
    public function __construct(private readonly LibraryRepositoryInterface $library)
    {
    }

    public function createBook(array $data): Book
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $data['available_copies'] = $data['quantity'];
        $book = $this->library->createBook($data);
        activity()->causedBy(auth()->user())->performedOn($book)->event('created')->log('Book created');

        return $book;
    }

    public function updateBook(Book $book, array $data): Book
    {
        $data['available_copies'] = $book->available_copies + ($data['quantity'] - $book->quantity);
        $book = $this->library->updateBook($book, $data);
        activity()->causedBy(auth()->user())->performedOn($book)->event('updated')->log('Book updated');

        return $book;
    }

    public function createCategory(array $data): \App\Modules\Library\Models\Category
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $category = $this->library->createCategory($data);
        activity()->causedBy(auth()->user())->performedOn($category)->event('created')->log('Category created');

        return $category;
    }

    public function updateCategory(\App\Modules\Library\Models\Category $category, array $data): \App\Modules\Library\Models\Category
    {
        $category = $this->library->updateCategory($category, $data);
        activity()->causedBy(auth()->user())->performedOn($category)->event('updated')->log('Category updated');

        return $category;
    }

    public function createAuthor(array $data): \App\Modules\Library\Models\Author
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $author = $this->library->createAuthor($data);
        activity()->causedBy(auth()->user())->performedOn($author)->event('created')->log('Author created');

        return $author;
    }

    public function updateAuthor(\App\Modules\Library\Models\Author $author, array $data): \App\Modules\Library\Models\Author
    {
        $author = $this->library->updateAuthor($author, $data);
        activity()->causedBy(auth()->user())->performedOn($author)->event('updated')->log('Author updated');

        return $author;
    }

    public function createPublisher(array $data): \App\Modules\Library\Models\Publisher
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $publisher = $this->library->createPublisher($data);
        activity()->causedBy(auth()->user())->performedOn($publisher)->event('created')->log('Publisher created');

        return $publisher;
    }

    public function updatePublisher(\App\Modules\Library\Models\Publisher $publisher, array $data): \App\Modules\Library\Models\Publisher
    {
        $publisher = $this->library->updatePublisher($publisher, $data);
        activity()->causedBy(auth()->user())->performedOn($publisher)->event('updated')->log('Publisher updated');

        return $publisher;
    }

    public function issueBook(array $data): BookIssue
    {
        return DB::transaction(function () use ($data): BookIssue {
            $data['school_id'] = app(SchoolContext::class)->id();

            $book = Book::query()->findOrFail($data['book_id']);
            if ($book->available_copies < 1) {
                throw new \RuntimeException('No copies available for issue.');
            }

            $fineSetting = FineSetting::query()->where('school_id', $data['school_id'])->where('status', 'active')->first();
            $graceDays = $fineSetting?->grace_period_days ?? 0;

            $data['issue_date'] = $data['issue_date'] ?? now()->format('Y-m-d');
            $data['due_date'] = $data['due_date'] ?? now()->addDays(14)->format('Y-m-d');
            $data['status'] = 'issued';

            $issue = $this->library->createIssue($data);

            $book->decrement('available_copies');

            activity()->causedBy(auth()->user())->performedOn($issue)->event('created')->log('Book issued');

            return $issue;
        });
    }

    public function returnBook(BookIssue $issue, array $data): BookIssue
    {
        return DB::transaction(function () use ($issue, $data): BookIssue {
            $returnDate = $data['return_date'] ?? now()->format('Y-m-d');
            $data['return_date'] = $returnDate;
            $data['status'] = 'returned';

            $fineSetting = FineSetting::query()->where('school_id', $issue->school_id)->where('status', 'active')->first();
            $finePerDay = $fineSetting?->fine_per_day ?? 0;
            $maxFine = $fineSetting?->max_fine;
            $graceDays = $fineSetting?->grace_period_days ?? 0;

            $dueDate = \Carbon\Carbon::parse($issue->due_date);
            $return = \Carbon\Carbon::parse($returnDate);
            $overdueDays = $dueDate->diffInDays($return, false);

            if ($overdueDays > $graceDays) {
                $fine = ($overdueDays - $graceDays) * (float) $finePerDay;
                if ($maxFine !== null) {
                    $fine = min($fine, (float) $maxFine);
                }
                $data['fine_amount'] = round($fine, 2);
            } else {
                $data['fine_amount'] = 0;
            }

            $issue = $this->library->updateIssue($issue, $data);
            $issue->book()->increment('available_copies');

            activity()->causedBy(auth()->user())->performedOn($issue)->event('updated')->log('Book returned');

            return $issue;
        });
    }

    public function createFineSetting(array $data): FineSetting
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        FineSetting::query()->where('school_id', $data['school_id'])->update(['status' => 'inactive']);
        $setting = $this->library->createFineSetting($data);
        activity()->causedBy(auth()->user())->performedOn($setting)->event('created')->log('Fine setting created');

        return $setting;
    }

    public function updateFineSetting(FineSetting $setting, array $data): FineSetting
    {
        $setting = $this->library->updateFineSetting($setting, $data);
        activity()->causedBy(auth()->user())->performedOn($setting)->event('updated')->log('Fine setting updated');

        return $setting;
    }
}
