<?php

namespace App\Modules\Library\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'library_categories';

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'category_id');
    }
}
