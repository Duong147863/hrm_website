<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salaries;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\SalariesResource as SalariesResource;

class SalariesController extends Controller
{
    public function index(){

    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            "enterprise_id"=> "required",
            ""=> "",
        ]);
        if ($validator->fails()) {
            $arr = [
                "success"=> false,
                "message"=> "Data check error",
                "data"=> $validator->errors(),
            ];
            return response()->json($arr,200);
        }
        $salaries = Salaries::create($input);
        $arr = [
            "status"=> true,
            "message"=> "Save successful",
            "data"=> new SalariesResource($salaries)
        ];
        return response()->json( $arr,201);
    }

    public function edit($id){

    }

    public function update(Request $request, Salaries $salaries){
        $input = $request->all();
        $validator = Validator::make($input, [
            ""=> "",
        ]);
        if ($validator->fails()) {
            $arr = [
                "success"=> false,
                "message"=> "Data check error",
                "data"=> $validator->errors(),
            ];
            return response()->json($arr,200);
        }
        $salaries->name = $input['name'];
        $salaries->save();
        $arr = [
            "status"=> true,
            "message"=> "Save successful",
            "data"=> new SalariesResource($salaries)
        ];
        return response()->json( $arr,200);
    }

    public function delete(Salaries $salaries){
        $salaries -> delete();
        $arr = [
            "status"=> true,
            "message"=> "Delete success",
            "data"=> []
        ];
        return response()->json( $arr,200);
    }
}
