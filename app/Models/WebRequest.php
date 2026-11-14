<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Something a visitor sent from the website: appointment, home collection, contact message, corporate quote. */
class WebRequest extends Model
{
    public const TYPES = [
        'appointment' => ['Appointment', 'fas fa-calendar-check'],
        'home_collection' => ['Home collection', 'fas fa-house-medical'],
        'contact' => ['Message', 'fas fa-envelope'],
        'corporate' => ['Corporate quote', 'fas fa-briefcase'],
    ];

    public const STATUSES = ['new' => 'New', 'confirmed' => 'Confirmed', 'done' => 'Done', 'cancelled' => 'Cancelled'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['preferred_date' => 'date', 'handled_at' => 'datetime'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }

    public function statusBadge(): string
    {
        $class = ['new' => 'danger', 'confirmed' => 'primary', 'done' => 'success', 'cancelled' => 'secondary'][$this->status] ?? 'light';

        return '<span class="badge text-bg-'.$class.'">'.e(self::STATUSES[$this->status] ?? $this->status).'</span>';
    }
}
