<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerifyUser extends Model{

    // use SoftDeletes;
    protected $table = 'register_verify';
    protected $fillable = ['user_id', 'token'];
}