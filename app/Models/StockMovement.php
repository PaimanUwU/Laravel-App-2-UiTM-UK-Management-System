<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $primaryKey = 'stock_ID';
    public $timestamps = false;

    protected $fillable = [
        'meds_ID',
        'quantity',
        'type',
        'reason',
        'user_id'
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class, 'meds_ID', 'meds_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
