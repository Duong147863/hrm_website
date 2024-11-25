<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departments;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DepartmentsController extends Controller
{
    public function index()
    {
        $department = Departments::all();
        return response()->json($department);
    }
    public function show(string $profile_id)
    {
        return Departments::findOrFail($profile_id);
    }

    public function getDepartmentMembers(string $department_id)
    {
        return
            DB::table('departments')
            ->join('profiles', 'departments.department_id', '=', 'profiles.department_id')
            ->join('positions', 'profiles.position_id', '=', 'positions.position_id')
            ->select(
                'profiles.profile_name',
                'positions.position_name'
            )
            ->where([['departments.department_id', '=', $department_id]],)
            ->get()
        ;
    }
    public function getDepartmentsByPosition()
    {
        return DB::table('departments')
            ->join('positions', 'positions.department_id', '=', 'departments.department_id')
            ->select(
                'departments.department_id',
                'departments.department_name',
                'positions.position_id',
                'positions.position_name'
            )
            ->get();
    }
    public function createNewDepartment(Request $request)
    {
        $fields = $request->validate([
            "department_id" => "required|string|unique:departments,department_id",
            "department_name" => "required|string|unique:departments,department_name",
        ]);
        $newDepartment = Departments::create([
            'department_id' => ($fields['department_id']),
            'department_name' => ($fields['department_name']),
        ]);
        return response()->json([], 201);
    }

    public function update(Request $request)
    {
        $department = Departments::find($request->department_id);
        $input = $request->validate([
            "department_id" => "required|string",
            "department_name" => "required|string",
        ]);
        $department->department_id = $input['department_id'];
        $department->department_name = $input['department_name'];
        $department->save();
        return response()->json([
            "status" => true,
            "message" => "Lưu thành công"
        ], 200); 
    }

    public function delete($id)
    {
        $departments = Departments::find($id);

        if (!$departments) {
            return response()->json([
                "status" => false,
                "message" => "Department not found",
                "data" => []
            ], 404);
        }

        $departments->delete();
        return response()->json([
            "status" => true,
            "message" => "Delete success",
            "data" => []
        ], 200);
    }
}