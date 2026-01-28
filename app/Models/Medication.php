<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
  use HasFactory;

  protected $table = 'medications';
  protected $primaryKey = 'meds_id';

  protected $fillable = [
    'meds_name',
    'meds_type',
    'stock_quantity',
    'min_threshold',
  ];

  public function stockMovements()
  {
    return $this->hasMany(StockMovement::class, 'meds_id', 'meds_id');
  }
}
