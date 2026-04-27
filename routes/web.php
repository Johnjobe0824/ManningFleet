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
    VesselController,
    CraftSizeController,
    BilletController,
    JuniorVesselAssignmentController,
    JuniorHigherBilletController,
    CompletionBilletController,
    SlmcEndorsementController,
    ExoDeliberationController,  
    ExoReassignController,
    Cat2ExoPromotionController,

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

    Route::get('/documents/fetch', [DocumentController::class, 'fetch'])->name('documents.fetch');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::patch('/documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.updateStatus');

    Route::get('/seaDutyRecords', [SeaDutyRecordController::class, 'index'])->name('seaDutyRecords.index');
    Route::post('/seaDutyRecords', [SeaDutyRecordController::class, 'store'])->name('seaDutyRecords.store');
    Route::delete('/seaDutyRecords/{seaDutyRecord}', [SeaDutyRecordController::class, 'destroy'])->name('seaDutyRecords.destroy');
    Route::patch('/seaDutyRecords/{seaDutyRecord}/status', [SeaDutyRecordController::class, 'updateStatus'])->name('seaDutyRecords.updateStatus');

    Route::resource('/craft_size', CraftSizeController::class)->except('create', 'show', 'edit'); 
    

    Route::get('/juniorVesselAssignments', [JuniorVesselAssignmentController::class, 'index'])->name('juniorVesselAssignments.index');
    Route::post('/juniorVesselAssignments', [JuniorVesselAssignmentController::class, 'store'])->name('juniorVesselAssignments.store');
    Route::get('/juniorVesselAssignments/fetch', [JuniorVesselAssignmentController::class, 'fetch'])->name('juniorVesselAssignments.fetch');
    Route::get('/juniorVesselAssignments/admin', [JuniorVesselAssignmentController::class, 'adminIndex'])->name('juniorVesselAssignments.adminIndex');
    Route::get('/juniorVesselAssignments/admin/fetch', [JuniorVesselAssignmentController::class, 'adminFetch'])->name('juniorVesselAssignments.adminFetch');
    Route::patch('/juniorVesselAssignments/{juniorVesselAssignment}/status', [JuniorVesselAssignmentController::class, 'updateStatus'])->name('juniorVesselAssignments.updateStatus');
    Route::delete('/juniorVesselAssignments/{juniorVesselAssignment}', [JuniorVesselAssignmentController::class, 'destroy'])->name('juniorVesselAssignments.destroy');

    Route::get('/juniorHigherBillets/admin', [JuniorHigherBilletController::class, 'adminIndex'])->name('juniorHigherBillets.adminIndex');
    Route::get('/juniorHigherBillets/fetch', [JuniorHigherBilletController::class, 'fetch'])->name('juniorHigherBillets.fetch');
    Route::get('/juniorHigherBillets', [JuniorHigherBilletController::class, 'index'])->name('juniorHigherBillets.index');
    Route::post('/juniorHigherBillets', [JuniorHigherBilletController::class, 'store'])->name('juniorHigherBillets.store');
    Route::patch('/juniorHigherBillets/{juniorHigherBillet}/status', [JuniorHigherBilletController::class, 'updateStatus'])->name('juniorHigherBillets.updateStatus');
    Route::delete('/juniorHigherBillets/{juniorHigherBillet}', [JuniorHigherBilletController::class, 'destroy'])->name('juniorHigherBillets.destroy');

    // ✅ Correct order — specific routes before wildcard
    // ✅ Specific static routes FIRST
    Route::get('/completionBillets/fetch',[CompletionBilletController::class, 'fetch'])->name('completionBillets.fetch');
    Route::get('/completionBillets/admin',[CompletionBilletController::class, 'adminIndex'])->name('completionBillets.adminIndex');
    // ✅ Base routes
    Route::get('/completionBillets',[CompletionBilletController::class, 'index'])->name('completionBillets.index');
    Route::post('/completionBillets',[CompletionBilletController::class, 'store'])->name('completionBillets.store');
    // ✅ Wildcard routes LAST
    Route::get('/completionBillets/{completionBillet}',[CompletionBilletController::class, 'show'])->name('completionBillets.show');
    Route::patch('/completionBillets/{completionBillet}/updateStatus',[CompletionBilletController::class, 'updateStatus'])->name('completionBillets.updateStatus');
    Route::patch('/completionBillets/{completionBillet}/update',[CompletionBilletController::class, 'update'])->name('completionBillets.update');
    Route::delete('/completionBillets/{completionBillet}',[CompletionBilletController::class, 'destroy'])->name('completionBillets.destroy');


    // ✅ Specific static routes FIRST
    Route::get('/slmcEndorsements/fetch',[SlmcEndorsementController::class, 'fetch'])->name('slmcEndorsements.fetch');
    Route::get('/slmcEndorsements/admin',[SlmcEndorsementController::class, 'adminIndex'])->name('slmcEndorsements.adminIndex');
    // ✅ Base routes
    Route::get('/slmcEndorsements',[SlmcEndorsementController::class, 'index'])->name('slmcEndorsements.index');
    Route::post('/slmcEndorsements',[SlmcEndorsementController::class, 'store'])->name('slmcEndorsements.store');
    // ✅ Wildcard routes LAST
    Route::get('/slmcEndorsements/{slmcEndorsement}',[SlmcEndorsementController::class, 'show'])->name('slmcEndorsements.show');
    Route::patch('/slmcEndorsements/{slmcEndorsement}/updateStatus',[SlmcEndorsementController::class, 'updateStatus'])->name('slmcEndorsements.updateStatus');
    Route::delete('/slmcEndorsements/{slmcEndorsement}',[SlmcEndorsementController::class, 'destroy'])->name('slmcEndorsements.destroy');
    

    Route::get('/exoDeliberations',[ExoDeliberationController::class, 'index'])->name('exoDeliberations.index');
    Route::get('/exoDeliberations/fetch',[ExoDeliberationController::class, 'fetch'])->name('exoDeliberations.fetch');
    Route::post('/exoDeliberations',[ExoDeliberationController::class, 'store'])->name('exoDeliberations.store');
    Route::delete('/exoDeliberations/{exoDeliberation}',[ExoDeliberationController::class, 'destroy'])->name('exoDeliberations.destroy');
    Route::get('/exoDeliberations/admin', [ExoDeliberationController::class, 'adminIndex'])->name('exoDeliberations.adminIndex');
    Route::patch('exoDeliberations/{exoDeliberation}/status', [ExoDeliberationController::class, 'updateStatus'])->name('exoDeliberations.updateStatus');
    

    Route::get('/exoReassignments/fetch', [ExoReassignController::class, 'fetch'])->name('exoReassignments.fetch');
    Route::get('/exoReassignments/admin', [ExoReassignController::class, 'adminIndex'])->name('exoReassignments.adminIndex');
    Route::get('/exoReassignments',  [ExoReassignController::class, 'index'])->name('exoReassignments.index');
    Route::post('/exoReassignments', [ExoReassignController::class, 'store'])->name('exoReassignments.store');
    Route::patch('/exoReassignments/{exoReassign}/status', [ExoReassignController::class, 'updateStatus'])->name('exoReassignments.updateStatus');
    Route::delete('/exoReassignments/{exoReassign}', [ExoReassignController::class, 'destroy'])->name('exoReassignments.destroy');


    // ✅ Static routes FIRST
Route::get('/cat2ExoPromotions/fetch', [Cat2ExoPromotionController::class, 'fetch'])
     ->name('cat2ExoPromotions.fetch');
 
Route::get('/cat2ExoPromotions/admin', [Cat2ExoPromotionController::class, 'adminIndex'])
     ->name('cat2ExoPromotions.adminIndex');
 
// ✅ Base routes
Route::get('/cat2ExoPromotions',  [Cat2ExoPromotionController::class, 'index'])
     ->name('cat2ExoPromotions.index');
 
Route::post('/cat2ExoPromotions', [Cat2ExoPromotionController::class, 'store'])
     ->name('cat2ExoPromotions.store');
 
// ✅ Wildcard routes LAST
Route::patch('/cat2ExoPromotions/{cat2ExoPromotion}/status', [Cat2ExoPromotionController::class, 'updateStatus'])
     ->name('cat2ExoPromotions.updateStatus');
 
Route::delete('/cat2ExoPromotions/{cat2ExoPromotion}', [Cat2ExoPromotionController::class, 'destroy'])
     ->name('cat2ExoPromotions.destroy');
 

    
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