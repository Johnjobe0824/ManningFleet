<?php

use App\Models\User;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Spatie\Permission\Models\Permission;

use App\Http\Controllers\{
    RoleController,
    UserController,
    ProfileController,
    PermissionController,
    RankController,
    PositionController,
    VesselController,
    PersonalInformationController,
    UserPhotoController,
    DocumentController
};

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
    return Inertia::render('Dashboard', [
        'users'         => (int) User::count(),
        'roles'         => (int) Role::count(),
        'permissions'   => (int) Permission::count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth', 'verified')->group(function ()    {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/personal_info/store',[PersonalInformationController::class,'store'])->name('personal_info.store');
    Route::match(['put', 'patch'], '/personal_info/update/{personalInfo}',[PersonalInformationController::class,'update'])->name('personal_info.update');

    Route::post('/user_photo/store',[UserPhotoController::class,'store'])->name('user_photo.store');
    Route::resource('/user', UserController::class)->except('create', 'show', 'edit');
    Route::post('/user/destroy-bulk', [UserController::class, 'destroyBulk'])->name('user.destroy-bulk');
    
    Route::resource('/role', RoleController::class)->except('create', 'show', 'edit');
    Route::resource('/rank', RankController::class)->except('create', 'show', 'edit');
    Route::resource('/position', PositionController::class)->except('create', 'show', 'edit');
    Route::resource('/vessel', VesselController::class)->except('create', 'show', 'edit');

    Route::resource('/permission', PermissionController::class)->except('create', 'show', 'edit');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::patch('/documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.updateStatus');


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
