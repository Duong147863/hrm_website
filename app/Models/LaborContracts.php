<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaborContracts extends Model
{
    protected $table = "labor_contract";
    protected $primaryKey = "labor_contract_id";
    protected $keyType = "string";

    protected $fillable = [
        "labor_contract_id",
        "profile_id",
        "image",
        "start_time",
        "end_time",
    ];
    public $timestamps = false;
    protected $casts = [
        "labor_contract_id" => "string",
        "profile_id" => "string",
        "end_time" => "datetime",
        "start_time" => "datetime",
        "image" => "string",
    ];
}
