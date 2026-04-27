<?php

namespace App\Http\Controllers;

use App\Models\ExoReassign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ExoReassignController extends Controller
{
    /* ============================================================
       CONSTRUCTOR — permission middleware
    ============================================================ */
    public function __construct()
    {
        $this->middleware('permission:read exo_reassign',   ['only' => ['index', 'fetch', 'adminIndex']]);
        $this->middleware('permission:create exo_reassign', ['only' => ['store']]);
        $this->middleware('permission:update exo_reassign', ['only' => ['updateStatus']]);
        $this->middleware('permission:delete exo_reassign', ['only' => ['destroy']]);
    }

    /* ============================================================
       INDEX — dropdown data for the form
       GET /exoReassignments
    ============================================================ */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        return response()->json($this->dropdownData($userId));
    }

    /* ============================================================
       FETCH — paginated table data (user-facing)
       GET /exoReassignments/fetch
    ============================================================ */
    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'page'     => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search'   => 'nullable|string|max:255',
        ]);

        $query = ExoReassign::with([
                'assignmentOrders:id,name',
                'performanceEval:id,name',
                'certification:id,name',
            ])
            ->forUser($request->user_id);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('status',  'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('assignmentOrders', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('performanceEval',  fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('certification',    fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $paginated = $query->latest()->paginate($request->per_page ?? 10);

        $paginated->getCollection()->transform(fn (ExoReassign $record) => [
            'id'                => $record->id,
            'status'            => $record->status,
            'remarks'           => $record->remarks,
            'scheduled_at'      => $record->scheduled_at,
            'assignment_orders' => $record->assignmentOrders,
            'performance_eval'  => $record->performanceEval,
            'certification'     => $record->certification,
            'created_at'        => $record->created_at,
        ]);

        return response()->json($paginated);
    }

    /* ============================================================
       STORE — submit new application
       POST /exoReassignments
    ============================================================ */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'user_id'              => ['required', 'integer', Rule::in([$userId])],
            'assignment_orders_id' => ['required', 'exists:documents,id'],
            'performance_eval_id'  => ['required', 'exists:documents,id'],
            'certification_id'     => ['nullable', 'exists:certifications,id'],
        ]);

        $existing = ExoReassign::forUser($userId)
            ->where('status', ExoReassign::STATUS_PENDING)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending EXO Reassignment application. Please wait for it to be processed before submitting a new one.',
            ], 422);
        }

        $record = ExoReassign::create($validated + ['status' => ExoReassign::STATUS_PENDING]);

        return response()->json($record->load([
            'assignmentOrders:id,name',
            'performanceEval:id,name',
            'certification:id,name',
        ]), 201);
    }

    /* ============================================================
       UPDATE STATUS — admin updates status / remarks / schedule
       PATCH /exoReassignments/{exoReassign}/status
    ============================================================ */
    public function updateStatus(Request $request, ExoReassign $exoReassign): JsonResponse
    {
        $validated = $request->validate([
            'status'       => ['required', Rule::in(ExoReassign::STATUSES)],
            'remarks'      => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $exoReassign->update($validated);

        return response()->json([
            'message' => 'Status updated successfully.',
            'data'    => $exoReassign->fresh([
                'user:id,name,email',
                'assignmentOrders:id,name',
                'performanceEval:id,name',
                'certification:id,name',
            ]),
        ]);
    }

    /* ============================================================
       DESTROY — soft delete
       DELETE /exoReassignments/{exoReassign}
    ============================================================ */
    public function destroy(ExoReassign $exoReassign): JsonResponse
    {
        if ($exoReassign->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $exoReassign->isPending()) {
            return response()->json([
                'message' => 'Only pending applications can be deleted.',
            ], 422);
        }

        $exoReassign->delete();

        return response()->json(['message' => 'EXO Reassignment application deleted.']);
    }

    /* ============================================================
       ADMIN INDEX — Inertia page + JSON for ajax refetch
       GET /exoReassignments/admin
    ============================================================ */
    public function adminIndex(Request $request): JsonResponse|InertiaResponse
    {
        $query = ExoReassign::with([
                'user:id,name,email',
                'assignmentOrders:id,name',
                'performanceEval:id,name',
                'certification:id,name',
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

        $paginated = $query->paginate($request->per_page ?? 10)->withQueryString();

        // Ajax refetch from the Vue admin component
        if ($request->wantsJson()) {
            $paginated->getCollection()->transform(fn (ExoReassign $record) => [
                'id'                => $record->id,
                'status'            => $record->status,
                'remarks'           => $record->remarks,
                'scheduled_at'      => $record->scheduled_at,
                'user'              => $record->user,
                'assignment_orders' => $record->assignmentOrders,
                'performance_eval'  => $record->performanceEval,
                'certification'     => $record->certification,
                'created_at'        => $record->created_at,
            ]);

            return response()->json($paginated);
        }

        return Inertia::render('ExoReassign/AdminIndex', [
            'title'        => 'EXO Reassignment — Admin',
            'filters'      => $request->only(['search', 'status']),
            'exoReassigns' => $paginated,
        ]);
    }

    /* ============================================================
       PRIVATE HELPERS
    ============================================================ */
    private function dropdownData(int $userId): array
    {
        return [
            'assignmentOrders' => DB::table('documents')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'performanceEvals' => DB::table('documents')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'certifications' => DB::table('certifications')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ];
    }
}