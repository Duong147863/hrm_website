<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diplomas;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DiplomasResource as DiplomasResource;
use App\Models\Enterprises;
use App\Http\Resources\EnterprisesResource as EnterprisesResource;

class DiplomasController extends Controller
{
    public function index()
    {
        $diploma = Diplomas::all();
        return response()->json($diploma);
    }

    public function createNewDiploma(Request $request)
    {
        $fields = $request->validate([
            "diploma_name" => "required|string",
            "enterprise_id" => 'required|integer',
        ]);
        $newDiploma = Diplomas::create([
            'diploma_name'=>($fields['diploma_name']),
            'enterprise_id'=>($fields['enterprise_id']),
        ]);
        return response()->json([], 201);
    }


    public function update(Request $request, Diplomas $diplomas)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            "diploma_name" => "",
            "diploma_id" => "",
        ]);
        if ($validator->fails()) {
            $arr = [
                "success" => false,
                "message" => "Data check error",
                "data" => $validator->errors(),
            ];
            return response()->json($arr, 200);
        }
        $diplomas->name = $input['diploma_name'];
        $diplomas->name = $input['diploma_id'];
        $diplomas->save();
        $arr = [
            "status" => true,
            "message" => "Save successful",
            "data" => new DiplomasResource($diplomas)
        ];
        return response()->json($arr, 200);
    }

    public function delete(Diplomas $diplomas)
    {
        $diplomas->delete();
        $arr = [
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ];
        return response()->json($arr, 200);
    }
}
