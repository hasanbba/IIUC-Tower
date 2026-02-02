<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    
    protected $fillable = [
        'client_name',
        'client_id',
        'rent_increase',
        'contact_address',
        'agreement_year',
        'rent_start_date',
        'expired_date',
        'floor',
        'rent_items',
        'total_rent',
        'rent_advance',
        'status',
    ];

    protected $casts = [
        'rent_items' => 'array',
        'rent_start_date' => 'date',
        'expired_date' => 'date',
    ];

    public function getCurrentRentAttribute(): float
    {
        // Base checks
        if (! $this->rent_start_date || ! $this->total_rent) {
            return (float) $this->total_rent;
        }

        $baseRent = (float) $this->total_rent;
        $increasePercent = (float) $this->rent_increase;

        $start = Carbon::parse($this->rent_start_date);
        $end = $this->expired_date ? Carbon::parse($this->expired_date) : now();

        // Stop calculation at expired_date
        $calcDate = now()->gt($end) ? $end : now();

        // Full 12-month periods passed
        $monthsPassed = $start->diffInMonths($calcDate);
        $fullYears = intdiv($monthsPassed, 12);

        $currentRent = $baseRent;

        for ($i = 0; $i < $fullYears; $i++) {
            $currentRent += ($currentRent * $increasePercent / 100);
        }

        return round($currentRent, 2);
    }

    // ✅ AUTO STATUS
    public function getStatusAttribute($value)
    {
        if ($this->expired_date && now()->gt($this->expired_date)) {
            return 'expired';
        }

        return $value;
    }

    protected static function booted()
    {
        static::retrieved(function ($tenant) {
            if ($tenant->expired_date && now()->gt($tenant->expired_date) && $tenant->status !== 'expired') {
                $tenant->updateQuietly(['status' => 'expired']);
            }
        });
    }


}
