<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonalIndexRequest;
use App\Http\Requests\PersonalStoreRequest;
use App\Http\Requests\PersonalUpdateRequest;
use App\Models\PersonalInformation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PersonalInformationController extends Controller
{
    //
    public function __construct()
    {

        // $this->middleware('permission:create vessel', ['only' => ['create', 'store']]);
        // $this->middleware('permission:read vessel', ['only' => ['index', 'show']]);
        // $this->middleware('permission:update vessel', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:delete vessel', ['only' => ['destroy', 'destroyBulk']]);
    }

    // public function index(PersonalIndexRequest $request)
    // {
    //     $vessels = PersonalInfo::query();
    //     if ($request->has('search')) {
    //         $vessels->where('vessel_name', 'LIKE', "%" . $request->search . "%");
    //         $vessels->orWhere('vessel_bow_no', 'LIKE', "%" . $request->search . "%");
    //         $vessels->orWhere('category', 'LIKE', "%" . $request->search . "%");
    //         $vessels->orWhere('size', 'LIKE', "%" . $request->search . "%");
    //         $vessels->orWhere('status', 'LIKE', "%" . $request->search . "%");
    //     }

    //     if ($request->has(['field', 'order'])) {
    //         $vessels->orderBy($request->field, $request->order);
    //     }

    //     return Inertia::render('Vessel/Index', [
    //         'title'         => "Vessel",
    //         'filters'       => $request->all(['search', 'field', 'order']),
    //         'vessels'         => $vessels->paginate(10),
    //     ]);
    // }

    public function store(PersonalStoreRequest $request)
    {
        //

        DB::beginTransaction();
        try {
            $personalInfo = PersonalInformation::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix,
                'serial_no' => $request->serial_no,
                'address' => $request->address,
                'gender' => $request->gender,
                'civil_status' => $request->civil_status,
                'user_id' => $request->user_id,
                'rank_id' => $request->rank_id,
                'age' => $request->age,
                'birth_date' => $request->birth_date,
            ]);

            DB::commit();

            // return back()->with('success', $personalInfo->serial_no. ' created successfully.');
            return Redirect::route('profile.edit');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Error creating '.$th->getMessage());
        }
    }

    public function update(PersonalUpdateRequest $request, PersonalInformation $personalInfo): RedirectResponse
    {
        //
        DB::beginTransaction();
        try {
            $personalInfo->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix,
                'serial_no' => $request->serial_no,
                'address' => $request->address,
                'gender' => $request->gender,
                'civil_status' => $request->civil_status,
                'user_id' => $request->user_id,
                'rank_id' => $request->rank_id,
                'age' => $request->age,
                'birth_date' => $request->birth_date,
            ]);

            DB::commit();

            // return back()->with('success', $personalInfo->serial_no. ' updated successfully.');
            return Redirect::route('profile.edit')->with('success', 'Personnel with serial number '.$personalInfo->serial_no.' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Error updating '.$th->getMessage());
            // return Redirect::route('profile.edit');
        }
    }
}
