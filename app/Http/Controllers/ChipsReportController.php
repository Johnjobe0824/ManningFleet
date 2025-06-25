<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChipsReportIndexRequest;
use App\Http\Requests\ChipsReportStoreRequest;
use App\Http\Requests\ChipsReportUpdateRequest;
use App\Models\ChipsReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class ChipsReportController extends Controller
{
    public function __construct()
    {

        // $this->middleware('permission:create chipsReport', ['only' => ['create', 'store']]);
        $this->middleware('permission:read chipsReport', ['only' => ['index', 'show']]);
        $this->middleware('permission:update chipsReport', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete chipsReport', ['only' => ['destroy', 'destroyBulk']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ChipsReportIndexRequest $request)
    {
        $chipsReports = ChipsReport::query();
        if ($request->has('search')) {
            $chipsReports->where('name', 'LIKE', '%'.$request->search.'%');
            $chipsReports->orWhere('status', 'LIKE', '%'.$request->search.'%');
        }

        if ($request->has(['field', 'order'])) {
            $chipsReports->orderBy($request->field, $request->order);
        }

        return Inertia::render('ChipsReport/Index', [
            'title' => 'Chips Reports',
            'filters' => $request->all(['search', 'field', 'order']),
            'chipsReports' => $chipsReports->paginate(10),
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
    public function store(ChipsReportStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $name = $request->input('name');
            $user_id = $request->input('user_id');
            $status = $request->input('status');

            $path = $file->store('chipsReports', 'public');

            $chip = ChipsReport::create([
                'user_id' => $user_id,
                'name' => $name,
                'path' => $path,
                'status' => $status,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Chips Report uploaded successfully.',
                'chipsReport' => $chip,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating chipsReport: '.$th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ChipsReport $chipsReport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChipsReport $chipsReport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChipsReportUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // Find the existing chipsReport by ID
            $chip = ChipsReport::findOrFail($id);

            if ($request->hasFile('file')) {
                if (Storage::exists('public/'.$chip->path)) {
                    Storage::delete('public/'.$chip->path);
                }

                $file = $request->file('file');
                $path = $file->store('chipsReports', 'public');

                $chip->path = $path;
                $chip->name = $file->getClientOriginalName();
            }

            $chip->status = $request->input('status', $chip->status);
            $chip->save();

            DB::commit();

            return back()->with('success', $chip->name.' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Error updating Chips Report: '.$th->getMessage());
        }
    }

    public function updateStatus(Request $request, ChipsReport $chipsReport)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected', // adjust as needed
        ]);

        $chipsReport->status = $request->status;
        $chipsReport->save();

        return response()->json([
            'message' => 'Status updated successfully.',
            'status' => $chipsReport->status,
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Find the chipsReport by ID
            $doc = ChipsReport::findOrFail($id);

            if (Storage::exists('public/'.$doc->path)) {
                Storage::delete('public/'.$doc->path);
            }

            $doc->delete();

            DB::commit();

            // Return success message
            return back()->with('success', 'Chips Report deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Error deleting Chips Report: '.$th->getMessage());
        }
    }
}
