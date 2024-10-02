<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Accounts extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = "accounts";
    protected $primaryKey = "account_id";

    protected $fillable = [
        "username",
        "permission",// mặc định 0 là superadmin, 1 là admin, 2 là nhân viên
        'password',
        "account_status"
    ];
    public $hidden = ['password', 'remember_token'];
    public $timestamps = false;
    protected $casts = ["permission"=>'integer'];
}
