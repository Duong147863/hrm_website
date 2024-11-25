<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermissions extends Model
{
    use HasFactory;
    protected $table = "role_permission";

    protected $fillable = [
        "role_id",
        "permission_id",
    ];
    public $timestamps = false;
    protected $casts = [
        "role_id" => "integer",
        "permission_id" => "integer",
    ];
}
