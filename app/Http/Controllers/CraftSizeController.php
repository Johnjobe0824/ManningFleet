<?php

namespace App\Http\Controllers;

use App\Models\CraftSize;
use App\Http\Requests\CraftSizeIndexRequest;
use App\Http\Requests\CraftSizeStoreRequest;
use App\Http\Requests\CraftSizeUpdateRequest;
use App\Models\Position;
use App\Models\Vessel;
use App\Models\VesselPosition;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CraftSizeController extends Controller
{

    public function __construct()
        {

            $this->middleware('permission:create craft_size', ['only' => ['create', 'store']]);
            $this->middleware('permission:read craft_size', ['only' => ['index', 'show']]);
            $this->middleware('permission:update craft_size', ['only' => ['edit', 'update']]);
            $this->middleware('permission:delete craft_size', ['only' => ['destroy', 'destroyBulk']]);
        }

    /**
     * Display a listing of the resource.
     */
    public function index(CraftSizeIndexRequest $request)
    {
        $craftSizes = CraftSize::query();
        if ($request->has('search')) {
            $craftSizes->where('code', 'LIKE', '%'.$request->search.'%');
            $craftSizes->orWhere('description', 'LIKE', '%'.$request->search.'%');
        }

        if ($request->has(['field', 'order'])) {
            $vessels->orderBy($request->field, $request->order);
        }

        return Inertia::render('CraftSize/Index', [
            'title' => 'Craft Size',
            'filters' => $request->all(['search', 'field', 'order']),
            'craft_sizes' => $craftSizes->paginate(10),
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
     public function store(CraftSizeStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $code = $request->input('code');
            $description = $request->input('description');

            $size = CraftSize::create([
                'code' => $code,
                'description' => $description,
            ]);

            DB::commit();
             return back()->with('success', $size->code.' created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

             return back()->with('error', 'Error creating '.$th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CraftSize $craftSize)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CraftSize $craftSize)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CraftSizeUpdateRequest $request, CraftSize $craftSize)
     {
        //
        DB::beginTransaction();
        try {
            $craftSize->update([
                'code' => $request->code,
                'description' => $request->description,
            ]);
            DB::commit();

            return back()->with('success', $craftSize->code.' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Error updating '.$craftSize->code.' '.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CraftSize $craftSize)
     {
        //
        try {
            $craftSize->delete();

            return back()->with('success', $craftSize->code.' deleted successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Error deleting '.$craftSize->code.$th->getMessage());
        }
    }
}
