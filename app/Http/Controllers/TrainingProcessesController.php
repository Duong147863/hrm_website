<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrainingProcessesResource;
use Illuminate\Routing\Controller;
use App\Models\TrainingProcesses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Profiles;
use App\Http\Controllers\ProfilesController;
use Carbon\Carbon;
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
                        "message" => "Tên đào tạo và Nội dung đào tạo không được trùng lặp"
                    ], 422);
                }            
        $trainingProcesses = TrainingProcesses::create($input);
        $arr = [
            "status" => true,
            "message" => "Thêm đào tạo thành công",
        ];
        return response()->json($arr, 201);
    }

    public function update(Request $request)
    {
        $trainingprocesses = TrainingProcesses::find($request->trainingprocesses_id);
        $input = $request->validate([
            'profile_id' => "required|string",
            'trainingprocesses_id' => "required|integer",
            'trainingprocesses_name' => "required|string",
            'trainingprocesses_content' => "required|string",
            'start_time' => "required|date",
            'end_time' => "nullable|date",
        ]);
        // Kiểm tra workplace_name và workingprocess_content không trùng trong cùng profile_id
        $duplicate = TrainingProcesses::where('profile_id', $input['profile_id'])
        ->where('trainingprocesses_name', $input['trainingprocesses_name'])
        ->where('trainingprocesses_content', $input['trainingprocesses_content'])
        ->where('trainingprocesses_id', '!=', $trainingprocesses->trainingprocesses_id)
        ->first();

    if ($duplicate) {
        return response()->json([
            "status" => false,
            "message" => "Tên đào tạo và Nội dung đào tạo không được trùng lặp"
        ], 422);
    }            
        $trainingprocesses->trainingprocesses_id = $input['trainingprocesses_id'];
        $trainingprocesses->start_time = $input['start_time'];
        $trainingprocesses->end_time = $input['end_time'];
        $trainingprocesses->profile_id = $input['profile_id'];
        $trainingprocesses->trainingprocesses_name = $input['trainingprocesses_name'];
        $trainingprocesses->trainingprocesses_content = $input['trainingprocesses_content'];
        $trainingprocesses->save();
        $arr = [
            "status" => true,
            "message" => "Save successful",
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
