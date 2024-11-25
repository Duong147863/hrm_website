<?php

namespace App\Http\Controllers;

use App\Models\Recruitments;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class RecruitmentsController extends Controller
{
    public function index()
    {
        $position = Recruitments::all();
        return response()->json($position);
    }
    public function show(string $id)
    {
        return (
            Recruitments::findOrFail($id)
        );
    }
    public function newRecruitments(Request $request)
    {
        $fields = $request->validate([

            "hiring_profile_id" => "integer",
            "interview_time" => "datetime",
            "interviewer_id" => "string",
            "result" => "integer",
            "comments" => "string",
        ]);
        $newPosition = Recruitments::create([
            'position_id' => ($fields['position_id']),
            'result' => ($fields['result']),
        ]);
        response()->json([], 201);
    }

    public function update(Request $request)
    {
        $position = Recruitments::find($request->position_id);
        $input = $request->validate([
            "recruitment_id" => "integer",
            "hiring_profile_id" => "integer",
            "interview_time" => "datetime",
            "interviewer_id" => "string",
            "result" => "integer",
            "comments" => "string",
        ]);
        $position->recruitment_id = $input['recruitment_id'];
        $position->hiring_profile_id = $input['hiring_profile_id'];
        $position->interviewer_id = $input['interviewer_id'];
        $position->interview_time = $input['interview_time'];
        $position->result = $input['result'];
        $position->comments = $input['comments'];
        $position->save();
        return response()->json(["message" => "Update data success"], 200);
    }

    public function delete($id)
    {
        $recruitment = Recruitments::find($id);

        if (!$recruitment) {
            return response()->json([
                "status" => false,
                "message" => "recruitments not found",
                "data" => []
            ], 404);
        }

        $recruitment->delete();
        return response()->json([
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ], 200);
    }
}
