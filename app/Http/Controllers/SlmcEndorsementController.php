<?php

namespace App\Http\Controllers;

use App\Models\SlmcEndorsement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SlmcEndorsementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read slmc_endorsement',   ['only' => ['index', 'show', 'fetch']]);
        $this->middleware('permission:create slmc_endorsement', ['only' => ['store']]);
        $this->middleware('permission:update slmc_endorsement', ['only' => ['updateStatus']]);
        $this->middleware('permission:delete slmc_endorsement', ['only' => ['destroy']]);
    }

    /* ══════════════════════════════════════════════════════
       INDEX  —  Inertia page OR JSON (Accept: application/json)
    ══════════════════════════════════════════════════════ */
    public function index(Request $request): InertiaResponse|JsonResponse
    {
        $user = Auth::user();

        if ($request->expectsJson()) {
            return response()->json($this->dropdownData($user->id));
        }

        $endorsements = SlmcEndorsement::with([
                'seaDutyRecord',
                'shipsOrder',
                'graduationCert',
                'watchstanderCert',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('SlmcEndorsement/Index', [
            'title'            => 'SLMC Endorsement',
            'user'             => $user,
            'slmcEndorsements' => $endorsements,
            ...$this->dropdownData($user->id),
        ]);
    }

    /* ══════════════════════════════════════════════════════
       FETCH  —  paginated JSON for the DataTable
    ══════════════════════════════════════════════════════ */
    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'page'     => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search'   => 'nullable|string|max:255',
        ]);

        $query = SlmcEndorsement::with([
                'seaDutyRecord',
                'shipsOrder',
                'graduationCert',
                'watchstanderCert',
            ])
            ->where('user_id', $request->user_id);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('status',    'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('seaDutyRecord',   fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('shipsOrder',       fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('graduationCert',   fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('watchstanderCert', fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        return response()->json(
            $query->latest()->paginate($request->per_page ?? 10)
        );
    }

    /* ══════════════════════════════════════════════════════
       STORE
    ══════════════════════════════════════════════════════ */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'user_id'              => ['required', 'integer', Rule::in([$userId])],
            'sea_duty_record_id'   => ['required', 'exists:documents,id'],
            'ships_order_id'       => ['required', 'exists:chips_reports,id'],
            'graduation_cert_id'   => ['required', 'exists:certifications,id'],
            'watchstander_cert_id' => ['required', 'exists:certifications,id'],
        ]);

        // Prevent duplicate pending/approved applications
        $existing = SlmcEndorsement::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have an active SLMC Endorsement application.',
            ], 422);
        }

        $endorsement = SlmcEndorsement::create($validated + ['status' => 'pending']);

        return response()->json($endorsement->load([
            'seaDutyRecord',
            'shipsOrder',
            'graduationCert',
            'watchstanderCert',
        ]), 201);
    }

    /* ══════════════════════════════════════════════════════
       SHOW
    ══════════════════════════════════════════════════════ */
    public function show(SlmcEndorsement $slmcEndorsement): JsonResponse
    {
        if ($slmcEndorsement->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json($slmcEndorsement->load([
            'seaDutyRecord',
            'shipsOrder',
            'graduationCert',
            'watchstanderCert',
            'user:id,name',
        ]));
    }

    /* ══════════════════════════════════════════════════════
       UPDATE STATUS  —  admin status / remarks
    ══════════════════════════════════════════════════════ */
    public function updateStatus(Request $request, SlmcEndorsement $slmcEndorsement)
    {
        $request->validate([
            'status'  => 'required|in:pending,approved,rejected',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $slmcEndorsement->update([
            'status'  => $request->status,
            'remarks' => $request->remarks,
        ]);

        return back();
    }

    /* ══════════════════════════════════════════════════════
       DESTROY
    ══════════════════════════════════════════════════════ */
    public function destroy(SlmcEndorsement $slmcEndorsement): JsonResponse
    {
        if ($slmcEndorsement->user_id !== Auth::id()) {
            abort(403);
        }

        $slmcEndorsement->delete();

        return response()->json(['message' => 'SLMC Endorsement application deleted.']);
    }

    /* ══════════════════════════════════════════════════════
       ADMIN INDEX
    ══════════════════════════════════════════════════════ */
    public function adminIndex(Request $request): InertiaResponse
    {
        $query = SlmcEndorsement::with([
                'user',
                'seaDutyRecord',
                'shipsOrder',
                'graduationCert',
                'watchstanderCert',
            ])
            ->when($request->search, fn ($q) =>
                $q->whereHas('user', fn ($u) =>
                    $u->where('name',  'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%")
                )
            )
            ->when(
                $request->status && $request->status !== 'all',
                fn ($q) => $q->where('status', $request->status)
            )
            ->latest();

        return Inertia::render('SlmcEndorsement/AdminIndex', [
            'title'            => 'SLMC Endorsement — Admin',
            'filters'          => $request->only(['search', 'status']),
            'slmcEndorsements' => $query->paginate(10)->withQueryString(),
        ]);
    }

    /* ══════════════════════════════════════════════════════
       PRIVATE HELPERS
    ══════════════════════════════════════════════════════ */

    /**
     * Dropdown data — mirrors CompletionBilletController::dropdownData()
     *
     *  sea_duty_record   → documents      table
     *  ships_order       → chips_reports  table
     *  graduation_cert   → certifications table
     *  watchstander_cert → certifications table
     */
    private function dropdownData(int $userId): array
    {
        return [
            'seaDutyRecords' => DB::table('documents')
                                    ->where('user_id', $userId)
                                    ->select('id', 'name')
                                    ->get(),

            'shipsOrders'    => DB::table('chips_reports')
                                    ->where('user_id', $userId)
                                    ->select('id', 'name')
                                    ->get(),

            'graduationCerts' => DB::table('certifications')
                                    ->where('user_id', $userId)
                                    ->select('id', 'name')
                                    ->get(),

            'watchstanderCerts' => DB::table('certifications')
                                        ->where('user_id', $userId)
                                        ->select('id', 'name')
                                        ->get(),
        ];
    }
}