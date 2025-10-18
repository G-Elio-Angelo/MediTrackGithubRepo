<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'code', 'used', 'expires_at'];

    protected $dates = ['expires_at'];

    public function isExpired()
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }
}
