<?php

namespace App\Http\Controllers;

use App\Models\JuniorHigherBillet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class JuniorHigherBilletController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:read junior_higher_billet',   ['only' => ['index', 'show', 'fetch']]);
        $this->middleware('permission:create junior_higher_billet', ['only' => ['store']]);
        $this->middleware('permission:update junior_higher_billet', ['only' => ['update', 'updateStatus']]);
        $this->middleware('permission:delete junior_higher_billet', ['only' => ['destroy']]);
    }


    /**
     * GET /juniorHigherBillets
     *
     * - Browser / Inertia  →  renders the Vue page with SSR props
     * - Axios (fetchIndexData) with Accept: application/json
     *                       →  returns JSON dropdown data only
     */



    public function index(Request $request)
    {
        $user = Auth::user();

        // When called via axios for dropdown refresh, return JSON only
        if ($request->expectsJson()) {
            return response()->json(
                $this->dropdownData($user->id)
            );
        }

        // Full Inertia page with SSR-seeded props
        $billets = JuniorHigherBillet::with([
                'seaDutyRecord',
                'shipsOrder',
                'endorsement',
                'dutyPerformanceCert',
                'jsolcCert',
                'watchstanderCert',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('JuniorHigherBillet/Index', [
            'title'               => 'Junior Higher Billet',
            'user'                => $user,
            'juniorHigherBillets' => $billets,
            ...$this->dropdownData($user->id),
        ]);
    }

    /**
     * GET /juniorHigherBillets/fetch
     * Lazy paginated DataTable with search.
     */
    public function fetch(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'page'     => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search'   => 'nullable|string|max:255',
        ]);

        $query = JuniorHigherBillet::with([
                'seaDutyRecord',
                'shipsOrder',
                'endorsement',
                'dutyPerformanceCert',
                'jsolcCert',
                'watchstanderCert',
            ])
            ->where('user_id', $request->user_id);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('status',  'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('seaDutyRecord',      fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('shipsOrder',          fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('endorsement',         fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('dutyPerformanceCert', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('jsolcCert',           fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('watchstanderCert',    fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        return response()->json(
            $query->latest()->paginate($request->per_page ?? 10)
        );
    }

    /**
     * POST /juniorHigherBillets
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'                  => 'required|exists:users,id',
            'sea_duty_record_id'       => 'required|exists:documents,id',
            'ships_order_id'           => 'required|exists:chips_reports,id',
            'endorsement_id'           => 'required|exists:chips_reports,id',
            'duty_performance_cert_id' => 'required|exists:certifications,id',
            'jsolc_cert_id'            => 'required|exists:certifications,id',
            'watchstander_cert_id'     => 'nullable|exists:certifications,id',
        ]);

        JuniorHigherBillet::create($validated);

        return response()->json(
            ['message' => 'Billet application submitted successfully.'],
            201
        );
    }

    /**
     * DELETE /juniorHigherBillets/{juniorHigherBillet}
     */
       public function destroy(JuniorHigherBillet $juniorHigherBillet)
    {
        $this->authorize('delete', $juniorHigherBillet);

        $juniorHigherBillet->delete();

        return response()->json(['message' => 'Billet application deleted.']);
    }

    /* ------------------------------------------------------------------ */

    /**
     * Dropdown data via DB facade.
     *
     *  sea_duty_record        → documents      table
     *  ships_order            → chips_reports  table
     *  endorsement            → chips_reports  table
     *  duty_performance_cert  → certifications table  (model: Certification)
     *  jsolc_cert             → certifications table  (model: Certification)
     *  watchstander_cert      → certifications table  (nullable)
     */
    private function dropdownData(int $userId): array
    {
        return [
            'seaDutyRecords'       => DB::table('documents')
                                        ->where('user_id', $userId)
                                        ->select('id', 'name')->get(),

            'shipsOrders'          => DB::table('chips_reports')
                                        ->where('user_id', $userId)
                                        ->select('id', 'name')->get(),

            'endorsements'         => DB::table('chips_reports')
                                        ->where('user_id', $userId)
                                        ->select('id', 'name')->get(),

            'dutyPerformanceCerts' => DB::table('certifications')
                                        ->where('user_id', $userId)
                                        ->select('id', 'name')->get(),

            'jsolcCerts'           => DB::table('certifications')
                                        ->where('user_id', $userId)
                                        ->select('id', 'name')->get(),

            'watchstanderCerts'    => DB::table('certifications')
                                        ->where('user_id', $userId)
                                        ->select('id', 'name')->get(),
        ];
    }

    public function adminIndex(Request $request)
{
    $query = JuniorHigherBillet::with([
            'user', 'seaDutyRecord', 'shipsOrder', 'endorsement',
            'dutyPerformanceCert', 'jsolcCert', 'watchstanderCert',
        ])
        ->when($request->search, fn($q) =>
            $q->whereHas('user', fn($u) =>
                $u->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
        )
        ->when($request->status && $request->status !== 'all', fn($q) =>
            $q->where('status', $request->status)
        )
        ->latest();

    return inertia('JuniorHigherBillet/AdminIndex', [
        'title'               => 'Junior Higher Billet — Admin',
        'filters'             => $request->only(['search', 'status']),
        'juniorHigherBillets' => $query->paginate(10)->withQueryString(),
    ]);
}

public function updateStatus(Request $request, JuniorHigherBillet $juniorHigherBillet)
{
    $request->validate([
        'status'  => 'required|in:pending,approved,rejected',
        'remarks' => 'nullable|string|max:1000',
    ]);

    $juniorHigherBillet->update([
        'status'  => $request->status,
        'remarks' => $request->remarks,
    ]);

    return back();
}
}