<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\BookIssue;

class LibrarianDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Librarian';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $totalBooks = Book::query()->count();
        $issuedBooks = BookIssue::query()->whereNull('returned_at')->count();
        $overdueBooks = BookIssue::query()->whereNull('returned_at')->where('due_date', '<', now())->count();
        $availableBooks = $totalBooks - $issuedBooks;

        return [
            $this->statCard('Total Books', $totalBooks, 'books', 'primary', null, null, route('admin.library.index')),
            $this->statCard('Issued Books', $issuedBooks, 'book-open', 'info'),
            $this->statCard('Overdue Books', $overdueBooks, 'exclamation-triangle', 'danger'),
            $this->statCard('Available Books', $availableBooks, 'book', 'success'),
        ];
    }

    protected function buildWidgets(): array
    {
        return [];
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Manage Books', route('admin.library.index'), 'books', 'primary', 'library.view'),
            $this->quickAction('Issue Book', route('admin.library.index'), 'book-open', 'success', 'library.create'),
        ];
    }

    protected function buildCharts(): array
    {
        return [];
    }
}