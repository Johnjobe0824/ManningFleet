<?php

namespace App\Http\Controllers;

use App\Models\{
    Vessel,
    Position,
    VesselPosition
};
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\VesselIndexRequest;
use App\Http\Requests\VesselStoreRequest;
use App\Http\Requests\VesselUpdateRequest;
use Spatie\Permission\Models\Permission;

class VesselController extends Controller
{

    public function __construct()
    {

        $this->middleware('permission:create vessel', ['only' => ['create', 'store']]);
        $this->middleware('permission:read vessel', ['only' => ['index', 'show']]);
        $this->middleware('permission:update vessel', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete vessel', ['only' => ['destroy', 'destroyBulk']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(VesselIndexRequest $request)
    {
        $vessels = Vessel::query();
        if ($request->has('search')) {
            $vessels->where('vessel_name', 'LIKE', "%" . $request->search . "%");
            $vessels->orWhere('vessel_bow_no', 'LIKE', "%" . $request->search . "%");
            $vessels->orWhere('category', 'LIKE', "%" . $request->search . "%");
            $vessels->orWhere('size', 'LIKE', "%" . $request->search . "%");
            $vessels->orWhere('status', 'LIKE', "%" . $request->search . "%");
        }

        if ($request->has(['field', 'order'])) {
            $vessels->orderBy($request->field, $request->order);
        }
        
        return Inertia::render('Vessel/Index', [
            'title'         => "Vessel",
            'filters'       => $request->all(['search', 'field', 'order']),
            'vessels'         => $vessels->paginate(10),
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
    public function store(VesselStoreRequest $request)
    {
        //
        DB::beginTransaction();
        try {

            
            $vessel = Vessel::create([
                'vessel_name'   => $request->vessel_name,
                'vessel_bow_no' => $request->vessel_bow_no,
                'category'  => $request->category,
                'size'  => $request->size,
                'status' => $request->status
            ]);

            // automatic create position in vessel_positions table
            $defaultPositions = Position::all();

            foreach ($defaultPositions as $position) {
                VesselPosition::create([
                    'vessel_id'   => $vessel->id,
                    'position_id' => $position->id,
                    'status'      => 'available',
                ]);
            }

            DB::commit();
            return back()->with('success', $vessel->vessel_name. ' created successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error creating ' .  $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vessel $vessel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vessel $vessel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VesselUpdateRequest $request, Vessel $vessel)
    {
        //
        DB::beginTransaction();
        try {
            $vessel->update([
                'vessel_name'   => $request->vessel_name,
                'vessel_bow_no' => $request->vessel_bow_no,
                'category'  => $request->category,
                'size'  => $request->size,
                'status' => $request->status
            ]);
            DB::commit();
            return back()->with('success', $vessel->vessel_name. ' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error updating ' . $vessel->vessel_name. $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vessel $vessel)
    {
        //
        try {
            $vessel->delete();
            return back()->with('success', $vessel->vessel_name. ' deleted successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Error deleting ' . $vessel->vessel_name . $th->getMessage());
        }
    }
}
