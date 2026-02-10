<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeExpenseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'head_id',
        'amount',
        'remark',
    ];

    public function group()
    {
        return $this->belongsTo(OfficeExpenseGroup::class, 'group_id');
    }

    public function head()
    {
        return $this->belongsTo(OfficeExpenseHead::class, 'head_id');
    }
}
