<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Relatives;
use Illuminate\Routing\Controller;
use App\Models\Profiles;
use App\Http\Controllers\ProfilesController;
class RelativesController extends Controller
{
    public function show(string $id)
    {
        return (
            Relatives::findOrFail($id)
        );
    }
    public function showRelativesOf(string $profile_id)
    {
        return Relatives::where('profile_id', $profile_id)->get();
    }
    public function addNewRelatives(Request $request)
    {
        $fields = $request->validate([
            "relative_name" => "required|string",
            "relative_phone" => "required|string",
            "relative_birthday" => "required|date_format:d-m-Y",
            "relationship" => "required|string",
            "relative_job" => "required|string",
            "relative_nation" => "required|string",
            "relative_temp_address" => "required|string",
            "relative_current_address" => "required|string",
            "profile_id" => 'required|string',
        ]);
         // Kiểm tra nếu tên thân nhân đã tồn tại cho cùng profile_id
        $nameRelative = Relatives::where('profile_id', $fields['profile_id'])
        ->where('relative_name', $fields['relative_name'])
        ->first();
        if ($nameRelative) {
        return response()->json([
        "status" => false,
        "message" => "Tên thân nhân không được trùng lặp trong danh sách thân nhân của nhân viên đó."
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
        // Kiểm tra nếu tên thân nhân trùng với tên nhân viên
         if ($fields['relative_name'] === $employee->profile_name) {
        return response()->json([
            "status" => false,
            "message" => "Tên thân nhân không được trùng với tên nhân viên."
        ], 422);
        }
        $newRelative = Relatives::create(attributes: [
            'profile_id' => $fields['profile_id'],
            'relative_name' => $fields['relative_name'],
            'relative_phone' => $fields['relative_phone'],
            'relative_birthday' => $fields['relative_birthday'],
            'relative_nation' => $fields['relative_nation'],
            'relative_job' => $fields['relative_job'],
            "relative_current_address" => $fields["relative_current_address"],
            "relative_temp_address" => $fields["relative_temp_address"],
            "relationship" => $fields["relationship"],
        ]);
       
        return response()->json([
            "status" => true,
            "message" => "Thân nhân đã được thêm thành công.",
            "data" => $newRelative
        ], 201);
    }

    public function update(Request $request)
    {
        $relatives = Relatives::find($request->relative_id);
        // Kiểm tra xem relative có tồn tại không
        if (!$relatives) {
            return response()->json([
                'message' => 'Relative not found'
            ], 404);  // Trả về lỗi 404 nếu không tìm thấy relative
        }
        $input = $request->validate([
            "relative_id" => "required|integer",
            "relative_name" => "required|string",
            "relative_phone" => "required|string",
            "relative_birthday" => "required|date",
            "relationship" => "required|string",
            "relative_job" => "required|string",
            "relative_nation" => "required|string",
            "relative_temp_address" => "required|string",
            "relative_current_address" => "required|string",
            "profile_id" => 'required|string',
        ]);
            // Kiểm tra nếu tên thân nhân đã tồn tại cho cùng profile_id (loại trừ bản ghi hiện tại)
            $nameRelative = Relatives::where('profile_id', $input['profile_id'])
            ->where('relative_name', $input['relative_name'])
            ->where('relative_id', '!=', $relatives->relative_id)
            ->first();
            if ($nameRelative) {
                return response()->json([
                    "status" => false,
                    "message" => "Tên thân nhân không được trùng lặp trong danh sách thân nhân của nhân viên này."
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
          // Kiểm tra nếu tên thân nhân trùng với tên nhân viên
           if ($input['relative_name'] === $employee->profile_name) {
          return response()->json([
              "status" => false,
              "message" => "Tên thân nhân không được trùng với tên nhân viên."
          ], 422);
          }
        $relatives->relative_id = $input['relative_id'];
        $relatives->relative_name = $input['relative_name'];
        $relatives->relative_phone = $input["relative_phone"];
        $relatives->relative_birthday = $input["relative_birthday"];
        $relatives->relative_job = $input["relative_job"];
        $relatives->relationship = $input["relationship"];
        $relatives->relative_nation = $input["relative_nation"];
        $relatives->relative_temp_address = $input["relative_temp_address"];
        $relatives->relative_current_address = $input["relative_current_address"];
        $relatives->profile_id = $input["profile_id"];
        $relatives->save();
        
        return response()->json([
            "status" => true,
            "message" => "Thân nhân đã được cập nhật thành công.",
            "data" => $relatives
        ], 200);
    }

    public function delete(int $relative_id)
    {
        $relatives = Relatives::where('relative_id', $relative_id);

        if ($relatives->doesntExist()) {
            return response()->json([
                "status" => false,
                "message" => "No relatives found for this profile",
                "data" => []
            ], 404);
        }

        $relatives->delete();

        return response()->json([
            "status" => true,
            "message" => "Relatives deleted successfully",
            "data" => []
        ], 200);
    }


}
