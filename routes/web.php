<?php

use App\Http\Controllers\{
    CertificationController,
    ChipsReportController,
    DocumentController,
    PersonalInformationController,
    ProfileController,
    RankController,
    PermissionController,
    PositionController,
    SeaDutyRecordController,
    RoleController,
    UserController,
    UserPhotoController,
    VesselController
};
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->hasRole('officer')) {
        return redirect()->route('profile.edit');
    }

    return Inertia::render('Dashboard', [
        'users' => (int) User::count(),
        'roles' => (int) Role::count(),
        'permissions' => (int) Permission::count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth', 'verified')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/personal_info/store', [PersonalInformationController::class, 'store'])->name('personal_info.store');
    Route::match(['put', 'patch'], '/personal_info/update/{personalInfo}', [PersonalInformationController::class, 'update'])->name('personal_info.update');

    Route::post('/user_photo/store', [UserPhotoController::class, 'store'])->name('user_photo.store');
    Route::resource('/user', UserController::class)->except('create', 'show', 'edit');
    Route::post('/user/destroy-bulk', [UserController::class, 'destroyBulk'])->name('user.destroy-bulk');

    Route::resource('/role', RoleController::class)->except('create', 'show', 'edit');
    Route::resource('/rank', RankController::class)->except('create', 'show', 'edit');
    Route::resource('/position', PositionController::class)->except('create', 'show', 'edit');
    Route::resource('/vessel', VesselController::class)->except('create', 'show', 'edit');

    Route::resource('/certificates', CertificationController::class)->except('create', 'show', 'edit');
    Route::patch('/certificates/{certificate}/status', [CertificationController::class, 'updateStatus'])->name('certificates.updateStatus');

    Route::resource('/permission', PermissionController::class)->except('create', 'show', 'edit');

    Route::get('/chipsReports', [ChipsReportController::class, 'index'])->name('chipsReports.index');
    Route::post('/chipsReports', [ChipsReportController::class, 'store'])->name('chipsReports.store');
    Route::delete('/chipsReports/chipsReport}', [ChipsReportController::class, 'destroy'])->name('chipsReports.destroy');
    Route::patch('/chipsReports/{chipsReport}/status', [ChipsReportController::class, 'updateStatus'])->name('chipsReports.updateStatus');


    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::patch('/documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.updateStatus');

    Route::get('/seaDutyRecords', [SeaDutyRecordController::class, 'index'])->name('seaDutyRecords.index');
    Route::post('/seaDutyRecords', [SeaDutyRecordController::class, 'store'])->name('seaDutyRecords.store');
    Route::delete('/seaDutyRecords/{seaDutyRecord}', [SeaDutyRecordController::class, 'destroy'])->name('seaDutyRecords.destroy');
    Route::patch('/seaDutyRecords/{seaDutyRecord}/status', [SeaDutyRecordController::class, 'updateStatus'])->name('seaDutyRecords.updateStatus');

    Route::get('/personal_data', function () {
        return Inertia::render('Personnel/Index');
    });

});

// Route::get('/form', function () {
//     return Inertia::render('SakaiForm');
// });

// Route::get('/button', function () {
//     return Inertia::render('SakaiButton');
// });

// Route::get('/list', function () {
//     return Inertia::render('SakaiList');
// });

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';