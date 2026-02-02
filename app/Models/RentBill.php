<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class RentBill extends Model
{
    
    /** @use HasFactory<\Database\Factories\UserFactory> */
        use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable = [
        'invoice_id','tenant_id','bill_month','client_name',
        'rent_items','rent',
        'parking_qty','parking_rate','parking_total',
        'others_cost', 'total',
        'tax_percent','income_tax',
        'balance',
        'rent_advance','amount_to_pay',
        'vat_percent','vat_total',
        'grand_total',
        'status'


    ];
    
    protected $casts = [
        'rent_items' => 'array',
        'bill_month' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

}
