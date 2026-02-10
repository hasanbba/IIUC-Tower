<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeExpenseGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_date',
        'bill_no',
        'total_amount',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(OfficeExpenseItem::class, 'group_id');
    }
}
