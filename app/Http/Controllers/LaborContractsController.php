<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\LaborContracts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profiles;
use App\Http\Controllers\ProfilesController;
class LaborContractsController extends Controller
{

    public function index()
    {
        $laborcontracts = Laborcontracts::all();
        return response()->json($laborcontracts);
    }

    public function getLaborContactsOfProfile(string $profile_id)
    {
        return Laborcontracts::where('profile_id', $profile_id)->get();
    }
    public function getLaborContacts(string $laborContractId)
    {
        return Laborcontracts::where('labor_contract_id', $laborContractId)->get();
    }
    // public function showLaborContractDetails(string $profile_id)
    // {
    //     return DB::table('labor_contract')
    //         ->join('profiles', 'profile_id', '=', 'profiles.profile_id')
    //         ->select(
    //             'labor_contract.*',
    //             "profiles.profile_name",
    //             "profiles.birthday",
    //             "profiles.identify_num",
    //             "profiles.id_license_day",
    //             "profiles.gender",
    //             "profiles.phone",
    //             "profiles.current_address",
    //         )
    //         ->where([
    //             ['profiles.profile_id' => $profile_id],
    //             ['labor_contract.profile_id' => 'profiles.profile_id']
    //         ])
    //         ->get();
    // }
    
    public function createNewLaborContract(Request $request)
    {
        $existLaborContract = LaborContracts::where('profile_id',$request->profile_id)->get();

        $fields = $request->validate([
            "labor_contract_id" => "required|string",
            "end_time" => "nullable|date",
            "start_time" => "required|date",
            "image" => "required|string",
            "profile_id" => "required|string",
        ]);
        $laborcontractIdExists = Laborcontracts::where('labor_contract_id', $fields['labor_contract_id'])->exists();
        if ($laborcontractIdExists) {
            return response()->json([
                "status" => false,
                "message" => "Mã HD này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }
        $newLaborContract = LaborContracts::create([
            'labor_contract_id' => ($fields['labor_contract_id']),
            'start_time' => ($fields['start_time']),
            'image' => ($fields['image']),
            'end_time' => ($fields['end_time']),
            'profile_id' => ($fields['profile_id']),
        ]);
            // Lấy profile để kiểm tra trạng thái hiện tại
        $profile = Profiles::find($fields['profile_id']);
        if ($profile) {
            // Kiểm tra và cập nhật profileStatus dựa vào trạng thái hiện tại
            switch ($profile->profile_status) {
                case 0: // Đang thử việc
                    $profile->profile_status = 1; // Ký hợp đồng lần 1
                    break;
                case 1: // Ký hợp đồng lần 1
                    $profile->profile_status = 2; // Ký hợp đồng lần 2
                    break;
                case 2: // Ký hợp đồng lần 2
                    $profile->profile_status = 3; // Ký hợp đồng lần 3
                    break;
                default:
                    // Không làm gì nếu đã vượt quá trạng thái ký hợp đồng lần 3
                    break;
            }
            // Lưu lại trạng thái cập nhật
            $profile->save();
        }
        return response()->json([
                "status" => true,
                "message" => "Hợp đồng mới đã được tạo và trạng thái profile đã được cập nhật."
        ], 201);
    }
    public function update(Request $request)
    {
        $laborContracts = LaborContracts::find($request->labor_contract_id);
        $input = $request->validate([
            "labor_contract_id" => "required|string",
            "end_time" => "nullable|date",
            "start_time" => "required|date",
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
}
