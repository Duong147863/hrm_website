<?php

namespace App\Http\Controllers;

use App\Models\Absents;
use App\Http\Resources\AbsentsResource as AbsentsResource;
use App\Models\Timekeepings;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Profiles;
use App\Http\Controllers\ProfilesController;

class AbsentsController extends Controller
{
    public function index()
    {
        $absents = Absents::all();
        return response()->json($absents);
    }
    public function show($profileId)
    {
        // Lấy thông tin người dùng đã đăng nhập (dựa trên $profileId)
        $userProfile = DB::table('profiles')->where('profile_id', $profileId)->first();

        // Kiểm tra nếu role_id của người đăng nhập là 4
        if ($userProfile && $userProfile->role_id == 4) {
            // Lấy tất cả đơn nghỉ của những nhân viên có role_id = 1 (không bao gồm đơn nghỉ của profileId)
            $absents = DB::table('absents')
                ->join('profiles', 'absents.profile_id', '=', 'profiles.profile_id')
                ->where('profiles.role_id',"<",4) // Lọc nhân viên có role_id = <4
                ->where('absents.profile_id', '<>', $profileId) // Loại trừ đơn nghỉ của chính người đăng nhập
                ->select('absents.*') // Chỉ lấy các cột từ bảng absents
                ->get();

            // Trả về kết quả dưới dạng JSON
            return response()->json($absents);
        }

        // Kiểm tra nếu role_id của người đăng nhập là 5
        if ($userProfile && $userProfile->role_id == 5) {
            // Lấy tất cả đơn nghỉ của mọi nhân viên, trừ đơn nghỉ của chính người đăng nhập
            $absents = DB::table('absents')
                ->where('absents.profile_id', '<>', $profileId) // Loại trừ đơn nghỉ của chính người đăng nhập
                ->get();

            // Trả về kết quả dưới dạng JSON
            return response()->json($absents);
        }
        if ($userProfile && $userProfile->role_id == 3) {
            // Lấy department_id của người dùng hiện tại
            $userDepartmentId = $userProfile->department_id;
        
            // Lấy tất cả đơn nghỉ của nhân viên thuộc cùng phòng ban, ngoại trừ người dùng hiện tại
            $absents = DB::table('absents')
                ->join('profiles', 'absents.profile_id', '=', 'profiles.profile_id')
                ->join('departments', 'departments.department_id', '=', 'profiles.department_id')
                ->where('profiles.department_id', $userDepartmentId) // Chỉ lấy nhân viên cùng phòng ban
                ->where('profiles.profile_id', '<>', $profileId) // Loại trừ đơn nghỉ của người dùng hiện tại
                ->select('absents.*', 'profiles.profile_id', 'profiles.profile_name', 'departments.department_name') // Chọn các cột cần thiết
                ->get();
        
            // Trả về kết quả dưới dạng JSON
            return response()->json($absents);
        }


        // Nếu người dùng không có role_id là 4 hoặc 5, trả về thông báo lỗi
        return response()->json(['message' => 'Access denied or invalid role'], 403);
    }

