<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssignmentsResource;
use App\Models\Assignments;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class AssignmentsController extends Controller
{
    public function index()
    {
        $assignments = Assignments::all();
        return response()->json($assignments);
    }

    public function show(string $id)
    {
        return (Assignments::findOrFail($id));
    }
    public function create(Request $request)
    {
        $fields = $request->validate([
            "assignment_id" => "required|string",
            "profile_id" => "required|string",
            "task_id" => 'nullable|string',
            "project_id" => "required|string",
        ]);
        $newDecision = Assignments::create([
            'assignment_id' => $fields['assignment_id'],
            'profile_id' => $fields['profile_id'],
            'task_id' => $fields['task_id'],
            'project_id' => $fields['project_id'],
        ]);
        return response()->json([], 201);
    }

    public function update(Request $request, Assignments $assignments)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            "assignment_id" => "required|string",
            "profile_id" => "required|string",
            "task_id" => 'nullable|string',
            "project_id" => "required|string",
        ]);
        if ($validator->fails()) {
            $arr = [
                "success" => false,
                "message" => "Data check error",
                "data" => $validator->errors(),
            ];
            return response()->json($arr, 200);
        }
        $assignments->assignment_id = $input['assignment_id'];
        $assignments->profile_id = $input['profile_id'];
        $assignments->task_id = $input['task_id'];
        $assignments->project_id = $input['project_id'];
        $assignments->save();
        $arr = [
            "status" => true,
            "message" => "Save successful",
            "data" => new AssignmentsResource($assignments)
        ];
        return response()->json($arr, 200);
    }
}
