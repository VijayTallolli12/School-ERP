<?php

namespace App\Modules\Library\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'library_books';

    protected $fillable = [
        'school_id',
        'isbn',
        'title',
        'category_id',
        'author_id',
        'publisher_id',
        'edition',
        'language',
        'rack_number',
        'quantity',
        'available_copies',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'available_copies' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class, 'book_id');
    }
}
