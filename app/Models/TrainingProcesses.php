<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingProcesses extends Model
{
    protected $table = "trainingprocesses";
    protected $primaryKey = "trainingprocesses_id";
    protected $keyType = "integer";
    protected $fillable = [
        'trainingprocesses_id',
        "trainingprocesses_name",
        "trainingprocesses_content",
        "start_time",
        "end_time",
        'profile_id'
    ];
    public $timestamps = false;
    protected $casts = [
        'trainingprocesses_id' => "integer",
        'trainingprocesses_name' => "string",
        'trainingprocesses_content' => "string",
        'profile_id' => "string",
        'start_time' => "date",
        'end_time' => "date",
    ];
}
