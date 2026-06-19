<?php

namespace App\Modules\Library\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'library_authors';

    protected $fillable = [
        'school_id',
        'name',
        'biography',
        'status',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }
}
