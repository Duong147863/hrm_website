<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Positions;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PositionsResource as PositionsResource;
use App\Models\Enterprises;
use App\Http\Resources\EnterprisesResource as EnterprisesResource;

class PositionsController extends Controller
{
    public function index()
    {
        $position = Positions::all();
        return response()->json($position);
    }
    public function show(string $id)
    {
        return (
            Positions::findOrFail($id)
        );
    }
    public function createNewPosition(Request $request)
    {
        $fields = $request->validate([
            "position_id" => "required|string",
            "position_name" => "required|string",
        ]);
        $newPosition = Positions::create([
            'position_id' => ($fields['position_id']),
            'position_name' => ($fields['position_name']),
        ]);
        $arr = [
            "status" => true,
            "message" => "Delete success",
            "data" => new PositionsResource($newPosition)
        ];
        response()->json($arr, 201);
    }

    public function update(Request $request)
    {
        $position = Positions::find($request->position_id);
        $input = $request->validate([
            "position_id" => "required|string",
            "position_name" => "required|string",
        ]);
        $position->position_id = $input['position_id'];
        $position->position_name = $input['position_name'];
        $position->save();
        return response()->json(["message" => "Update data success"], 200);
    }

    // public function delete(Positions $positions)
    // {
    //     $positions->delete();
    //     $arr = [
    //         "status" => true,
    //         "message" => "Delete success",
    //         "data" => []
    //     ];
    //     return response()->json($arr, 200);
    // }
    public function delete($id)
{
    $position = Positions::find($id);

    if (!$position) {
        return response()->json([
            "status" => false,
            "message" => "Position not found",
            "data" => []
        ], 404);
    }

    $position->delete();
    return response()->json([
        "status" => true,
        "message" => "Delete success",
        "data" => []
    ], 200);
}

}