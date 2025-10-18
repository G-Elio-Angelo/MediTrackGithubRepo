<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use Notifiable;
    protected $primaryKey = 'user_id';
    protected $fillable = ['username','email','phone_number','password','role'];
    protected $hidden = ['password','remember_token'];
}
