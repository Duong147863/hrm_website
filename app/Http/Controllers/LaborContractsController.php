<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\LaborContracts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaborContractsController extends Controller
{

    public function index()
    {
        $laborcontracts = Laborcontracts::all();
        return response()->json($laborcontracts);
    }

    public function getLaborContactsOfProfile(string $laborcontractsid)
    {
        return Laborcontracts::where('labor_contract_id', $laborcontractsid)->get();
    }

    public function showLaborContractDetails(string $profile_id)
    {
        return DB::table('labor_contract')
            ->join('profiles', 'profile_id', '=', 'profiles.profile_id')
            ->select(
                'labor_contract.*',
                "profiles.profile_name",
                "profiles.birthday",
                "profiles.identify_num",
                "profiles.id_license_day",
                "profiles.gender",
                "profiles.phone",
                "profiles.current_address",
            )
            ->where([
                ['profiles.profile_id' => $profile_id],
                ['labor_contract.profile_id' => 'profiles.profile_id']
            ])
            ->get();
    }
    public function createNewLaborContract(Request $request)
    {
        $existLaborContract = LaborContracts::where('profile_id',$request->profile_id)->get();

        $fields = $request->validate([
            "labor_contract_id" => "required|string",
            "end_time" => "nullable|date_format:d-m-Y",
            "start_time" => "required|date_format:d-m-Y",
            "image" => "required|string",
            "profile_id" => "required|string",
        ]);
        $newLaborContract = LaborContracts::create([
            'labor_contract_id' => ($fields['labor_contract_id']),
            'start_time' => ($fields['start_time']),
            'image' => ($fields['image']),
            'end_time' => ($fields['end_time']),
            'profile_id' => ($fields['profile_id']),
        ]);
        return response()->json([], 201);
    }
    public function update(Request $request)
    {
        $laborContracts = LaborContracts::find($request->labor_contract_id);
        $input = $request->validate([
            "labor_contract_id" => "required|string",
            "end_time" => "nullable|date_format:d-m-Y",
            "start_time" => "required|date_format:d-m-Y",
            "image" => "nullable|string",
            "profile_id" => "required|string",
        ]);
        $laborContracts->labor_contract_id = $input['labor_contract_id'];
        $laborContracts->start_time = $input['start_time'];
        $laborContracts->end_time = $input['end_time'];
        $laborContracts->profile_id = $input['profile_id'];
        $laborContracts->image = $input['image'];
        $laborContracts->save();
        return response()->json([], 200);
    }
    public function delete($id)
    {
        $laborContracts = LaborContracts::find($id);

        if (!$laborContracts) {
            return response()->json([
                "status" => false,
                "message" => "diplomas not found",
                "data" => []
            ], 404);
        }

        $laborContracts->delete();
        return response()->json([
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ], 200);
    }
}
