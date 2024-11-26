<?php

namespace App\Http\Controllers;

use App\Models\Absents;
use App\Http\Resources\AbsentsResource as AbsentsResource;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsentsController extends Controller
{
    public function index()
    {
        $absents = Absents::all();
        return response()->json($absents);
    }
    public function show(string $ID)
    {
        return (
            Absents::findOrFail($ID)
        );
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
        // Kiểm tra nếu trường date tồn tại
        $fields = $request->validate([
            // Xác thực ngày từ bắt buộc phải là ngày từ hiện tại đến tương lai nhưng bắt buộc năm phải là năm hiện tại, không cho phép ngày quá khứ
            'from' => 'required|date|after_or_equal:'. $currentYear . '-01-01|before_or_equal:' .'31-12-'. $currentYear,
            // Xác thực ngày đến, có thể là null, bắt buộc phải là ngày từ hiện tại đến tương lai nhưng bắt buộc phải là năm hiện tại, không cho phép ngày quá khứ
            'to' => 'nullable|date|after_or_equal:from|before_or_equal:'.'31-12-'. $currentYear,
            "reason" => "nullable|string",
            "profile_id" => "required|string",
            "days_off" => "nullable|numeric",
            "status" => "required|integer",
        ]);

        $from = Carbon::parse($request->from);
        $to = Carbon::parse($request->to);

        // Không cho phép nghỉ vào thứ 7 và CN
        // if ($start->isWeekend() || $end->isWeekend()) {
        //     return false;
        // }

        // Tổng số ngày nghỉ đã nghỉ trong năm
        $totalLeaveDays = DB::table('absents')
        ->where('profile_id', $request->profile_id) // Lọc theo user
        ->whereYear('from', $request->from) // Lọc theo năm
        ->sum('days_off'); //

        // Kiểm tra trùng lặp ngày nghỉ
        $existingLeave = Absents::where('profile_id', $request->profile_id)
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('from', [$from, $to])
                    ->orWhereBetween('to', [$from, $to])
                    ->orWhere(function ($q) use ($from, $to) {
                        $q->where('from', '<=', $from)
                            ->where('to', '>=', $to);
                    });
            })->exists();

        if ($existingLeave) {
            return response()->json(['Bạn đã xin nghỉ trong khoản tgian này rồi'], 401);
        }
        else if ($totalLeaveDays == 12) //Kiểm tra số ngày phép còn
        {
            return response()->json(['Bạn đã hết số ngày phép'], 400);
        }
        else {
            Absents::create([
                'from' => ($fields['from']),
                'to' => ($fields['to']),
                'reason' => $fields['reason'],
                'status' => $fields['status'],
                'profile_id' => $fields['profile_id'],
                'days_off' => $fields['days_off'],
            ]);
            return response()->json([], 201);
        }
    }

    public function update(Request $request)
    {
        $absents = Absents::find($request->ID);
        $input = $request->validate([
            "ID"=> "required|integer",
            "from" => "required|date",
            "to" => 'nullable|date',
            "reason" => "nullable|string",
            "profile_id" => "required|string",
            "days_off" => "nullable|numeric",
            "status" => "required|integer",
        ]);
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
