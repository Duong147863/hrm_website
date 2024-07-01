<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    use HasFactory;
    protected $table = "departments";
    protected $primaryKey = "department_id";
    protected $keyType = "string";
    protected $fillable = ["",""] ;
    public $timestamps = false;
    protected $casts = [""] ;
}
