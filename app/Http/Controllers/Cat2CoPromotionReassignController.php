<?php

namespace App\Http\Controllers;

use App\Models\Cat2CoPromotionReassign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class Cat2CoPromotionReassignController extends Controller
{
    /* ============================================================
       CONSTRUCTOR — permission middleware
    ============================================================ */
    // public function __construct()
    // {
    //     $this->middleware('permission:read cat2_co_promotion_reassigns',   ['only' => ['index', 'fetch', 'adminIndex']]);
    //     $this->middleware('permission:create cat2_co_promotion_reassigns', ['only' => ['store']]);
    //     $this->middleware('permission:update cat2_co_promotion_reassigns', ['only' => ['updateStatus']]);
    //     $this->middleware('permission:delete cat2_co_promotion_reassigns', ['only' => ['destroy']]);
    // }

    /* ============================================================
       INDEX — dropdown data for the form
       GET /cat2CoPromotionReassigns
    ============================================================ */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        return response()->json($this->dropdownData($userId));
    }

    /* ============================================================
       FETCH — paginated table data (user-facing)
       GET /cat2CoPromotionReassigns/fetch
    ============================================================ */
    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'page'     => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search'   => 'nullable|string|max:255',
        ]);

        $query = Cat2CoPromotionReassign::with([
                'icasAwardOrder:id,name',
                'seaDutyRecord:id,name',
                'performanceEval:id,name',
                'medicalCertificate:id,name',
                'supportingDoc:id,name',
            ])
            ->forUser($request->user_id);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('status',  'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('icasAwardOrder',    fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('seaDutyRecord',     fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('performanceEval',   fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('medicalCertificate',fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('supportingDoc',     fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $paginated = $query->latest()->paginate($request->per_page ?? 10);

        $paginated->getCollection()->transform(fn (Cat2CoPromotionReassign $record) => [
            'id'                           => $record->id,
            'status'                       => $record->status,
            'remarks'                      => $record->remarks,
            'scheduled_at'                 => $record->scheduled_at,
            'icas_award_order'             => $record->icasAwardOrder,
            'sea_duty_record'              => $record->seaDutyRecord,
            'performance_eval'             => $record->performanceEval,
            'medical_certificate'          => $record->medicalCertificate,
            'supporting_doc'               => $record->supportingDoc,
            'created_at'                   => $record->created_at,
            'checklist_icas_award_orders'  => $record->checklist_icas_award_orders,
            'checklist_sea_duty_compliance'=> $record->checklist_sea_duty_compliance,
            'checklist_performance_eval'   => $record->checklist_performance_eval,
            'checklist_medical_fitness'    => $record->checklist_medical_fitness,
        ]);

        return response()->json($paginated);
    }

    /* ============================================================
       STORE — submit new CAT 2 CO Assignment request
       POST /cat2CoPromotionReassigns
    ============================================================ */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'user_id'                      => ['required', 'integer', Rule::in([$userId])],
            'icas_award_order_id'          => ['required', 'exists:documents,id'],
            'sea_duty_record_id'           => ['required', 'exists:documents,id'],
            'performance_eval_id'          => ['required', 'exists:documents,id'],
            'medical_certificate_id'       => ['required', 'exists:documents,id'],
            'supporting_doc_id'            => ['nullable', 'exists:documents,id'],

            // Step 1 checklist flags
            'checklist_icas_award_orders'  => ['boolean'],
            'checklist_sea_duty_compliance'=> ['boolean'],
            'checklist_performance_eval'   => ['boolean'],
            'checklist_medical_fitness'    => ['boolean'],
        ]);

        // Prevent duplicate pending requests
        $existing = Cat2CoPromotionReassign::forUser($userId)
            ->where('status', Cat2CoPromotionReassign::STATUS_PENDING)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending Category 2 CO Promotion Reassign request. Please wait for it to be processed before submitting a new one.',
            ], 422);
        }

        $record = Cat2CoPromotionReassign::create(array_merge($validated, [
            'status'                       => Cat2CoPromotionReassign::STATUS_PENDING,
            'checklist_icas_award_orders'  => $validated['checklist_icas_award_orders']   ?? true,
            'checklist_sea_duty_compliance'=> $validated['checklist_sea_duty_compliance'] ?? true,
            'checklist_performance_eval'   => $validated['checklist_performance_eval']    ?? true,
            'checklist_medical_fitness'    => $validated['checklist_medical_fitness']     ?? true,
        ]));

        return response()->json($record->load([
            'icasAwardOrder:id,name',
            'seaDutyRecord:id,name',
            'performanceEval:id,name',
            'medicalCertificate:id,name',
            'supportingDoc:id,name',
        ]), 201);
    }

    /* ============================================================
       UPDATE STATUS — admin updates status / remarks / schedule
       PATCH /cat2CoPromotionReassigns/{cat2CoPromotionReassign}/status
    ============================================================ */
    public function updateStatus(Request $request, Cat2CoPromotionReassign $cat2CoPromotionReassign): JsonResponse
    {
        $validated = $request->validate([
            'status'       => ['required', Rule::in(Cat2CoPromotionReassign::STATUSES)],
            'remarks'      => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $cat2CoPromotionReassign->update($validated);

        return response()->json([
            'message' => 'Status updated successfully.',
            'data'    => $cat2CoPromotionReassign->fresh([
                'user:id,name,email',
                'icasAwardOrder:id,name',
                'seaDutyRecord:id,name',
                'performanceEval:id,name',
                'medicalCertificate:id,name',
                'supportingDoc:id,name',
            ]),
        ]);
    }

    /* ============================================================
       DESTROY — soft delete
       DELETE /cat2CoPromotionReassigns/{cat2CoPromotionReassign}
    ============================================================ */
    public function destroy(Cat2CoPromotionReassign $cat2CoPromotionReassign): JsonResponse
    {
        if ($cat2CoPromotionReassign->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $cat2CoPromotionReassign->isPending()) {
            return response()->json([
                'message' => 'Only pending requests can be deleted.',
            ], 422);
        }

        $cat2CoPromotionReassign->delete();

        return response()->json(['message' => 'Category 2 CO Promotion Reassign request deleted.']);
    }

    /* ============================================================
       ADMIN INDEX — Inertia page + JSON for ajax refetch
       GET /cat2CoPromotionReassigns/admin
    ============================================================ */
    public function adminIndex(Request $request): JsonResponse|InertiaResponse
    {
        $query = Cat2CoPromotionReassign::with([
                'user:id,name,email',
                'icasAwardOrder:id,name',
                'seaDutyRecord:id,name',
                'performanceEval:id,name',
                'medicalCertificate:id,name',
                'supportingDoc:id,name',
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
            $paginated->getCollection()->transform(fn (Cat2CoPromotionReassign $record) => [
                'id'                           => $record->id,
                'status'                       => $record->status,
                'remarks'                      => $record->remarks,
                'scheduled_at'                 => $record->scheduled_at,
                'user'                         => $record->user,
                'icas_award_order'             => $record->icasAwardOrder,
                'sea_duty_record'              => $record->seaDutyRecord,
                'performance_eval'             => $record->performanceEval,
                'medical_certificate'          => $record->medicalCertificate,
                'supporting_doc'               => $record->supportingDoc,
                'created_at'                   => $record->created_at,
                // Checklist flags — needed by the admin expanded row
                'checklist_icas_award_orders'  => $record->checklist_icas_award_orders,
                'checklist_sea_duty_compliance'=> $record->checklist_sea_duty_compliance,
                'checklist_performance_eval'   => $record->checklist_performance_eval,
                'checklist_medical_fitness'    => $record->checklist_medical_fitness,
            ]);

            return response()->json($paginated);
        }

        return Inertia::render('Category2Co/Cat2CoAssignmentAdmin', [
            'title'              => 'Category 2 CO Promotion Reassign — Admin',
            'filters'            => $request->only(['search', 'status']),
            'cat2CoPromotionReassigns'  => $paginated,
        ]);
    }

    /* ============================================================
       PRIVATE HELPERS
    ============================================================ */
    private function dropdownData(int $userId): array
    {
        return [
            'icasAwardOrders' => DB::table('documents')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'seaDutyRecords' => DB::table('documents')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'performanceEvals' => DB::table('documents')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'medicalCertificates' => DB::table('documents')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'supportingDocs' => DB::table('documents')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ];
    }
}