<?php

namespace App\Modules\Library\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publisher extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'library_publishers';

    protected $fillable = [
        'school_id',
        'name',
        'address',
        'contact',
        'status',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'publisher_id');
    }
}
