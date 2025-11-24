<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineIntake extends Model
{
    protected $fillable = ['user_id','medicine_id','intake_time','status','confirmed_at','quantity','sms_notified','sms_notified_at'];

    protected $casts = [
        'intake_time' => 'datetime',
        'status' => 'boolean',
        'confirmed_at' => 'datetime',
        'quantity' => 'integer',
        'sms_notified' => 'boolean',
        'sms_notified_at' => 'datetime',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