    public function attendanceStatistics(Request $request)
    {
        $today = now()->format('Y-m-d'); // Lấy ngày hiện tại
        // Lấy danh sách nghỉ phép hợp lệ (trạng thái = 1, ngày hiện tại nằm trong khoảng nghỉ phép)
        $approvedLeaves = DB::table('absents')
            ->join('profiles', 'absents.profile_id', '=', 'profiles.profile_id')
            ->select(
                'profiles.profile_id',
                'profiles.profile_name',
                'absents.from',
                'absents.to',
                'absents.reason',
                'absents.status',
                'absents.days_off'
            )
            ->where('absents.status', '=', 1) // Được duyệt
            ->where(function ($query) use ($today) {
                $query->where('absents.from', '<=', $today)
                    ->where('absents.to', '>=', $today)
                    ->orWhere('absents.from', '=', $today)
                    ->orWhere('absents.to', '=', $today);
            })
            ->get();

        // Lấy danh sách nghỉ không phép (trạng thái khác 1, nhưng ngày hiện tại nằm trong khoảng nghỉ phép)
        $unapprovedLeaves = DB::table('absents')
            ->join('profiles', 'absents.profile_id', '=', 'profiles.profile_id')
            ->select(
                'profiles.profile_id',
                'profiles.profile_name',
                'absents.from',
                'absents.to',
                'absents.reason',
                'absents.status',
                'absents.days_off'
            )
            ->where('absents.status', '!=', 1) // Chưa duyệt
            ->where(function ($query) use ($today) {
                $query->where('absents.from', '<=', $today)
                    ->where('absents.to', '>=', $today)
                    ->orWhere('absents.from', '=', $today)
                    ->orWhere('absents.to', '=', $today);
            })
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Attendance statistics fetched successfully',
            'data' => [
                'approved_leaves' => $approvedLeaves,
                'unapproved_leaves' => $unapprovedLeaves,
            ]
        ], 200);
    }

    public function showAbsentOfProfile(string $profile_id)
    {
        return DB::table('absents')
            ->join('profiles', 'absents.profile_id', '=', 'profiles.profile_id')
            ->select(
                'absents.*'
            )
            ->where('absents.profile_id', '=', $profile_id)
            ->get();
    }


    public function createNewAbsentRequest(Request $request)
    {
        //Lấy năm hiện tại
        $currentYear = Carbon::now()->year;
        // Kiểm tra dữ liệu đưa vào
        $fields = $request->validate([
            // Xác thực ngày từ bắt buộc phải là ngày từ hiện tại đến tương lai nhưng bắt buộc năm phải là năm hiện tại, không cho phép ngày quá khứ
            'from' => 'required|date|after:' . $currentYear . '-01-01|before_or_equal:' . '31-12-' . $currentYear,
            // Xác thực ngày đến, có thể là null, bắt buộc phải là ngày từ hiện tại đến tương lai nhưng bắt buộc phải là năm hiện tại, không cho phép ngày quá khứ
            'to' => 'nullable|date|after_or_equal:from|before_or_equal:' . '31-12-' . $currentYear,
            "reason" => "nullable|string",
            "profile_id" => "required|string",
            "days_off" => "nullable|integer",
            "status" => "required|integer",
        ]);

        $from = Carbon::parse($request->from);
        $to = $request->to != null ? Carbon::parse($request->to) : null; // Chỉ xử lý `to` nếu nó tồn tại
        //Duong
        $requestedDaysOff = $fields['days_off'] ?? $from->diffInDays($to) + 1; // Tính số ngày nghỉ dựa trên khoảng thời gian
        // Duong

        // // Tổng số ngày nghỉ ĐÃ NGHỈ trong năm
        // $totalLeaveDays = DB::table('absents')
        //     ->where('profile_id', $request->profile_id) // Lọc theo user
        //     ->whereYear('from', $request->from) // Lọc theo năm
        //     ->sum('days_off');

    //Duong
    // Tổng số ngày nghỉ ĐÃ NGHỈ trong năm
    $totalLeaveDays = DB::table('absents')
    ->where('profile_id', $fields['profile_id'])
    ->whereYear('from', $currentYear)
    ->sum('days_off');
    //Duong
    // Lấy Tổng số ngày ĐƯỢC NGHỈ PHÉP của nhân viên trong 1 năm
        $daysOffAvailablePerYear = DB::table('profiles')
            ->where('profile_id', $request->profile_id)
            ->value('days_off'); // Dùng value lấy giá trị trực tiếp của cột
         // Kiểm tra xem tổng số ngày nghỉ sau khi thêm yêu cầu mới có vượt quá giới hạn không
    //Duong
    if ($totalLeaveDays + $requestedDaysOff > $daysOffAvailablePerYear) {
        return response()->json(['message' => 'Số ngày nghỉ phép vượt quá giới hạn. Bạn chỉ còn ' . ($daysOffAvailablePerYear - $totalLeaveDays) . ' ngày nghỉ phép'], 400);
    }
    //Duong
      // Kiểm tra xem đã có yêu cầu nghỉ phép nào trong tháng này chưa
      $existingLeaveInMonth = Absents::where('profile_id', $fields['profile_id'])
      ->whereMonth('from', $from->month) // Kiểm tra tháng của ngày nghỉ
      ->whereYear('from', $from->year) // Kiểm tra năm của ngày nghỉ
      ->exists(); // Kiểm tra nếu có đơn nghỉ phép nào trong tháng
  //Duong
  if ($existingLeaveInMonth) {
      return response()->json(['message' => 'Bạn đã tạo đơn nghỉ phép trong tháng này rồi'], 400);
  }
  //Duong
        //Kiểm tra trùng đã nghỉ
        $existingLeave = Absents::where('profile_id', $request->profile_id)
            ->where(function ($query) use ($from, $to) {
                // Nếu `to` không null, kiểm tra khoảng thời gian
                if ($to) {
                    $query->where(function ($q) use ($from, $to) {
                        $q->where('from', '<=', $to)
                            ->where('to', '>=', $from); // Giao nhau giữa khoảng thời gian
                    });
                } else {
                    // Nếu chỉ có `from`, kiểm tra ngày `from` trùng 1 ngày
                    $query->where(function ($q) use ($from) {
                        $q->where(function ($subQuery) use ($from) {
                            // Kiểm tra bản ghi 1 ngày duy nhất trong CSDL
                            $subQuery->whereColumn('from', 'to')
                                ->where('from', '=', $from);
                        })->orWhere(function ($subQuery) use ($from) {
                            // Kiểm tra ngày `from` nằm trong khoảng thời gian
                            $subQuery->where('from', '<=', $from)
                                ->where('to', '>=', $from);
                        });
                    });
                }
            })->exists();


        if ($existingLeave) {
            return response()->json(['message' => 'Bạn đã xin nghỉ trong khoảng thời gian này rồi'], 401);
        }
        // Kiểm tra nếu số ngày nghỉ phép yêu cầu vượt quá giới hạn
        $requestedDaysOff = $fields['days_off'];
        if ($totalLeaveDays + $requestedDaysOff > $daysOffAvailablePerYear) // nếu số ngày ĐÃ NGHỈ == số ngày ĐƯỢC NGHỈ PHÉP trong 1 năm
        {
            return response()->json(['message' => 'Số ngày nghỉ phép của bạn không đủ'], 400);
        } else {
            Absents::create([
                'from' => ($fields['from']),
                'to' => ($fields['to']),
                'reason' => $fields['reason'],
                'status' => $fields['status'],
                'profile_id' => $fields['profile_id'],
                'days_off' => $fields['days_off'],
            ]);
            return response()->json([""], 201);
        }
    }

    public function update(Request $request)
    {
        $absents = Absents::find($request->ID);

        //Lấy năm hiện tại
        $currentYear = Carbon::now()->year;
        // Kiểm tra dữ liệu đưa vào
        $input = $request->validate([
            // Xác thực ngày từ bắt buộc phải là ngày từ hiện tại đến tương lai nhưng bắt buộc năm phải là năm hiện tại, không cho phép ngày quá khứ
            'from' => 'required|date|after:' . $currentYear . '-01-01|before_or_equal:' . '31-12-' . $currentYear,
            // Xác thực ngày đến, có thể là null, bắt buộc phải là ngày từ hiện tại đến tương lai nhưng bắt buộc phải là năm hiện tại, không cho phép ngày quá khứ
            'to' => 'nullable|date|after_or_equal:from|before_or_equal:' . '31-12-' . $currentYear,
            "reason" => "nullable|string",
            "profile_id" => "required|string",
            "days_off" => "nullable|integer",
            "status" => "required|integer",
            "ID" => "required|integer", 
        ]);

        $from = Carbon::parse($request->from);
        $to = $request->to != null ? Carbon::parse($request->to) : null; // Chỉ xử lý `to` nếu nó tồn tại

        // Tổng số ngày nghỉ ĐÃ NGHỈ trong năm
        $totalLeaveDays = DB::table('absents')
            ->where('profile_id', $request->profile_id) // Lọc theo user
            ->whereYear('from', $request->from) // Lọc theo năm
            ->sum('days_off');

        // Lấy Tổng số ngày ĐƯỢC NGHỈ PHÉP của nhân viên trong 1 năm
        $daysOffAvailablePerYear = DB::table('profiles')
            ->where('profile_id', $request->profile_id)
            ->value('days_off'); // Dùng value lấy giá trị trực tiếp của cột

        //Kiểm tra trùng đã nghỉ
        $existingLeave = Absents::where('profile_id', $request->profile_id)
            ->where(function ($query) use ($from, $to) {
                // Nếu `to` không null, kiểm tra khoảng thời gian
                if ($to) {
                    $query->where(function ($q) use ($from, $to) {
                        $q->where('from', '<=', $to)
                            ->where('to', '>=', $from); // Giao nhau giữa khoảng thời gian
                    });
                } else {
                    // Nếu chỉ có `from`, kiểm tra ngày `from` trùng 1 ngày
                    $query->where(function ($q) use ($from) {
                        $q->where(function ($subQuery) use ($from) {
                            // Kiểm tra bản ghi 1 ngày duy nhất trong CSDL
                            $subQuery->whereColumn('from', 'to')
                                ->where('from', '=', $from);
                        })->orWhere(function ($subQuery) use ($from) {
                            // Kiểm tra ngày `from` nằm trong khoảng thời gian
                            $subQuery->where('from', '<=', $from)
                                ->where('to', '>=', $from);
                        });
                    });
                }
            })->exists();

        // Kiểm tra nếu số ngày nghỉ phép yêu cầu vượt quá giới hạn
        $requestedDaysOff = $input['days_off'];
        if ($totalLeaveDays + $requestedDaysOff > $daysOffAvailablePerYear) // nếu số ngày ĐÃ NGHỈ == số ngày ĐƯỢC NGHỈ PHÉP trong 1 năm
        {
            return response()->json(['message' => 'Số ngày nghỉ phép của bạn không đủ'], 400);
        } else {
            $absents->ID = $input['ID'];
            $absents->from = $input['from'];
            $absents->to = $input['to'];
            $absents->reason = $input['reason'];
            $absents->profile_id = $input['profile_id'];
            $absents->days_off = $input['days_off'];
            $absents->status = $input['status'];
            $absents->save();
            return response()->json([], 200);
        }
    }
    public function delete(int $ID)
    {
        $absents = Absents::find($ID);
        $absents->delete();
        $arr = [
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ];
        return response()->json($arr, 200);
    }
}
