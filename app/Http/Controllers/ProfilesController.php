<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profiles;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProfilesController extends Controller
{   
    public function index()
    {
        $profiles = Profiles::all();
        return response()->json($profiles);
    }

    public function show(string $profile_id)
    {
        return Profiles::findOrFail($profile_id);
    }
    public function departmentMembersCount(string $department_id) // Số lượng nhân viên đang làm việc tại phòng ban
    {
        // Lấy danh sách nhân viên có profile_status khác -1
        $profiles = Profiles::where('department_id', $department_id)
            ->where('profile_status', '!=', -1) // Loại bỏ profile_status == -1
            ->get();

        return response()->json([
            'profiles' => $profiles,
            'totals' => $profiles->count(), // Đếm tổng số nhân viên đủ điều kiện
        ]);
    }
    public function positionMembersCount(string $position_id) //SL nhân viên đang làm việc và nắm chức vụ hiện tại
    {
        $profiles = Profiles::where(['position_id' => $position_id, 'profile_status' => 1])->get();;
        return response()->json([
            'profiles' => $profiles,
            'totals' => $profiles->count(),
        ]);
    }
    public function quitMembersCount() //SL nhân viên đã nghỉ việc
    {
        $profiles = Profiles::where('profile_status', 0)->get();;
        return response()->json([
            'profiles' => $profiles,
            'totals' => $profiles->count(),
        ]);
    }
    public function MembersCount() // SL nhân viên đã nghỉ việc và đang làm việc
    {
        // Lấy tất cả nhân viên đã nghỉ việc (profile_status = -1)
        $quitProfiles = Profiles::where('profile_status', -1)
            ->get();

        // Lấy tất cả nhân viên đang làm việc (profile_status = 1)
        $activeProfiles = Profiles::where('profile_status', "!=", 1)->get();
        $officialContractsCount = DB::table('profiles')
            ->join('labor_contract', 'profiles.profile_id', '=', 'labor_contract.profile_id')
            ->whereNull('labor_contract.end_time')
            ->count();

        $temporaryContractsCount = DB::table('profiles')
            ->join('labor_contract', 'profiles.profile_id', '=', 'labor_contract.profile_id')
            ->whereNotNull('labor_contract.end_time')
            ->count();
        return response()->json([
            'quitCount' => $quitProfiles->count(), // Số lượng nhân viên đã nghỉ việc
            'activeCount' => $activeProfiles->count(), // Số lượng nhân viên đang làm việc
            'officialContractsCount' => $officialContractsCount, // Số lượng hợp đồng chính thức (end_time null)
            'temporaryContractsCount' => $temporaryContractsCount, // Số lượng hợp đồng có thời hạn (end_time không null)
        ]);
    }
    public function MembersCountGenderAndMaritalStatus() // SL nhân viên đã nghỉ việc và đang làm việc
    {
        //Lấy count nam
        $genderManEmloyment = Profiles::where('gender', 0)->get();
        //Lấy count Nữ
        $genderWomanEmloyment = Profiles::where('gender', 1)->get();

        $marriedEmloyment = Profiles::where('marriage', 1)->get();

        $unmarriedEmloyment = Profiles::where('marriage', 0)->get();

        return response()->json([
            'profiles' => $genderManEmloyment,
            'genderMan' => $genderManEmloyment->count(),

            'profiles' => $genderWomanEmloyment,
            'genderWoman' => $genderWomanEmloyment->count(),

            'profiles' => $marriedEmloyment,
            'married' => $marriedEmloyment->count(),

            'profiles' => $unmarriedEmloyment,
            'unmarried' => $unmarriedEmloyment->count(),
        ]);
    }
    public function getUserProfileInfo(string $profile_id)
    {
        return DB::table('profiles')
            ->join('departments', 'department_id', '=', 'departments.department_id')
            ->join('positions', 'position_id', '=', 'positions.position_id')
            ->join('salaries', 'salary_id', '=', 'salaries.salary_id')
            ->join('labor_contract', 'labor_contract_id', '=', 'labor_contract.labor_contract_id')
            ->select(
                "profiles.profile_id",
                "profiles.profile_name",
                "profiles.identify_num",
                "profiles.id_license_day",
                "profiles.gender",
                "profiles.profiles.phone",
                "profiles.email",
                "profiles.birthday",
                "profiles.marriage",
                "profiles.temporary_address",
                "profiles.current_address",
                "profiles.nation",
                "profiles.place_of_birth",
                'departments.*',
                'positions.*'
            )->where('profiles.profile_id', $profile_id)
            ->get();
    }

    public function emailLogin(Request $request)
    {
        $fields = $request->validate([
            "email" => "required|string",
            "password" => "required|string",
        ]);

        //Check email
        $user = Profiles::where("email", "=", $fields['email'])->where("profile_status", "!=", -1)->first();

        //Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Sai mật khẩu hoặc email/ số điện thoại'
            ], 401);
        } else {
            // Tạo API token cho người dùng
            $token = $user->createToken('API TOKEN')->plainTextToken;

            return response()->json(
                [
                    'user' => $user,
                    'token' => $token,
                    'role_permissions' =>
                    DB::table('role_permission')
                        ->join('roles', 'role_permission.role_id', '=', 'roles.role_id')
                        ->join('permissions', 'role_permission.permission_id', '=', 'permissions.permission_id')
                        ->select('permissions.permission_name')
                        ->where('role_permission.role_id', $user->role_id)->get(),
                ],
                200
            );
        }
    }

    public function phoneNumberLogin(Request $request)
    {
        $fields = $request->validate([
            "phone" => "required|string",
            "password" => "required|string",
        ]);

        //Check phone
        $user = Profiles::where("phone", "=", $fields['phone'])->where("profile_status", "!=", -1)
            ->first();
        //Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Sai mật khẩu hoặc email/ số điện thoại'
            ], 401);
        } else {
            // Tạo API token cho người dùng
            $token = $user->createToken('API TOKEN')->plainTextToken;

            return response()->json(
                [
                    'user' => $user,
                    'token' => $token,
                    'role_permissions' =>
                    DB::table('role_permission')
                        ->join('roles', 'role_permission.role_id', '=', 'roles.role_id')
                        ->join('permissions', 'role_permission.permission_id', '=', 'permissions.permission_id')
                        ->select('permissions.permission_name')
                        ->where('role_permission.role_id', $user->role_id)->get(),

                ],
                200
            );
        }
    }

    public function registerNewProfile(Request $request)
    {
        $fields = $request->validate([
            "profile_id" => "required|string",
            "profile_name" => "required|string",
            "profile_status" => "required|integer",
            "identify_num" => "required|string",
            "id_license_day" => "required|date",
            "gender" => "required|boolean",
            "phone" => "required|string",
            "email" => "nullable|string",
            "birthday" => "required|date",
            "password" => "required|string",
            "marriage" => "required|boolean",
            "temporary_address" => "required|string",
            "current_address" => "required|string",
            "nation" => "required|string",
            "place_of_birth" => "required|string",
            "role_id" => "required|integer",
            "profile_image" => "nullable|string",
            "start_time" => "nullable|date",
            "end_time" => "nullable|date",
            //foriegn key
            "department_id" => "nullable|string",
            "position_id" => "nullable|string",
            "salary_id" => "nullable|string",
            // "labor_contract_id" => "nullable|string",

        ]);
        // Kiểm tra nếu số điện thoại đã tồn tại
        $phoneExists = Profiles::where('phone', $fields['phone'])->exists();
        if ($phoneExists) {
            return response()->json([
                "status" => false,
                "message" => "Số điện thoại này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }    // Kiểm tra nếu số điện thoại đã tồn tại
        $emailExists = Profiles::where('email', $fields['email'])->exists();
        if ($emailExists) {
            return response()->json([
                "status" => false,
                "message" => "Email này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }
        $profileIdExists = Profiles::where('profile_id', $fields['profile_id'])->exists();
        if ($profileIdExists) {
            return response()->json([
                "status" => false,
                "message" => "Mã NV này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }
        $CCCDExists = Profiles::where('identify_num', $fields['identify_num'])->exists();
        if ($CCCDExists) {
            return response()->json([
                "status" => false,
                "message" => "CCCD này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }


        $newProfile = Profiles::create([
            'profile_id' => $fields['profile_id'],
            'profile_name' => $fields['profile_name'],
            'profile_image' => $fields['profile_image'],
            'birthday' => $fields['birthday'],
            'place_of_birth' => $fields['place_of_birth'],
            'phone' => $fields['phone'],
            'email' => $fields['email'],
            'gender' => $fields['gender'],
            'nation' => $fields['nation'],
            'marriage' => $fields['marriage'],
            'temporary_address' => $fields['temporary_address'],
            'current_address' => $fields['current_address'],
            'identify_num' => $fields['identify_num'],
            'id_license_day' => $fields['id_license_day'],
            'password' => bcrypt($fields['password']),
            'role_id' => $fields['role_id'],
            'profile_status' => $fields['profile_status'],
            'start_time' => $fields['start_time'],
            'end_time' => $fields['end_time'],
            //fk
            "department_id" => $fields["department_id"],
            "position_id" => $fields["position_id"],
            "salary_id" => $fields["salary_id"],
            // "labor_contract_id" => $fields["labor_contract_id"],
        ]);
        // Tạo API token cho người dùng
        $token = $newProfile->createToken('API TOKEN')->plainTextToken;
        return response()->json(
            [
                'user' => $newProfile,
                'token' => $token,
                "message" => "Thêm nhân viên thành công"
            ],
            201
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "Logged out"
        ]);
    }
    public function updateInfo(Request $request)
    {
        $profiles = Profiles::find($request->profile_id);
        // Kiểm tra xem hồ sơ có tồn tại không
        if (!$profiles) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
        $input = $request->validate([
            "profile_id" => "string",
            "profile_name" => "string",
            "profile_status" => "integer",
            "identify_num" => "string",
            "id_license_day" => "date",
            "gender" => "boolean",
            "phone" => "required|string",
            "email" => "nullable|string",
            "birthday" => "required|date",
            // "password" => "nullable|string",
            "marriage" => "required|boolean",
            "temporary_address" => "string",
            "current_address" => "string",
            "nation" => "required|string",
            "place_of_birth" => "string",
            "role_id" => "integer",
            "profile_image" => "nullable|string",
            "start_time" => "date",
            "end_time" => "date",
            // "days_off" => "integer",
            //foriegn key
            "department_id" => "nullable|string",
            "position_id" => "nullable|string",
            "salary_id" => "nullable|string",
            // "labor_contract_id" => "nullable|string",
        ]);
        // Kiểm tra số điện thoại trùng, ngoại trừ bản ghi hiện tại
        $phoneExists = Profiles::where('phone', $input['phone'])
            ->where('profile_id', '!=', $profiles->profile_id)
            ->exists();
        if ($phoneExists) {
            return response()->json([
                "status" => false,
                "message" => "Số điện thoại này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }

        // Kiểm tra email trùng, ngoại trừ bản ghi hiện tại
        $emailExists = Profiles::where('email', $input['email'])
            ->where('profile_id', '!=', $profiles->profile_id)
            ->exists();
        if ($emailExists) {
            return response()->json([
                "status" => false,
                "message" => "Email này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }

        // Kiểm tra mã NV trùng, ngoại trừ bản ghi hiện tại
        $profileIdExists = Profiles::where('profile_id', $input['profile_id'])
            ->where('profile_id', '!=', $profiles->profile_id)
            ->exists();
        if ($profileIdExists) {
            return response()->json([
                "status" => false,
                "message" => "Mã NV này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }

        // Kiểm tra CCCD trùng, ngoại trừ bản ghi hiện tại
        $CCCDExists = Profiles::where('identify_num', $input['identify_num'])
            ->where('profile_id', '!=', $profiles->profile_id)
            ->exists();
        if ($CCCDExists) {
            return response()->json([
                "status" => false,
                "message" => "CCCD này đã tồn tại trong danh sách nhân viên."
            ], 422);
        }
        $profiles->profile_id = $input['profile_id'];
        $profiles->profile_name = $input['profile_name'];
        $profiles->phone = $input['phone'];
        $profiles->email = $input['email'];
        $profiles->gender = $input['gender'];
        $profiles->birthday = $input['birthday'];
        $profiles->place_of_birth = $input['place_of_birth'];
        $profiles->nation = $input['nation'];
        $profiles->temporary_address = $input['temporary_address'];
        $profiles->current_address = $input['current_address'];
        $profiles->marriage = $input['marriage'];
        $profiles->profile_image = $input['profile_image'];
        $profiles->profile_status = $input['profile_status'];
        $profiles->identify_num = $input['identify_num'];
        $profiles->id_license_day = $input['id_license_day'];
        $profiles->start_time = $input['start_time'];
        $profiles->end_time = $input['end_time'];
        // $profiles->days_off = $input['days_off'];
        // $profiles->password = $input['password'];
        $profiles->role_id = $input['role_id'];
        if (isset($input['profile_image'])) {
            $profiles->profile_image = $input['profile_image'];
        }
        // Chỉ cập nhật mật khẩu nếu nó có giá trị
        if (isset($input['password'])) {
            $profiles->password = bcrypt($input['password']); // Mã hóa mật khẩu trước khi lưu
        }
        //fk
        $profiles->salary_id = $input['salary_id'];
        $profiles->department_id = $input['department_id'];
        $profiles->position_id = $input['position_id'];
        // $profiles->labor_contract_id = $input['labor_contract_id'];
        $profiles->save();
        return response()->json([], 200);
    }
    public function deactivateProfile(Request $request) //Khoá tk nếu nhân sự nghỉ việc
    {
        $profile = Profiles::find($request->profile_id);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $profile->profile_status = -1;
        $profile->save();

        return response()->json(['message' => 'Profile deactivated successfully'], 200);
    }
    public function changePassword(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'profile_id' => 'required|string',
            'current_password' => 'required|string',
            'new_password' => 'required|string|confirmed', // confirmed sẽ kiểm tra trường `new_password_confirmation`
        ]);

        // Lấy người dùng hiện tại theo `profile_id`
        $user = Profiles::find($request->profile_id);


        // Kiểm tra xem mật khẩu hiện tại có đúng không
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng.'], 422);
        }

        // Cập nhật mật khẩu mới
        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Đổi mật khẩu thành công.'], 200);
    }

    public function resetPassword(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'profile_id' => 'required|string',
            'new_password' => 'required|string|confirmed', // confirmed sẽ kiểm tra trường `new_password_confirmation`
        ]);

        // Lấy người dùng hiện tại theo `profile_id`
        $user = Profiles::find($request->profile_id);

        // Kiểm tra xem hồ sơ có tồn tại và có đang bị khoá không
        if (!$user) {
            return response()->json(['message' => 'Profile not found'], 404);
        } elseif ($user->profile_status != -1) {
            return response()->json(['message' => 'Profile is inactive'], 403);
        }
        // Cập nhật mật khẩu mới
        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Đổi mật khẩu thành công.'], 200);
    }

    //     public function forgotPassword(Request $request)
    //     {
    //         $request->validate([
    //             'email' => 'required|email|exists:profiles,email',
    //         ]);

    //         $email = $request->email;

    //         // Tạo token và lưu cache
    //         $token = Str::random(64);
    //         Cache::put("password_reset_$email", $token, 3600); // Lưu token 1 giờ

    //         // Gửi email với token
    //         $url = url("/reset-password?token=$token&email=$email");
    //         Mail::raw("Reset your password using the following link: $url", function ($message) use ($email) {
    //             $message->to($email)
    //                 ->subject('Reset Password');
    //         });

    //         return response()->json(['message' => 'Password reset link sent']);
    //     }

    //     public function resetPassword(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email|exists:profiles,email',
    //         'token' => 'required',
    //         'password' => 'required|confirmed',
    //     ]);

    //     $email = $request->email;
    //     $token = $request->token;

    //     // Lấy token từ cache
    //     $cachedToken = Cache::get("password_reset_$email");

    //     if (!$cachedToken || $cachedToken !== $token) {
    //         return response()->json(['message' => 'Invalid or expired token'], 401);
    //     }

    //     // Đặt lại mật khẩu
    //     $profile = Profiles::where('email', $email)->first();
    //     $profile->password = Hash::make($request->password);
    //     $profile->save();

    //     // Xóa token khỏi cache
    //     Cache::forget("password_reset_$email");

    //     return response()->json(['message' => 'Password reset successfully']);
    // }
}
