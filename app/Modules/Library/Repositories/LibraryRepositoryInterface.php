<?php

namespace App\Modules\Library\Repositories;

use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Library\Models\Category;
use App\Modules\Library\Models\Author;
use App\Modules\Library\Models\Publisher;
use App\Modules\Library\Models\FineSetting;
use Illuminate\Database\Eloquent\Builder;

interface LibraryRepositoryInterface
{
    public function books(): Builder;
    public function categories(): Builder;
    public function authors(): Builder;
    public function publishers(): Builder;
    public function issues(): Builder;
    public function fineSettings(): Builder;

    public function createBook(array $data): Book;
    public function updateBook(Book $book, array $data): Book;

    public function createCategory(array $data): Category;
    public function updateCategory(Category $category, array $data): Category;

    public function createAuthor(array $data): Author;
    public function updateAuthor(Author $author, array $data): Author;

    public function createPublisher(array $data): Publisher;
    public function updatePublisher(Publisher $publisher, array $data): Publisher;

    public function createIssue(array $data): BookIssue;
    public function updateIssue(BookIssue $issue, array $data): BookIssue;

    public function createFineSetting(array $data): FineSetting;
    public function updateFineSetting(FineSetting $setting, array $data): FineSetting;
}
