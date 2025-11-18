<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineReturn extends Model
{
    protected $table = 'medicine_returns';

    protected $fillable = [
        'medicine_id', 'batch_number', 'quantity', 'supplier_name', 'remarks', 'returned_at'
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'quantity' => 'integer',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
