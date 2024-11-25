<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recruitments extends Model
{
    use HasFactory;

    protected $table = "recruitments";
    protected $primaryKey = "recruitment_id";

    protected $keyType = "integer";

    protected $fillable = [
        "recruitment_id",
        "hiring_profile_id",
        "interviewer_id",
        "comments",
        "result",
        "interview_time",
    ];
    public $timestamps = false;
    protected $casts = [
        "recruitment_id" => "integer",
        "hiring_profile_id" => "integer",
        "interview_time"=> "datetime",
        "interviewer_id"=> "string",
        "result"=> "integer",
        "comments"=> "string",
    ];
}
