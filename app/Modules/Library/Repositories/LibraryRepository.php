<?php

namespace App\Modules\Library\Repositories;

use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Library\Models\Category;
use App\Modules\Library\Models\Author;
use App\Modules\Library\Models\Publisher;
use App\Modules\Library\Models\FineSetting;
use Illuminate\Database\Eloquent\Builder;

class LibraryRepository implements LibraryRepositoryInterface
{
    public function books(): Builder
    {
        return Book::query()->with(['category', 'author', 'publisher'])->orderBy('title');
    }

    public function categories(): Builder
    {
        return Category::query()->withCount('books')->orderBy('sort_order')->orderBy('name');
    }

    public function authors(): Builder
    {
        return Author::query()->withCount('books')->orderBy('name');
    }

    public function publishers(): Builder
    {
        return Publisher::query()->withCount('books')->orderBy('name');
    }

    public function issues(): Builder
    {
        return BookIssue::query()->with(['book', 'issueable'])->latest();
    }

    public function fineSettings(): Builder
    {
        return FineSetting::query()->latest();
    }

    public function createBook(array $data): Book
    {
        return Book::query()->create($data);
    }

    public function updateBook(Book $book, array $data): Book
    {
        $book->fill($data)->save();
        return $book->refresh();
    }

    public function createCategory(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->fill($data)->save();
        return $category->refresh();
    }

    public function createAuthor(array $data): Author
    {
        return Author::query()->create($data);
    }

    public function updateAuthor(Author $author, array $data): Author
    {
        $author->fill($data)->save();
        return $author->refresh();
    }

    public function createPublisher(array $data): Publisher
    {
        return Publisher::query()->create($data);
    }

    public function updatePublisher(Publisher $publisher, array $data): Publisher
    {
        $publisher->fill($data)->save();
        return $publisher->refresh();
    }

    public function createIssue(array $data): BookIssue
    {
        return BookIssue::query()->create($data);
    }

    public function updateIssue(BookIssue $issue, array $data): BookIssue
    {
        $issue->fill($data)->save();
        return $issue->refresh();
    }

    public function createFineSetting(array $data): FineSetting
    {
        return FineSetting::query()->create($data);
    }

    public function updateFineSetting(FineSetting $setting, array $data): FineSetting
    {
        $setting->fill($data)->save();
        return $setting->refresh();
    }
}
