<?php

namespace App\Http\Controllers;

use App\Models\CompletionBillet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CompletionBilletController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read completion_billet',   ['only' => ['index', 'show', 'fetch']]);
        $this->middleware('permission:create completion_billet', ['only' => ['store']]);
        $this->middleware('permission:update completion_billet', ['only' => ['update']]);
        $this->middleware('permission:delete completion_billet', ['only' => ['destroy']]);
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

        $billets = CompletionBillet::with([
                'seaDutyRecord',
                'shipsOrder',
                'engroDgoCert',
                'watchstanderCert',
            ])
            ->forUser($user->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('CompletionBillet/Index', [
            'title'             => 'Completion Billet',
            'user'              => $user,
            'completionBillets' => $billets,
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

        $query = CompletionBillet::with([
                'seaDutyRecord',
                'shipsOrder',
                'engroDgoCert',
                'watchstanderCert',
            ])
            ->forUser($request->user_id);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('status',  'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('seaDutyRecord',   fn($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('shipsOrder',       fn($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('engroDgoCert',     fn($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('watchstanderCert', fn($r) => $r->where('name', 'like', "%{$search}%"));
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
            'engro_dgo_cert_id'    => ['required', 'exists:certifications,id'],
            'watchstander_cert_id' => ['required', 'exists:certifications,id'],
        ]);

        // Prevent duplicate pending/approved applications
        $existing = CompletionBillet::forUser($userId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have an active Completion Billet application.',
            ], 422);
        }

        $billet = CompletionBillet::create($validated + ['status' => 'pending']);

        return response()->json($billet->load([
            'seaDutyRecord',
            'shipsOrder',
            'engroDgoCert',
            'watchstanderCert',
        ]), 201);
    }

    /* ══════════════════════════════════════════════════════
       SHOW
    ══════════════════════════════════════════════════════ */
    public function show(CompletionBillet $completionBillet): JsonResponse
    {
        if ($completionBillet->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json($completionBillet->load([
            'seaDutyRecord',
            'shipsOrder',
            'engroDgoCert',
            'watchstanderCert',
            'user:id,name',
        ]));
    }

    /* ══════════════════════════════════════════════════════
       UPDATE  —  admin status / remarks
    ══════════════════════════════════════════════════════ */
    public function updateStatus(Request $request, CompletionBillet $completionBillet)
{
    $request->validate([
        'status'  => 'required|in:pending,approved,rejected',
        'remarks' => 'nullable|string|max:1000',
    ]);

    $completionBillet->update([
        'status'  => $request->status,
        'remarks' => $request->remarks,
    ]);

    return back();
}

    /* ══════════════════════════════════════════════════════
       DESTROY
    ══════════════════════════════════════════════════════ */
    public function destroy(CompletionBillet $completionBillet): JsonResponse
    {
        if ($completionBillet->user_id !== Auth::id()) {
            abort(403);
        }

        $completionBillet->delete();

        return response()->json(['message' => 'Completion Billet application deleted.']);
    }

    /* ══════════════════════════════════════════════════════
       ADMIN INDEX
    ══════════════════════════════════════════════════════ */
    public function adminIndex(Request $request): InertiaResponse
    {
        $query = CompletionBillet::with([
                'user',
                'seaDutyRecord',
                'shipsOrder',
                'engroDgoCert',
                'watchstanderCert',
            ])
            ->when($request->search, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('name',  'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%")
                )
            )
            ->when(
                $request->status && $request->status !== 'all',
                fn($q) => $q->where('status', $request->status)
            )
            ->latest();

        return Inertia::render('CompletionBillet/AdminIndex', [
            'title'             => 'Completion Billet — Admin',
            'filters'           => $request->only(['search', 'status']),
            'completionBillets' => $query->paginate(10)->withQueryString(),
        ]);
    }

    /* ══════════════════════════════════════════════════════
       PRIVATE HELPERS
    ══════════════════════════════════════════════════════ */

    /**
     * Dropdown data — mirrors JuniorHigherBilletController::dropdownData()
     *
     *  sea_duty_record   → documents      table
     *  ships_order       → chips_reports  table
     *  engro_dgo_cert    → certifications table
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

            'engroDgoCerts'  => DB::table('certifications')
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