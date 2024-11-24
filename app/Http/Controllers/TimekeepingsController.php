<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Timekeepings;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimekeepingsController extends Controller
{
    public function show()
    {
        return DB::table('timekeepings')
            ->join('profiles', 'timekeepings.profile_id', '=', 'profiles.profile_id')
            ->join('shifts', '')
            ->select(
                'profiles.profile_id',
                'profiles.profile_name',
                'shifts.shift_name'
            )
            ->get();
    }
    public function showByProfileID(string $profile_id)
    {
        return Timekeepings::where('profile_id', $profile_id)->get();
    }
    public function getTimeKeepingsList()
    {
        return DB::table('timekeepings')
            ->join('profiles', 'timekeepings.profile_id', '=', 'profiles.profile_id')
            ->join('shifts', 'timekeepings.shift_id', '=', 'shifts.shift_id')
            ->select(
                'timekeepings.*',
                'profiles.profile_name',
                'shifts.shift_name',
            )->get();
    }
    public function getLateList(DateTime $start_date)
    {
        return DB::table('timekeepings')
            ->join('profiles', 'timekeepings.profile_id', '=', 'profiles.profile_id')
            ->join('shifts', 'timekeepings.shift_id', '=', 'shifts.shift_id')
            ->select(
                'timekeepings.*',
                'profiles.profile_name',
                'shifts.shift_name',
            )->where(['timekeepings.late','!=',null])
            ->get()
        ;
    }
    public function checkIn(Request $request)
    {
        // Kiểm tra xem đã check-in chưa
        $timeKeepings = Timekeepings::where('profile_id', $request->profile_id)
        ->whereDate('date', now()->toDateString())
        ->first();
        $input = $request->validate([
            'profile_id' => "required|string",
            'late' => "nullable|date_format:H:i:s",
            'checkin' => "required|date_format:H:i:s",
            'shift_id' => "required|string",
            'date' => "required|date",
            'status' => 'required|integer'
        ]);

       Timekeepings::create([
            'date'=> $input['date'],
            'status'=> $input['status'],
            'late'=> $input['late'],
            'checkin'=>$input['checkin'],
            'profile_id'=> $input['profile_id'],
            'shift_id'=> $input['shift_id'],
        ]);
        return response()->json([], 201);
    }
    public function checkOut(Request $request)
    {
        $checkOut = Timekeepings::find($request->timekeeping_id);
         // Kiểm tra xem đã check-in nhưng chưa check-out
         $checkOut = Timekeepings::where('profile_id', $request->profile_id)
         ->whereDate('date', now()->toDateString())
         ->where('status', 0)
         ->whereNull('checkout')
         ->first();

        $input = $request->validate([
            'checkout' => "required|time",
            'leaving_soon' => "nullable|time",
            'status' => 'required|integer'
        ]);
        $checkOut->timekeeping_id = $input['timekeeping_id'];
        $checkOut->profile_id = $input['profile_id'];
        $checkOut->shift_id = $input['shift_id'];
        $checkOut->checkout = $input['checkout'];
        $checkOut->shift_id = $input['shift_id'];
        $checkOut->date = $input['date'];
        $checkOut->status = $input['status'];
        $checkOut->save();
        return response()->json([], 200);
    }
}
