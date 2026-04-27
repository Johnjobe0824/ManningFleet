<?php

namespace App\Http\Controllers;

use App\Models\Cat2ExoPromotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class Cat2ExoPromotionController extends Controller
{
    /* ============================================================
       CONSTRUCTOR — permission middleware
    ============================================================ */
    // public function __construct()
    // {
    //     $this->middleware('permission:read cat2_exo',   ['only' => ['index', 'fetch', 'adminIndex']]);
    //     $this->middleware('permission:create cat2_exo', ['only' => ['store']]);
    //     $this->middleware('permission:update cat2_exo', ['only' => ['updateStatus']]);
    //     $this->middleware('permission:delete cat2_exo', ['only' => ['destroy']]);
    // }

    /* ============================================================
       INDEX — dropdown data for the form
       GET /cat2ExoPromotions
    ============================================================ */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        return response()->json($this->dropdownData($userId));
    }

    /* ============================================================
       FETCH — paginated table data (user-facing)
       GET /cat2ExoPromotions/fetch
    ============================================================ */
    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'page'     => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search'   => 'nullable|string|max:255',
        ]);

        $query = Cat2ExoPromotion::with([
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

        $paginated->getCollection()->transform(fn (Cat2ExoPromotion $record) => [
            'id'               => $record->id,
            'status'           => $record->status,
            'remarks'          => $record->remarks,
            'scheduled_at'     => $record->scheduled_at,
            'assignment_orders'=> $record->assignmentOrders,
            'performance_eval' => $record->performanceEval,
            'certification'    => $record->certification,
            'created_at'       => $record->created_at,
        ]);

        return response()->json($paginated);
    }

    /* ============================================================
       STORE — submit new application
       POST /cat2ExoPromotions
    ============================================================ */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'user_id'                    => ['required', 'integer', Rule::in([$userId])],
            'assignment_orders_id'       => ['required', 'exists:documents,id'],
            'performance_eval_id'        => ['required', 'exists:documents,id'],
            'certification_id'           => ['nullable', 'exists:certifications,id'],

            // Step 1 checklist flags
            'checklist_sea_duty_records' => ['boolean'],
            'checklist_min_residency'    => ['boolean'],
            'checklist_steaming_hours'   => ['boolean'],
            'checklist_cat1_compliance'  => ['boolean'],
        ]);

        // Prevent duplicate pending applications
        $existing = Cat2ExoPromotion::forUser($userId)
            ->where('status', Cat2ExoPromotion::STATUS_PENDING)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending CAT 2 EXO Promotion application. Please wait for it to be processed before submitting a new one.',
            ], 422);
        }

        $record = Cat2ExoPromotion::create(array_merge($validated, [
            'status'                     => Cat2ExoPromotion::STATUS_PENDING,
            'checklist_sea_duty_records' => $validated['checklist_sea_duty_records'] ?? true,
            'checklist_min_residency'    => $validated['checklist_min_residency']    ?? true,
            'checklist_steaming_hours'   => $validated['checklist_steaming_hours']   ?? true,
            'checklist_cat1_compliance'  => $validated['checklist_cat1_compliance']  ?? true,
        ]));

        return response()->json($record->load([
            'assignmentOrders:id,name',
            'performanceEval:id,name',
            'certification:id,name',
        ]), 201);
    }

    /* ============================================================
       UPDATE STATUS — admin updates status / remarks / schedule
       PATCH /cat2ExoPromotions/{cat2ExoPromotion}/status
    ============================================================ */
    public function updateStatus(Request $request, Cat2ExoPromotion $cat2ExoPromotion): JsonResponse
    {
        $validated = $request->validate([
            'status'       => ['required', Rule::in(Cat2ExoPromotion::STATUSES)],
            'remarks'      => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $cat2ExoPromotion->update($validated);

        return response()->json([
            'message' => 'Status updated successfully.',
            'data'    => $cat2ExoPromotion->fresh([
                'user:id,name,email',
                'assignmentOrders:id,name',
                'performanceEval:id,name',
                'certification:id,name',
            ]),
        ]);
    }

    /* ============================================================
       DESTROY — soft delete
       DELETE /cat2ExoPromotions/{cat2ExoPromotion}
    ============================================================ */
    public function destroy(Cat2ExoPromotion $cat2ExoPromotion): JsonResponse
    {
        if ($cat2ExoPromotion->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $cat2ExoPromotion->isPending()) {
            return response()->json([
                'message' => 'Only pending applications can be deleted.',
            ], 422);
        }

        $cat2ExoPromotion->delete();

        return response()->json(['message' => 'CAT 2 EXO Promotion application deleted.']);
    }

    /* ============================================================
       ADMIN INDEX — Inertia page + JSON for ajax refetch
       GET /cat2ExoPromotions/admin
    ============================================================ */
    public function adminIndex(Request $request): JsonResponse|InertiaResponse
    {
        $query = Cat2ExoPromotion::with([
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

        // Ajax refetch from Vue admin component
        if ($request->wantsJson()) {
            $paginated->getCollection()->transform(fn (Cat2ExoPromotion $record) => [
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

        return Inertia::render('Cat2ExoPromotion/AdminIndex', [
            'title'            => 'CAT 2 EXO Promotion — Admin',
            'filters'          => $request->only(['search', 'status']),
            'cat2ExoPromotions' => $paginated,
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