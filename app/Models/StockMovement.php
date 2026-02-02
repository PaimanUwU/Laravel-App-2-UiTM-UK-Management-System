<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $primaryKey = 'stock_id';
    const UPDATED_AT = null;

    protected $fillable = [
        'meds_id',
        'quantity',
        'type',
        'reason',
        'user_id'
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class, 'meds_id', 'meds_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
