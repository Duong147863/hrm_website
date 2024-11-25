<?php

namespace App\Models;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class Profiles extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = "profiles";
    protected $primaryKey = "profile_id";
    protected $keyType = "string";
    protected $fillable = [
        "profile_id",
        "profile_name",
        "birthday",
        "place_of_birth",
        "identify_num",
        "id_license_day",
        "gender",
        "phone",
        "email",
        "password",
        "nation",
        "marriage",
        "role_id",
        "temporary_address",
        "current_address",
        "profile_status",
        "profile_image",
        "start_time",
        "end_time",
        "days_off",     //ngày được nghỉ phép
        //foriegn key
        "department_id",
        "position_id",
        "salary_id",
        // "labor_contract_id"
    ];
    protected $hidden = [
        // 'password',
        'remember_token',
    ];

    public $timestamps = false;
    protected $casts = [
        "profile_id" => "string",
        "profile_name" => "string",
        "profile_status" => "integer",
        "identify_num" => "string",
        "id_license_day" => "date",
        "gender" => "boolean",
        "phone" => "string",
        "email" => "string",
        "birthday" => "date",
        "password" => "string",
        "marriage" => "boolean",
        "temporary_address" => "string",
        "current_address" => "string",
        "nation" => "string",
        "role_id" => "integer",
        "place_of_birth" => "string",
        "start_time" => "datetime",
        "end_time" => "datetime",
        "days_off" => "integer",
        //foriegn key
        "department_id" => "string",
        "position_id" => "string",
        "salary_id" => "string",
        // "labor_contract_id" => "string",
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'profiles');
    }
    public function position()
    {
        return $this->belongsToMany(Positions::class, 'profiles');
    }
    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permission)) {
                return true;
            }
        }
        return false;
    }
}
