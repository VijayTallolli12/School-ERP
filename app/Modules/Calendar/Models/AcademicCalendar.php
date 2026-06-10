<?php

namespace App\Modules\Calendar\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicCalendar extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $table = 'academic_calendars';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'title',
        'event_type',
        'start_date',
        'end_date',
        'description',
        'audience',
        'location',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getEventTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            'holiday' => 'Holiday',
            'exam' => 'Exam',
            'school_event' => 'School Event',
            'ptm' => 'PTM',
            'sports_day' => 'Sports Day',
            'annual_day' => 'Annual Day',
            'field_trip' => 'Field Trip',
            'workshop' => 'Workshop',
            default => 'Other',
        };
    }

    public function getEventTypeBadgeAttribute(): string
    {
        return static::badgeClass($this->event_type);
    }

    public static function badgeClass(string $eventType): string
    {
        return match ($eventType) {
            'holiday' => 'bg-danger',
            'exam' => 'bg-warning text-dark',
            'school_event' => 'bg-info text-dark',
            'ptm' => 'bg-primary',
            'sports_day' => 'bg-success',
            'annual_day' => 'bg-dark',
            'field_trip' => 'bg-calendar-field-trip',
            'workshop' => 'bg-calendar-workshop',
            default => 'bg-light text-dark',
        };
    }

    public function getAudienceLabelAttribute(): string
    {
        return ucfirst($this->audience);
    }

    public function getPublishedBadgeAttribute(): string
    {
        return $this->is_published
            ? '<span class="badge bg-success">Published</span>'
            : '<span class="badge bg-secondary">Draft</span>';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query, ?int $limit = null)
    {
        $q = $query->where('start_date', '>=', today())->orderBy('start_date');

        if ($limit) {
            $q->limit($limit);
        }

        return $q;
    }

    public function scopeByMonth($query, int $year, int $month)
    {
        return $query->whereYear('start_date', $year)
            ->whereMonth('start_date', $month);
    }

    public static function eventTypes(): array
    {
        return [
            'holiday',
            'exam',
            'school_event',
            'ptm',
            'sports_day',
            'annual_day',
            'field_trip',
            'workshop',
            'other',
        ];
    }

    public static function audiences(): array
    {
        return ['all', 'students', 'parents', 'teachers', 'staff'];
    }
}
