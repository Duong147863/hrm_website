<?php

use App\Http\Controllers\AbsentsController;
use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\DecisionsController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DiplomasController;
use App\Http\Controllers\EnterprisesController;
use App\Http\Controllers\LaborContractsController;
use App\Http\Controllers\PayrollDetailsController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\RelativesController;
use App\Http\Controllers\SalariesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\ShiftsController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\TimekeepingsController;
use App\Http\Controllers\WorkingProcessesController;
use App\Http\Controllers\TrainingProcessesController;
use App\Http\Controllers\InsurancesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\HiringsController;
use Illuminate\Support\Facades\Route;


Route::controller(ProfilesController::class)->group(function () {
    Route::post('/v1/auth/login/email', 'emailLogin');
    Route::post('/v1/auth/login/phone', 'phoneNumberLogin');
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(ProfilesController::class)->group(function () {
        Route::post('/v1/logout', 'logout');
        Route::get('/v1/profiles/MembersCountGenderAndMaritalStatus', 'MembersCountGenderAndMaritalStatus');
        Route::get('/v1/profiles/department/{id}', 'departmentMembersCount'); //Danh sách nhân viên thuộc phòng ban và số lượng
        Route::get('/v1/profiles/position/{id}', 'positionMembersCount'); //Danh sách nhân viên giữ chức vụ và số lượng
        Route::get('/v1/profiles/quit', 'quitMembersCount');
        Route::get('/v1/profiles/count', 'MembersCount'); //Danh sách nhân viên đã nghỉ việc và số lượng
        Route::get('/v1/profiles', 'index');
        Route::post('/v1/profile/auth/register', 'registerNewProfile');
        Route::put('v1/profile/update', 'updateInfo');
        Route::post('/v1/profile/changePassword', 'changePassword'); // API đổi mật khẩu
        Route::put('/v1/profile/delete', 'deactivateProfile');
    });
    Route::controller(PositionsController::class)->group(function () {
        Route::get('/v1/positions', 'index');
        Route::put('/v1/position/update', 'update');
        Route::post('/v1/position/create', 'createNewPosition');
        Route::delete('/v1/position/delete/{id}', 'delete');
    });
    //
    // Route::controller(TasksController::class)->group(function () {
    //     Route::get('/v1/project/task/{id}', 'showTaskDetail');
    //     Route::get('/v1/project/task', 'index');
    //     Route::put('/v1/project/task/update', 'update');
    //     Route::post('/v1/project/task/create', 'createNewTask');
    //     Route::delete('/v1/project/task/delete/{id}', 'delete');
    // });
    // //
    // Route::controller(AssignmentsController::class)->group(function () {
    //     Route::get('/v1/assign/task', 'index');
    //     Route::get('/v1/assign/task/detail/{id}', 'getAssigmentDetails');
    //     Route::put('/v1/assign/update', 'update');
    //     Route::post('/v1/assign/create', 'create');
    //     Route::delete('/v1/assign/delete/{id}', 'delete');
    // });
    //
    Route::controller(DepartmentsController::class)->group(function () {
        Route::get('/v1/departments/profiles/position/{id}', 'getDepartmentMembers');
        Route::get('/v1/departments/position', 'getDepartmentsByPosition');
        Route::get('/v1/departments', 'index');
        Route::put('/v1/department/update', 'update');
        Route::post('/v1/department/create', 'createNewDepartment');
        Route::delete('/v1/department/delete/{id}', 'delete');
    });
    Route::controller(ShiftsController::class)->group(function () {
        Route::get('/v1/shifts', 'index');
        Route::put('/v1/shift/update', 'update');
        Route::post('/v1/shift/create', 'store');
        Route::delete('/v1/shift/delete/{id}', 'delete');
    });
    //
    Route::controller(AbsentsController::class)->group(function () {
        Route::get('/v1/absents/{profile_id}', 'showAbsentOfProfile');
        Route::get('/v1/absents', 'index');
        Route::get('/v1/absents/attendanceStatistics', 'attendanceStatistics');
        Route::put('/v1/absent/update', 'update');
        Route::post('/v1/absent/create', 'createNewAbsentRequest');
        Route::delete('/v1/absent/delete/{id}', 'delete');
    });
    //
    Route::controller(TimekeepingsController::class)->group(function () {
        Route::get('/v1/checkin/all', 'index');
        Route::get('/v1/checkin/profile', 'getCheckinHistoryOf');
        Route::get('/v1/hours/weekly/profile/{id}', 'getWeeklyWorkingHoursOf');
        Route::get('/v1/late-employees', 'getLateList');
        Route::post('/v1/checkin', 'checkIn');
        Route::put('/v1/update/{id}', 'checkOut');
    });
    //
    Route::controller(RolesController::class)->group(function () {
        Route::get('/v1/roles', 'index');
    });
    //
    Route::controller(DiplomasController::class)->group(function () {
        Route::get('/v1/diploma/show/{id}', 'getDiplomaOfProfile');
        Route::put('/v1/diploma/update', 'update');
        Route::post('/v1/diploma/create', 'createNewDiploma');
        Route::delete('/v1/diploma/delete/{id}', 'delete');
    });
    //
    // Route::controller(InsurancesController::class)->group(function () {
    //     Route::get('/v1/insurances/show/{id}', 'getInsurancesOfProfile');
    //     Route::put('/v1/insurances/update', 'update');
    //     Route::post('/v1/insurances/create', 'store');
    //     Route::delete('/v1/insurances/delete/{id}', 'delete');
    // });
    //
    // Route::controller(DecisionsController::class)->group(function () {
    //     Route::get('/v1/decisions/abc', 'index');
    //     Route::put('/v1/decision/update', 'update');
    //     Route::post('/v1/decision/create', 'createNewdecision');
    //     Route::delete('/v1/decision/delete/{id}', 'delete');
    // });
    //
    Route::controller(EnterprisesController::class)->group(function () {
        Route::get('/v1/enterprise/info', 'index');
        Route::put('/v1/enterprise/info/update', 'update');
    });
    // Route::controller(ProjectsController::class)->group(function () {
    //     Route::get('/v1/projects', 'index');
    //     Route::post('/v1/project/create', 'createNewProject');
    //     Route::put('/v1/project/update', 'update');
    //     Route::delete('/v1/project/delete/{id}', 'delete');
    // });
    //
    Route::controller(RelativesController::class)->group(
        function () {
            Route::get('/v1/relative/{id}', '');
            Route::get('/v1/profile/relatives/{id}', 'showRelativesOf'); //Lấy ra thông tin các thân nhân của nhân viên có id là id được truyền vào
            Route::post('/v1/relatives/create', 'addNewRelatives');
            Route::put('/v1/relatives/update', 'update');
            Route::delete('/v1/relatives/delete/{relative_id}', 'delete')->where('relative_id', '[0-9]+');
        }
    );
    //
    Route::controller(WorkingProcessesController::class)->group(function () {
        Route::get('/v1/profile/workingprocesses/{id}', 'showWorkingProcessesOfProfileID');
        Route::put('/v1/profile/workingprocesses/update', 'update');
        Route::post('/v1/profile/workingprocesses/add', 'addNewWWorkingProcesses');
        Route::delete('/v1/profile/workingprocesses/delete/{id}', 'delete');
    });
    //
    Route::controller(TrainingProcessesController::class)->group(function () {
        Route::get('/v1/profile/trainingProcesses/{id}', 'showTrainingProcessesOfProfile');
        Route::put('/v1/profile/trainingProcesses/update', 'update');
        Route::post('/v1/profile/trainingProcesses/add', 'addNewTrainingProccess');
        Route::delete('/v1/profile/trainingProcesses/delete/{id}', 'delete');
    });
    //
    Route::controller(SalariesController::class)->group(function () {
        Route::get('/v1/salaries', 'index');
        Route::get('/v1/salary/{id}', 'getSalarySlip'); //Load phiếu lương của 1 nhân viên, có tham chiếu khoá ngoại nhiều bảng khác
        Route::put('/v1/salary/update', 'update');
        Route::post('/v1/salary/create', 'addNewSalary');
        Route::delete('/v1/salary/delete/{id}', 'delete');
    });
    Route::controller(LaborContractsController::class)->group(function () {
        Route::get('/v1/contracts', 'index');
        Route::get('/v1/contract/ContactsOfProfile/{id}', 'getLaborContactsOfProfile');
        Route::post('/v1/contract/create', 'createNewLaborContract');
        Route::put('v1/contract/update', 'update');
        Route::delete('/v1/contract/delete/{id}', 'delete');
    });
    Route::controller(HiringsController::class)->group(function () {
        Route::get('/v1/hirings', 'index');
        Route::post('/v1/hiring/create', 'createNewHiringProfile');
        Route::put('v1/hiring/update', 'update');
    });
});
