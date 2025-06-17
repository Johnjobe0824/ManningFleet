<?php

namespace App\Http\Controllers;

use App\Models\SeaDutyRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\{
    SeaDutyRecordIndexRequest,
    SeaDutyRecordStoreRequest,
    SeaDutyRecordUpdateRequest
};
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class SeaDutyRecordController extends Controller
{

     public function __construct()
    {

        $this->middleware('permission:create sea_duty', ['only' => ['create', 'store']]);
        $this->middleware('permission:read sea_duty', ['only' => ['index', 'show']]);
        $this->middleware('permission:update sea_duty', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete sea_duty', ['only' => ['destroy', 'destroyBulk']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SeaDutyRecordIndexRequest $request)
    {
        //
        $seaDutyRecords = SeaDutyRecord::query()->with(['user', 'vessel', 'position']);

        if ($request->has('search')) {
            $searchTerm = $request->search;

            $seaDutyRecords->where(function ($query) use ($searchTerm) {
                $query->where('authority', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('tst', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('tmc', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('date_to', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('date_from', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'LIKE', "%{$searchTerm}%"))
                    ->orWhereHas('vessel', fn($q) => $q->where('vessel_name', 'LIKE', "%{$searchTerm}%"))
                    ->orWhereHas('position', fn($q) => $q->where('name', 'LIKE', "%{$searchTerm}%"));
            });
        }

        if ($request->has(['field', 'order'])) {
            $seaDutyRecords->orderBy($request->field, $request->order);
        }
        
        return Inertia::render('SeaDutyRecord/Index', [
            'title'         => "Sea Duty Record",
            'filters'       => $request->all(['search', 'field', 'order']),
            'seaDutyRecords'   => $seaDutyRecords->paginate(10),
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
    public function store(SeaDutyRecordStoreRequest $request)
{
    DB::beginTransaction();

    try {
        $seaDutyRecord = SeaDutyRecord::create([
            'user_id'     => $request->user_id,
            'vessel_id'   => $request->vessel_id,
            'position_id' => $request->position_id,
            'date_from'   => \Carbon\Carbon::parse($request->date_from)->format('Y-m-d'),
            'date_to'     => \Carbon\Carbon::parse($request->date_to)->format('Y-m-d'),
            'authority'   => $request->authority,
            'tst'         => $request->tst,
            'tmc'         => $request->tmc,
            'status'      => $request->status,
            'is_active'   => $request->is_active ?? false,
        ]);


        DB::commit();
        return back()->with('success', 'Sea Duty Record added successfully.');
    } catch (\Throwable $th) {
        DB::rollBack();

        return response()->json([
            'message' => 'Error creating sea duty record: ' . $th->getMessage(),
        ], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show(SeaDutyRecord $seaDutyRecord)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SeaDutyRecord $seaDutyRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SeaDutyRecord $seaDutyRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SeaDutyRecord $seaDutyRecord)
    {
        //
    }

    public function updateStatus(Request $request, SeaDutyRecord $seaDutyRecord)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected', // adjust as needed
        ]);

        $seaDutyRecord->status = $request->status;
        $seaDutyRecord->save();

        return response()->json([
            'message' => 'Status updated successfully.',
            'status' => $seaDutyRecord->status,
        ]);
    }
}
