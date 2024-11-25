<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hirings extends Model
{
    protected $table = "hirings";
    protected $primaryKey = "hiring_profile_id";
    protected $keyType = "integer";

    protected $fillable = [
        "hiring_profile_id",
        "profile_name",
        "birthday",
        "place_of_birth",
        "gender",
        "phone",
        "email",
        "nation",
        "apply_for",
        "educational_level",
        "current_address",
        "hiring_status",
        "hiring_profile_image",
        "work_experience"
    ];
    public $timestamps = false;
    protected $casts = [
        "hiring_profile_id" => 'integer',
        "educational_level" => "string",
        "profile_name" => "string",
        "phone" => "string",
        "email" => "string",
        "birthday" => "date",
        "gender" => "boolean",
        "apply_for" => "string",
        "current_address" => "string",
        "nation" => "string",
        "place_of_birth" => "string",
        "hiring_status" => "integer",
        "hiring_profile_image" => "string",
        "work_experience" => "string",
    ];
}
