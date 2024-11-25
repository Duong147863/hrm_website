<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrainingProcessesResource;
use Illuminate\Routing\Controller;
use App\Models\TrainingProcesses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrainingProcessesController extends Controller
{
    public function showTrainingProcessesOfProfile(string $profile_id)
    {
        return TrainingProcesses::where('profile_id', $profile_id)->get();
    }
    public function addNewTrainingProccess(Request $request)
    {
        $input = $request->validate([
            'profile_id' => "required|string",
            'trainingprocesses_name' => "required|string",
            'trainingprocesses_content' => "required|string",
            'start_time' => "required|date",
            'end_time' => "nullable|date",
        ]);
                   // Kiểm tra workplace_name và workingprocess_content không trùng trong cùng profile_id
    $duplicate = TrainingProcesses::where('profile_id', $input['profile_id'])
    ->where('trainingprocesses_name', $input['trainingprocesses_name'])
    ->where('trainingprocesses_content', $input['trainingprocesses_content'])
    ->first();

if ($duplicate) {
    return response()->json([
        "status" => false,
        "message" => "trainingprocesses_name và trainingprocesses_content không được trùng lặp"
    ], 422);
}
        $trainingProcesses = TrainingProcesses::create($input);
        $arr = [
            "status" => true,
            "message" => "Save successful",
            "data" => new TrainingProcessesResource($trainingProcesses)
        ];
        return response()->json($arr, 201);
    }

    public function update(Request $request)
    {
        $trainingprocesses = TrainingProcesses::find($request->trainingprocesses_id);
          // Kiểm tra xem relative có tồn tại không
    if (!$trainingprocesses) {
        return response()->json([
            'message' => 'Trainingprocesses not found'
        ], 404);  // Trả về lỗi 404 nếu không tìm thấy relative
    }
        $input = $request->validate([
            'profile_id' => "required|string",
            'trainingprocesses_name' => "required|string",
            'trainingprocesses_content' => "required|string",
            'start_time' => "required|date",
            'end_time' => "nullable|date",
        ]);
        $trainingprocesses->start_time = $input['start_time'];
        $trainingprocesses->end_time = $input['end_time'];
        $trainingprocesses->profile_id = $input['profile_id'];
        $trainingprocesses->trainingprocesses_name = $input['trainingprocesses_name'];
        $trainingprocesses->trainingprocesses_content = $input['trainingprocesses_content'];
        $trainingprocesses->save();
        $arr = [
            "status" => true,
            "message" => "Save successful",
            "data" => new TrainingProcessesResource($trainingprocesses)
        ];
        return response()->json($arr, 200);
    }
    public function delete($id)
    {
        $trainingprocesses = TrainingProcesses::find($id);

        if (!$trainingprocesses) {
            return response()->json([
                "status" => false,
                "message" => "TrainingProcesses not found",
                "data" => []
            ], 404);
        }

        $trainingprocesses->delete();
        return response()->json([
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ], 200);
    }
}
