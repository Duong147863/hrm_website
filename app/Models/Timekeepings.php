<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timekeepings extends Model
{
    protected $table = "timekeepings";
    protected $primaryKey = "timekeeping_id";
    protected $keyType = "integer";
    protected $fillable = [
        "checkin",
        "checkout",
        "date",
        "late",
        "leaving_soon",
        "status",
        "shift_id",
        "profile_id"
    ];
    public $timestamps = false;
    protected $casts = [
        'profile_id' => "string",
        'late' => "datetime",
        'checkin' => "datetime",
        'checkout' => "datetime",
        'shift_id' => "string",
        'leaving_soon' => "datetime",
        'date' => "date",
        'status' => 'integer'
    ];

}
