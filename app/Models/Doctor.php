<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Doctor extends Model
{
    use HasTranslations, SoftDeletes;

    /** 0 = Sunday, the same as Carbon. Listed from Saturday, the Bangladesh week start. */
    public const DAYS = [6 => ['শনি', 'Sat'], 0 => ['রবি', 'Sun'], 1 => ['সোম', 'Mon'], 2 => ['মঙ্গল', 'Tue'], 3 => ['বুধ', 'Wed'], 4 => ['বৃহস্পতি', 'Thu'], 5 => ['শুক্র', 'Fri']];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'days' => 'array',
            'fee' => 'decimal:2',
            'accepts_appointments' => 'boolean',
            'show_on_website' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->where('status', true)->where('show_on_website', true);
    }

    /** "শনি, সোম, বুধ · ৪:০০ PM – ৮:০০ PM" in the current language. */
    public function scheduleText(): string
    {
        $en = app()->getLocale() === 'en';
        $days = collect(self::DAYS)->filter(fn ($l, $d) => in_array($d, array_map('intval', $this->days ?? []), true))->map(fn ($l) => $l[$en ? 1 : 0]);
        $label = $days->count() === 7 ? ($en ? 'Every day' : 'প্রতিদিন') : $days->implode(', ');
        $time = $this->time_from && $this->time_to
            ? Carbon::parse($this->time_from)->format('g:i A').' – '.Carbon::parse($this->time_to)->format('g:i A') : '';
        $note = $this->localized('schedule_note');

        return collect([$label, $time, $note])->filter()->implode(' · ');
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset($this->photo) : null;
    }
}
