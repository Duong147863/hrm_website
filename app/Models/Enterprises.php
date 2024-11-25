<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprises extends Model
{
    use HasFactory;
    protected $table = "enterprises";
    protected $primaryKey = "enterprise_id";
    protected $keyType = "integer";

    protected $fillable = [
        "enterprise_id",
        "name",
        "email",
        "license_num",
        "phone",
        "assign_date",
        "address",
        "website"
    ];
    public $timestamps = false;
    protected $casts = [
        "license_num" => "string",
        "name" => "string",
        "phone" => "string",
        "email" => "string",
        "assign_date" => "date",
        "address"=> "string",
        "website"=> "string",
    ];
}
