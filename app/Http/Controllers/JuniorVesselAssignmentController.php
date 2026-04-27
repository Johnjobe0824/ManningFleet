<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\ChipsReport;
use App\Models\JuniorVesselAssignment;
use App\Models\Position;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class JuniorVesselAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read junior_vessel_assignment',   ['only' => ['index', 'show', 'fetch']]);
        $this->middleware('permission:create junior_vessel_assignment', ['only' => ['store']]);
        $this->middleware('permission:update junior_vessel_assignment', ['only' => ['update', 'updateStatus']]);
        $this->middleware('permission:delete junior_vessel_assignment', ['only' => ['destroy']]);
    }

    /**
     * Render the assignment page with all lookup data.
     */
     public function index(Request $request)
{
    $user = $request->user();

    $data = [

        'carryMorningReports' => ChipsReport::select('id', 'name')->orderBy('name')->get(),

        'certifications' => Certification::where('user_id', $user->id)
            ->select('id', 'user_id', 'name')
            ->orderBy('name')
            ->get(),

        'vessels' => Vessel::select('id', 'vessel_name')->orderBy('vessel_name')->get(),

        'positions' => Position::select('id', 'name')->orderBy('name')->get(),
    ];

    if ($request->expectsJson()) {
        return response()->json($data);
    }

    return Inertia::render('Billet/Index', $data);
}

    /**
     * Store a new junior vessel assignment.
     */
   public function store(Request $request)
{

    $validated = $request->validate([
        'user_id'                 => ['required', 'exists:users,id'],
        'carry_morning_report_id' => ['required', 'exists:chips_reports,id'],
        'certificate_mso_dco_id'  => ['required', 'exists:certifications,id'],
        'certificate_jsolc_id'    => ['required', 'exists:certifications,id'],
        'vessel_id'               => ['required', 'exists:vessels,id'],
        'position_id'             => ['required', 'exists:positions,id'],
        'remarks'                 => ['nullable', 'string', 'max:1000'],
    ]);

    try {
        $assignment = DB::transaction(function () use ($validated) {

            return JuniorVesselAssignment::create([
                ...$validated,
                'status' => 'pending',
            ]);
        });

        // Load relationships for frontend table refresh
        $assignment->load([
            'vessel:id,vessel_name',
            'position:id,name',
            'carryMorningReport:id,name',
            'certificateMsoDco:id,name',
            'certificateJsolc:id,name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assignment submitted successfully.',
            'data'    => $assignment,
        ], 201);

    } catch (\Throwable $e) {

        report($e); // logs error

        return response()->json([
            'success' => false,
            'message' => 'Failed to submit assignment.',
        ], 500);
    }
}

    /**
     * Paginated + searchable fetch for the Vue DataTable.
     */
    public function fetch(Request $request)
    {
        $query = JuniorVesselAssignment::with([
            'vessel:id,vessel_name',
            'position:id,name',
            'carryMorningReport:id,name',
            'certificateMsoDco:id,name',
            'certificateJsolc:id,name',
        ])->where('user_id', $request->user_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('vessel',   fn ($v) => $v->where('vessel_name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('position', fn ($p) => $p->where('name', 'LIKE', "%{$search}%"))
                  ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }

        return response()->json(
            $query->latest()->paginate($request->input('per_page', 10))
        );
    }

    /**
     * Update status + optional remarks (admin).
     */
    public function updateStatus(Request $request, JuniorVesselAssignment $juniorVesselAssignment)
    {
        $request->validate([
            'status'  => 'required|string|in:pending,approved,rejected',
            'remarks' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $juniorVesselAssignment->update([
                'status'  => $request->status,
                'remarks' => $request->remarks,
            ]);

            $juniorVesselAssignment->load([
                'vessel:id,vessel_name',
                'position:id,name',
                'carryMorningReport:id,name',
                'certificateMsoDco:id,name',
                'certificateJsolc:id,name',
                'user:id,name,email',
            ]);

            DB::commit();

            return back()->with('success', 'Status updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
    /**
     * Soft-delete an assignment.
     */
    public function destroy(JuniorVesselAssignment $juniorVesselAssignment)
    {
        DB::beginTransaction();
        try {
            $juniorVesselAssignment->delete();
            DB::commit();

            return back()->with('success', 'Assignment deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Error deleting assignment: ' . $th->getMessage());
        }
    }

    /**
 * Admin view — renders the admin page (no user filter).
 */
  public function adminIndex(Request $request)
    {
        $query = JuniorVesselAssignment::with([
            'vessel:id,vessel_name',
            'position:id,name',
            'carryMorningReport:id,name',
            'certificateMsoDco:id,name',
            'certificateJsolc:id,name',
            'user:id,name,email',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('vessel',    fn ($v) => $v->where('vessel_name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('position', fn ($p) => $p->where('name',        'LIKE', "%{$search}%"))
                  ->orWhereHas('user',     fn ($u) => $u->where('name',        'LIKE', "%{$search}%"))
                  ->orWhere('status',      'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $assignments = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Billet/AdminIndex', [
            'juniorVesselAssignments' => $assignments,
            'vessels'                 => Vessel::select('id', 'vessel_name')->orderBy('vessel_name')->get(),
            'positions'               => Position::select('id', 'name')->orderBy('name')->get(),
            'filters'                 => $request->only(['search', 'status']),
        ]);
    }   

/**
 * Admin paginated fetch — all users, searchable.
 */
public function adminFetch(Request $request)
{
    $query = JuniorVesselAssignment::with([
        'vessel:id,vessel_name',
        'position:id,name',
        'carryMorningReport:id,name',
        'certificateMsoDco:id,name',
        'certificateJsolc:id,name',
        'user:id,name,email',
    ]);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->whereHas('vessel',    fn ($v) => $v->where('vessel_name', 'LIKE', "%{$search}%"))
              ->orWhereHas('position', fn ($p) => $p->where('name',        'LIKE', "%{$search}%"))
              ->orWhereHas('user',     fn ($u) => $u->where('name',        'LIKE', "%{$search}%"))
              ->orWhere('status',      'LIKE', "%{$search}%");
        });
    }

    if ($request->filled('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    return response()->json(
        $query->latest()->paginate($request->input('per_page', 10))
    );
}
}