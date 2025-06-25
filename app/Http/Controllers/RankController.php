<?php

namespace App\Http\Controllers;

use App\Http\Requests\RankIndexRequest;
use App\Http\Requests\RankStoreRequest;
use App\Http\Requests\RankUpdateRequest;
use App\Models\Rank;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RankController extends Controller
{
    public function __construct()
    {

        $this->middleware('permission:create rank', ['only' => ['create', 'store']]);
        $this->middleware('permission:read rank', ['only' => ['index', 'show']]);
        $this->middleware('permission:update rank', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete rank', ['only' => ['destroy', 'destroyBulk']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(RankIndexRequest $request)
    {
        $ranks = Rank::query();
        if ($request->has('search')) {
            $ranks->where('name', 'LIKE', '%'.$request->search.'%');
            $ranks->orWhere('type', 'LIKE', '%'.$request->search.'%');
        }

        if ($request->has(['field', 'order'])) {
            $ranks->orderBy($request->field, $request->order);
        }

        return Inertia::render('Rank/Index', [
            'title' => 'Rank',
            'filters' => $request->all(['search', 'field', 'order']),
            'ranks' => $ranks->paginate(10),
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
    public function store(RankStoreRequest $request)
    {
        //
        DB::beginTransaction();
        try {
            $rank = Rank::create([
                'name' => $request->name,
                'type' => $request->type,
            ]);
            $rankId = $rank->id;
            $rank->update(['level' => $rankId]);
            DB::commit();

            return back()->with('success', $rank->name.' created successfully.');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Error creating '.$th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Rank $rank)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rank $rank)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RankUpdateRequest $request, Rank $rank)
    {
        //
        DB::beginTransaction();
        try {
            $rank->update([
                'name' => $request->name,
                'type' => $request->type,
            ]);
            DB::commit();

            return back()->with('success', $rank->name.' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Error updating '.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rank $rank)
    {
        //
        try {
            $rank->delete();

            return back()->with('success', $rank->name.' deleted successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Error deleting '.$rank->name.$th->getMessage());
        }
    }
}
