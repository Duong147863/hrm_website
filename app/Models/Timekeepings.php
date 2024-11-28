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
    // Accessor - Thay thế NULL bằng 'Không có dữ liệu' khi lấy giá trị
    // public function getColumnNameAttribute()
    // {
    //     return $this->column_name ?? 'Không có dữ liệu';
    // }
     // Phương thức tính số giờ làm việc trong ngày
   // Phương thức tính số giờ làm việc trong ngày
//    public function getWorkHoursAttribute()
//    {
//        if ($this->checkin && $this->checkout) {
//            // Sử dụng Carbon để tính số giờ làm việc giữa check_in và check_out
//            $checkIn = Carbon::parse($this->checkin);
//            $checkOut = Carbon::parse($this->checkout);

//            // Tính tổng số giờ giữa check_in và check_out
//            return $checkIn->diffInHours($checkOut);
//        }

//        return 0; // Nếu không có giờ vào hoặc giờ ra thì trả về 0
//    }
}
