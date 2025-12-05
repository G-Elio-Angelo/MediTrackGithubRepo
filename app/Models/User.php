<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use Notifiable;
    protected $primaryKey = 'user_id';
    protected $fillable = ['username','first_name','middle_name','last_name','age','address','email','phone_number','password','role'];
    protected $hidden = ['password','remember_token'];

    public function getFullNameAttribute()
    {
        return trim((($this->first_name ?? '') . ' ' . ($this->middle_name ?? '') . ' ' . ($this->last_name ?? '')));
    }
}
