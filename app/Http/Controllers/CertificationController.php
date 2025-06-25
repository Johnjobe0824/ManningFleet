<?php

namespace App\Http\Controllers;

use App\Http\Requests\CertificationIndexRequest;
use App\Http\Requests\CertificationStoreRequest;
use App\Http\Requests\CertificationUpdateRequest;
use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class CertificationController extends Controller
{
    public function __construct()
    {

        // $this->middleware('permission:create certificates', ['only' => ['create', 'store']]);
        $this->middleware('permission:read certificate', ['only' => ['index', 'show']]);
        $this->middleware('permission:update certificate', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete certificate', ['only' => ['destroy', 'destroyBulk']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CertificationIndexRequest $request)
    {
        $certificates = Certification::query();
        if ($request->has('search')) {
            $certificates->where('name', 'LIKE', '%'.$request->search.'%');
            $certificates->where('training_center', 'LIKE', '%'.$request->search.'%');
            $certificates->where('batch_no', 'LIKE', '%'.$request->search.'%');
            $certificates->where('date', 'LIKE', '%'.$request->search.'%');
            $certificates->orWhere('status', 'LIKE', '%'.$request->search.'%');
        }

        if ($request->has(['field', 'order'])) {
            $certificates->orderBy($request->field, $request->order);
        }

        return Inertia::render('Certifications/Index', [
            'title' => 'Training & Certifications',
            'filters' => $request->all(['search', 'field', 'order']),
            'certificates' => $certificates->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CertificationStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $certificate = Certification::create([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'training_center' => $request->training_center,
                'batch_no' => $request->batch_no,
                'date' => $request->date,
                'status' => $request->status,
            ]);

            DB::commit();

            return back()->with('success', $certificate->name.' added successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating certificate: '.$th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Certification $certification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certification $certification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CertificationUpdateRequest $request, Certification $certificate)
    {
        //
        DB::beginTransaction();
        try {
            $certificate->update([
                'name' => $request->name,
                'training_center' => $request->training_center,
                'batch_no' => $request->batch_no,
                'status' => $request->status,
                'date' => $request->date,
            ]);
            DB::commit();

            return back()->with('success', $certificate->name.' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Error updating '.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certification $certificate)
    {
        //
        try {
            $rank->delete();

            return back()->with('success', $certificate->name.' deleted successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Error deleting '.$certificate->name.$th->getMessage());
        }
    }

    public function updateStatus(Request $request, Certification $certificate)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected', // adjust as needed
        ]);

        $certificate->status = $request->status;
        $certificate->save();

        return response()->json([
            'message' => 'Status updated successfully.',
            'status' => $certificate->status,
        ]);
    }
}
