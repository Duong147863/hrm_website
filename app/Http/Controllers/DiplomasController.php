<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diplomas;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DiplomasResource as DiplomasResource;
use App\Models\Enterprises;
use App\Http\Resources\EnterprisesResource as EnterprisesResource;
use App\Models\Profiles;
use App\Http\Controllers\ProfilesController;
use Carbon\Carbon;
class DiplomasController extends Controller
{
    public function index()
    {
        $diploma = Diplomas::all();
        return response()->json($diploma);
    }

    public function getDiplomaOfProfile(string $profile_id)
    {
        $diplomas = DB::table('diplomas')
        ->join('profiles', 'diplomas.profile_id', '=', 'profiles.profile_id')
        ->select(
            "profiles.profile_id",
            'diplomas.*'
        )->where('diplomas.profile_id', $profile_id)
        ->get();
        return response()->json($diplomas);
    }
    public function createNewDiploma(Request $request)
    {
        $fields = $request->validate([
            "diploma_id" => "required|string",
            "diploma_degree_name" => "required|string",
            "diploma_image" => "nullable|string",
            "ranking" => "required|string",
            "license_date" => "required|date",
            "diploma_type" => "required|string",
            "granted_by" => "required|string",
            "major" => "required|string",
            "mode_of_study" => "required|string",
            'profile_id' => "required|string",
        ]);
          // Kiểm tra nếu tên bằng cấp đã tồn tại cho cùng profile_id
          $nameDiploman = Diplomas::where('profile_id', $fields['profile_id'])
          ->where('diploma_degree_name', $fields['diploma_degree_name'])
          ->first();
          if ($nameDiploman) {
          return response()->json([
          "status" => false,
          "message" => "Tên bằng cấp không được trùng lặp trong danh sách bằng cấp của nhân viên đó."
          ], 422);
          }
            // Lấy thông tin nhân viên từ bảng Profiles
        $employee = Profiles::find($fields['profile_id']);
        if (!$employee) {
        return response()->json([
            "status" => false,
            "message" => "Không tìm thấy nhân viên với mã nhân viên này."
        ], 404);
        }
            // Kiểm tra tính tuổi của nhân viên tại thời điểm cấp bằng
        $employeeBirthday = new \Carbon\Carbon($employee->birthday);
        $licenseDate = new \Carbon\Carbon($fields['license_date']);
        $ageAtGraduation = $licenseDate->diffInYears($employeeBirthday); // Tính tuổi tại thời điểm cấp bằng
        
        if ($ageAtGraduation < 18) {
            return response()->json([
                "status" => false,
                "message" => "Nhân viên phải ít nhất 18 tuổi khi nhận bằng cấp."
            ], 422);
        }

        $newDiploma = Diplomas::create([
            'diploma_id' => ($fields['diploma_id']),
            'mode_of_study' => ($fields['mode_of_study']),
            'granted_by' => ($fields['granted_by']),
            'license_date' => ($fields['license_date']),
            'diploma_degree_name' => ($fields['diploma_degree_name']),
            'major' => ($fields['major']),
            'diploma_type' => ($fields['diploma_type']),
            'diploma_image' => ($fields['diploma_image']),
            'ranking' => ($fields['ranking']),
            'profile_id' => ($fields['profile_id']),
        ]);
        return response()->json(["Thêm thông tin bằng cấp thành công."], 201);
    }


    public function update(Request $request)
    {   
        $diplomas = Diplomas::find($request->diploma_id);
        $input = $request->validate( [
            "diploma_id" => "required|string",
            "diploma_degree_name" => "required|string",
            "diploma_image" => "required|string",
            "ranking" => "required|string",
            "license_date" => "required|date",
            "diploma_type" => "required|string",
            "granted_by" => "required|string",
            "major" => "required|string",
            "mode_of_study" => "required|string",
            'profile_id' => "required|string",
        ]);
        
         // Kiểm tra nếu tên bằng cấp đã tồn tại cho cùng profile_id
         $nameDiploman = Diplomas::where('profile_id', $input['profile_id'])
         ->where('diploma_degree_name', $input['diploma_degree_name'])
         ->where('diploma_id', '!=',$diplomas->diploma_id) // Loại trừ bản ghi hiện tại
         ->first();
 
     if ($nameDiploman) {
         return response()->json([
             "status" => false,
             "message" => "Tên bằng cấp không được trùng lặp trong danh sách bằng cấp của nhân viên đó."
         ], 422);
     }
          // Lấy thông tin nhân viên từ bảng Profiles
      $employee = Profiles::find($input['profile_id']);
      if (!$employee) {
      return response()->json([
          "status" => false,
          "message" => "Không tìm thấy nhân viên với mã nhân viên này."
      ], 404);
      }
          // Kiểm tra tính tuổi của nhân viên tại thời điểm cấp bằng
      $employeeBirthday = new \Carbon\Carbon($employee->birthday);
      $licenseDate = new \Carbon\Carbon($input['license_date']);
      $ageAtGraduation = $licenseDate->diffInYears($employeeBirthday); // Tính tuổi tại thời điểm cấp bằng
      
      if ($ageAtGraduation < 18) {
          return response()->json([
              "status" => false,
              "message" => "Nhân viên phải ít nhất 18 tuổi khi nhận bằng cấp."
          ], 422);
      }
        $diplomas->diploma_id = $input['diploma_id'];
        $diplomas->diploma_degree_name = $input['diploma_degree_name'];
        $diplomas->diploma_image = $input['diploma_image'];
        $diplomas->ranking = $input['ranking'];
        $diplomas->license_date = $input['license_date'];
        $diplomas->granted_by = $input['granted_by'];
        $diplomas->major = $input['major'];
        $diplomas->diploma_type = $input['diploma_type'];
        $diplomas->mode_of_study = $input['mode_of_study'];
        $diplomas->profile_id = $input['profile_id'];
        $diplomas->save();
        return response()->json([
            "status" => true,
            "message" => "Cập nhật thông tin bằng cấp thành công.",
            "data" => $diplomas
        ], 201); 
    }

    public function delete($id)
    {
        $diplomas = Diplomas::find($id);
    
        if (!$diplomas) {
            return response()->json([
                "status" => false,
                "message" => "diplomas not found",
                "data" => []
            ], 404);
        }
    
        $diplomas->delete();
        return response()->json([
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ], 200);
    }
}