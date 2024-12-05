<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Timekeepings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimekeepingsController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $checkin = Timekeepings::whereBetween('date', [$from, $to])
            ->get();

        return response()->json(
            $checkin
        );
    }
    
    // Thống kê số giờ làm việc theo tháng
    public function monthlyStatistics()
    {
        $monthlyData = DB::table('timekeepings')
            ->selectRaw('MONTH(checkin) AS month, YEAR(checkin) AS year, SUM(hours_worked) AS total_hours')
            ->groupByRaw('YEAR(checkin), MONTH(checkin)')
            ->orderByRaw('YEAR(checkin) DESC, MONTH(checkin) DESC')
            ->get();

        return response()->json($monthlyData);
    }
    public function getWeeklyWorkingHoursOf(Request $request, $profile_id)
    {

        // Lấy khoảng thời gian từ request
        $startDate = $request->query('from'); // Ngày bắt đầu
        $endDate = $request->query('to');     // Ngày kết thúc
        // Lấy dữ liệu chấm công của nhân viên
        $attendances = Timekeepings::where('profile_id', $profile_id)
            ->whereBetween('date', [$startDate, $endDate]) // Lọc theo khoảng thời gian
            ->whereNotNull('checkout')
            ->select('date', 'checkin', 'checkout')
            ->get();
        // Kiểm tra tham số hợp lệ
        if (!$startDate || !$endDate) {
            return response()->json([
                'message' => 'Vui lòng cung cấp start_date và end_date'
            ], 400);
        }
        // Tính toán số giờ làm việc mỗi ngày
        $workHours = $attendances->map(function ($attendance) {
            $checkin = Carbon::parse($attendance->checkin);
            $checkout = Carbon::parse($attendance->checkout);
            $date = Carbon::parse($attendance->date);
            // Tính số giờ làm việc
            $hoursWorked = $checkout->diffInHours($checkin);

            return [
                'day_of_week' => $date->dayName, // Lấy tên thứ trong tuần
                'date' => $attendance->date,
                'hours_worked' => $hoursWorked,
            ];
        });
        return response()->json($workHours);
    }

    public function getCheckinHistoryOf(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $profile_id = $request->query('profile_id');

        $checkin = Timekeepings::whereBetween('date', [$from, $to])
            ->where('profile_id',  $profile_id)
            // ->whereNotNull('checkout')
            ->get();
        return response()->json(
            $checkin
        );
    }

    public function getOTList(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $checkin = Timekeepings::whereBetween('date', [$from, $to])
            ->get()->filter(function ($timeSheet) {
                $dayOfWeek = date('N', strtotime($timeSheet->work_date)); // 6: Thứ Bảy, 7: Chủ Nhật
                return in_array($dayOfWeek, [6, 7]);
            });
        return response()->json(
            $checkin
        );
    }
    public function getLateList(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        // Lấy danh sách nhân viên đi trễ từ lúc ? đến lúc ?
        $lateEmployees = Timekeepings::whereBetween('date', [$from, $to])
            ->join('profiles', 'timekeepings.profile_id', '=', 'profiles.profile_id')
            ->where('profiles.gender', 1) // nếu là Nữ
            ->where('late',  '>', '01:15:00') // đi trễ trên 60p
            ->orWhere('profiles.gender', 0) // nếu là Nam
            ->where('late',  '>', '00:30:00') // đi trễ trên 30p
            ->get(['profiles.profile_id', 'profiles.profile_name', 'timekeepings.checkin', 'timekeepings.late']);
        return response()->json(
            $lateEmployees,
            200
            // [
            // "total" => $lateEmployees->count(),
            // "lateEmployees" => $lateEmployees
            // ]
        );
    }

    public function getWorkingHours(Request $request)
    {
        $startDate = $request->input('start_date'); // Ngày bắt đầu
        $endDate = $request->input('end_date'); // Ngày kết thúc

        $attendances = Timekeepings::where('profile_id', $request->profile_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $result = $attendances->map(function ($attendance) {
            $checkIn = strtotime($attendance->checkin);
            $checkOut = $attendance->checkout ? strtotime($attendance->checkout) : $checkIn;
            $hoursWorked = ($checkOut - $checkIn) / 3600;

            return [
                'date' => $attendance->date,
                'hours_worked' => $hoursWorked,
            ];
        });

        return response()->json($result);
    }

    public function checkIn(Request $request)
    {
        // Kiểm tra xem đã check-in chưa
        $existingAttendance = Timekeepings::where('profile_id', $request->profile_id)
            ->whereDate('date', now()->toDateString())
            ->where('shift_id', $request->shift_id)
            ->whereNull('checkout')
            ->first();

        if ($existingAttendance) { //Nếu rồi thì check out
            $input = $request->validate([
                // 'leaving_soon' => "nullable|date_format:H:i:s",
                'checkout' => "required|date_format:H:i:s",
            ]);
            $existingAttendance->checkout = $input['checkout'];
            // $existingAttendance->leaving_soon = $input['leaving_soon'];
            $existingAttendance->status = 1;
            $existingAttendance->save();
            return response()->json(["Check out success"], 200);
        } else {
            $input = $request->validate([
                'profile_id' => "required|string",
                'late' => "nullable|date_format:H:i:s",
                'checkin' => "required|date_format:H:i:s",
                'shift_id' => "required|string",
                'date' => "required|date",
            ]);

            Timekeepings::create([
                'date' => $input['date'],
                'status' => 0,
                'late' => $input['late'],
                'checkin' => $input['checkin'],
                'profile_id' => $input['profile_id'],
                'shift_id' => $input['shift_id'],
            ]);
            return response()->json(["Check in success"], 201);
        }
    }
    public function update(Request $request)
    {
        $timekeepings = Timekeepings::find($request->timekeeping_id);
          // Kiểm tra xem relative có tồn tại không
        if (!$timekeepings) {
            return response()->json([
                'message' => 'Trainingprocesses not found'
            ], 404);  // Trả về lỗi 404 nếu không tìm thấy relative
        }
        $input = $request->validate([
            'date' => "date",
            'leaving_soon' => "required|date_format:H:i:s",
            'shift_id' => "string",
            'checkout' => "datetime|required",
            'checkin' => "required|date_format:H:i:s",
            'late' => "nullable|date_format:H:i:s",
            'profile_id' => "required|string",
            'status' => 0,
            'timekeeping_id' => 'required|integer'
        ]);        
        $timekeepings->date = $input['date'];
        $timekeepings->leaving_soon = $input['leaving_soon'];
        $timekeepings->shift_id = $input['shift_id'];
        $timekeepings->checkout = $input['checkout'];
        $timekeepings->checkin = $input['checkin'];
        $timekeepings->late = $input['late'];
        $timekeepings->profile_id = $input['profile_id'];
        $timekeepings->status = $input['status'];
        $timekeepings->timekeeping_id = $input['timekeeping_id'];
        $timekeepings->save();
        $arr = [
            "status" => true,
            "message" => "Save successful",
        ];
        return response()->json($arr, 200);
    }
    public function delete($id)
    {
        $timekeepings = Timekeepings::find($id);

        if (!$timekeepings) {
            return response()->json([
                "status" => false,
                "message" => "timekeepings not found",
                "data" => []
            ], 404);
        }

        $timekeepings->delete();
        return response()->json([
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ], 200);
    }
}