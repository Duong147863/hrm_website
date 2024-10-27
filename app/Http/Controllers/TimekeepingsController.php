<?php

namespace App\Http\Controllers;

use App\Http\Resources\TimekeepingsResource;
use Illuminate\Routing\Controller;
use App\Models\Timekeepings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimekeepingsController extends Controller
{
    // public function index()
    // {
    //     $timekeepings = Timekeepings::all();
    //     return response()->json($timekeepings);
    // }
    public function getTimeKeepingsList()
    {
        return
            DB::table('timekeepings')
            ->join('profiles', 'timekeepings.profile_id', '=', 'profiles.profile_id')
            ->select(
                'timekeepings.*',
                'profiles.profile_name'
            )
            ->get()
        ;
    }
    public function checkIn(Request $request)
    {
        $input = $request->validate([
            'timekeeping_id' => "integer",
            'profile_id' => "required",
            'late' => "nullable|time",
            'checkin' => "required|time",
            'checkout' => "nullable|time",
            'shift_id' => "string",
            'leaving_soon' => "nullable|time",
            'date' => "required|date",
            'status' => 'required|integer'
        ]);
        $timeKeepings = Timekeepings::create($input);
        $arr = [
            "status" => true,
            "message" => "Save successful",
            "data" => new TimekeepingsResource($timeKeepings)
        ];
        return response()->json($arr, 201);
    }


    public function showByProfileID(string $profile_id)
    {
        return Timekeepings::where('profile_id', $profile_id)->get();
    }

    public function checkOut(Request $request)
    {
        $checkOut = Timekeepings::find($request->timekeeping_id);
    }
}
