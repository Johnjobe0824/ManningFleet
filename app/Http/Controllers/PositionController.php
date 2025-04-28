<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\{
    PositionIndexRequest,
    PositionStoreRequest,
    PositionUpdateRequest
};
use Spatie\Permission\Models\Permission;

class PositionController extends Controller
{

    public function __construct()
    {

        $this->middleware('permission:create position', ['only' => ['create', 'store']]);
        $this->middleware('permission:read position', ['only' => ['index', 'show']]);
        $this->middleware('permission:update position', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete position', ['only' => ['destroy', 'destroyBulk']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PositionIndexRequest $request)
    {
        $positions = Position::query();
        if ($request->has('search')) {
            $positions->where('name', 'LIKE', "%" . $request->search . "%");
            $positions->orWhere('code', 'LIKE', "%" . $request->search . "%");
        }

        if ($request->has(['field', 'order'])) {
            $positions->orderBy($request->field, $request->order);
        }
        
        return Inertia::render('Position/Index', [
            'title'         => "Position",
            'filters'       => $request->all(['search', 'field', 'order']),
            'positions'         => $positions->paginate(10),
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
    public function store(PositionStoreRequest $request)
    {
        //
        DB::beginTransaction();
        try {
            $position = Position::create([
                'name'          => $request->name,
                'code'          => $request->code
            ]);
            DB::commit();
            return back()->with('success', $position->name. ' created successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error creating ' .  $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PositionUpdateRequest $request, Position $position)
    {
        //
        DB::beginTransaction();
        try {
            $position->update([
                'name'          => $request->name,
                'code'          => $request->code
            ]);
            DB::commit();
            return back()->with('success', $position->name. ' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error updating ' .  $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(position $position)
    {
        //
        try {
            $position->delete();
            return back()->with('success', $position->name. ' deleted successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Error deleting ' . $position->name . $th->getMessage());
        }
    }
}
