<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicine extends Model {
    protected $fillable = ['medicine_name','batch_number','stock', 'expiry_date','supplier_name','intake_interval_minutes','delivered_date'];

    protected $casts = [
        'expiry_date' => 'date',
        'delivered_date' => 'date',
    ];

    public function returns()
    {
        return $this->hasMany(\App\Models\MedicineReturn::class, 'medicine_id');
    }
}
