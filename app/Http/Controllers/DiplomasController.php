<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diplomas;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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

    public function getDiplomaOfProfile(string $profile_id)
    {
        $diplomas = Diplomas::where('profile_id', $profile_id)
            ->with('profile') // Load quan hệ với profile
            ->get();
    
        $result = $diplomas->map(function ($diploma) {
            return [
                'profile_name' => $diploma->profile->profile_name, // Lấy tên profile
            ];
        });
    
        return response()->json($result);
    }
    public function createNewDiploma(Request $request)
    {
        $fields = $request->validate([
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
        return response()->json([], 201);
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
            "message" => "Lưu thành công"
        ], 200); 
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