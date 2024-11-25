<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingProcesses extends Model
{
    use HasFactory;
    protected $table = "workingprocesses";
    protected $primaryKey = "workingprocess_id";
    protected $keyType = "integer";

    protected $fillable = [
        'workingprocess_id',
        'workplace_name',
        "workingprocess_content",
        "profile_id",
        "start_time",
        "end_time"
    ];
    public $timestamps = false;
    protected $casts = [
        'workingprocess_id' => "integer",
        'workplace_name' => "string",
        'workingprocess_content' => "string",
        'profile_id' => "string",
        'start_time' => "date",
        'end_time' => "date",
    ];
}
