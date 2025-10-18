<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicine extends Model {
    protected $fillable = ['medicine_name','stock','description', 'expiry_date',];
}
