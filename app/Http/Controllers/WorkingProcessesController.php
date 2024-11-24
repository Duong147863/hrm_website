<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkingProcesses;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\WorkingProcessesResource as WorkingProcessesResource;

class WorkingProcessesController extends Controller
{
    public function showWorkingProcessesOfProfileID(string $profile_id)
    {
        return WorkingProcesses::where('profile_id', $profile_id)->get();
    }
    public function addNewWWorkingProcesses(Request $request)
    {
        $input = $request->validate([
            'profile_id' => "required|string",
            'workingprocess_content' => "nullable|string",
            'start_time' => "required|date",
            'end_time' => "nullable|date",
            'workingprocess_status' => "required|integer",
            'workplace_name' => "required|string",
        ]);
            // Kiểm tra workplace_name và workingprocess_content không trùng trong cùng profile_id
    $duplicate = WorkingProcesses::where('profile_id', $input['profile_id'])
    ->where('workplace_name', $input['workplace_name'])
    ->where('workingprocess_content', $input['workingprocess_content'])
    ->first();

if ($duplicate) {
    return response()->json([
        "status" => false,
        "message" => "workplace_name và workingprocess_content không được trùng lặp"
    ], 422);
}
        $workingProcesses = WorkingProcesses::create($input);
        $arr = [
            "status" => true,
            "message" => "Save successful",
            "data" => new WorkingProcessesResource($workingProcesses)
        ];
        return response()->json($arr, 201);
    }

    public function update(Request $request)
{
    // Tìm bản ghi dựa trên ID
    $workingProcesses = WorkingProcesses::find($request->workingprocess_id);
    // Kiểm tra xem relative có tồn tại không
    if (!$workingProcesses) {
        return response()->json([
            'message' => 'WorkingProcesses not found'
        ], 404);  // Trả về lỗi 404 nếu không tìm thấy relative
    }
    // Validate dữ liệu đầu vào
    $input = $request->validate([
        'workingprocess_id' => "string|required",
        'profile_id' => "string|required",
        'workingprocess_content' => "string|nullable",
        'start_time' => "date|required",
        'end_time' => "nullable|date",
        'workingprocess_status' => "required|integer",
        'workplace_name' => "string|required",
    ]);

    // Cập nhật các trường trong bản ghi
    $workingProcesses->workingprocess_id = $input['workingprocess_id'];
    $workingProcesses->workplace_name = $input['workplace_name'];
    $workingProcesses->workingprocess_status = $input['workingprocess_status'];
    $workingProcesses->workingprocess_content = $input['workingprocess_content'];
    $workingProcesses->start_time = $input['start_time'];
    $workingProcesses->end_time = $input['end_time'];
    $workingProcesses->profile_id = $input['profile_id'];

    // Lưu bản ghi
    $workingProcesses->save();

    return response()->json([
        "status" => true,
        "message" => "Lưu thành công"
    ], 200); // Trả về thành công
    }
    public function delete($id)
    {
        $workingProcesses = WorkingProcesses::find($id);
    
        if (!$workingProcesses) {
            return response()->json([
                "status" => false,
                "message" => "WorkingProcesses not found",
                "data" => []
            ], 404);
        }
    
        $workingProcesses->delete();
        return response()->json([
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ], 200);
    }
}

