<?php

namespace App\Http\Controllers;

use App\Models\ExoIcasCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ExoIcasCompletionController extends Controller
{
    /* ============================================================
       CONSTRUCTOR — permission middleware
    ============================================================ */
    public function __construct()
    {
        $this->middleware('permission:read exo_icas',   ['only' => ['index', 'fetch', 'adminIndex']]);
        $this->middleware('permission:create exo_icas', ['only' => ['store']]);
        $this->middleware('permission:update exo_icas', ['only' => ['updateStatus']]);
        $this->middleware('permission:delete exo_icas', ['only' => ['destroy']]);
    }

    /* ============================================================
       INDEX — dropdown data for the form
       GET /exoIcasCompletions
    ============================================================ */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        return response()->json($this->dropdownData($userId));
    }

    /* ============================================================
       FETCH — paginated table data (user-facing)
       GET /exoIcasCompletions/fetch
    ============================================================ */
    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'page'     => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search'   => 'nullable|string|max:255',
        ]);

        $query = ExoIcasCompletion::with([
                'seaDutyRecord:id,name',
                'icasExamProof:id,name',
                'supportingDoc:id,name',
            ])
            ->forUser($request->user_id);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('status',  'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('seaDutyRecord', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('icasExamProof', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('supportingDoc', fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $paginated = $query->latest()->paginate($request->per_page ?? 10);

        $paginated->getCollection()->transform(fn (ExoIcasCompletion $record) => [
            'id'                         => $record->id,
            'status'                     => $record->status,
            'remarks'                    => $record->remarks,
            'scheduled_at'               => $record->scheduled_at,
            'sea_duty_record'            => $record->seaDutyRecord,
            'icas_exam_proof'            => $record->icasExamProof,
            'supporting_doc'             => $record->supportingDoc,
            'created_at'                 => $record->created_at,
            'checklist_sea_duty_records' => $record->checklist_sea_duty_records,
            'checklist_min_residency'    => $record->checklist_min_residency,
            'checklist_steaming_hours'   => $record->checklist_steaming_hours,
            'checklist_icas_exam'        => $record->checklist_icas_exam,
        ]);

        return response()->json($paginated);
    }

    /* ============================================================
       STORE — submit new completion request
       POST /exoIcasCompletions
    ============================================================ */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'user_id'                    => ['required', 'integer', Rule::in([$userId])],
            'sea_duty_record_id'         => ['required', 'exists:documents,id'],
            'icas_exam_proof_id'         => ['required', 'exists:documents,id'],
            'supporting_doc_id'          => ['nullable', 'exists:documents,id'],

            // Step 1 checklist flags
            'checklist_sea_duty_records' => ['boolean'],
            'checklist_min_residency'    => ['boolean'],
            'checklist_steaming_hours'   => ['boolean'],
            'checklist_icas_exam'        => ['boolean'],
        ]);

        // Prevent duplicate pending requests
        $existing = ExoIcasCompletion::forUser($userId)
            ->where('status', ExoIcasCompletion::STATUS_PENDING)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending EXO Billet Completion & ICAS Award request. Please wait for it to be processed before submitting a new one.',
            ], 422);
        }

        $record = ExoIcasCompletion::create(array_merge($validated, [
            'status'                     => ExoIcasCompletion::STATUS_PENDING,
            'checklist_sea_duty_records' => $validated['checklist_sea_duty_records'] ?? true,
            'checklist_min_residency'    => $validated['checklist_min_residency']    ?? true,
            'checklist_steaming_hours'   => $validated['checklist_steaming_hours']   ?? true,
            'checklist_icas_exam'        => $validated['checklist_icas_exam']         ?? true,
        ]));

        return response()->json($record->load([
            'seaDutyRecord:id,name',
            'icasExamProof:id,name',
            'supportingDoc:id,name',
        ]), 201);
    }

    /* ============================================================
       UPDATE STATUS — admin updates status / remarks / schedule
       PATCH /exoIcasCompletions/{exoIcasCompletion}/status
    ============================================================ */
    public function updateStatus(Request $request, ExoIcasCompletion $exoIcasCompletion): JsonResponse
    {
        $validated = $request->validate([
            'status'       => ['required', Rule::in(ExoIcasCompletion::STATUSES)],
            'remarks'      => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $exoIcasCompletion->update($validated);

        return response()->json([
            'message' => 'Status updated successfully.',
            'data'    => $exoIcasCompletion->fresh([
                'user:id,name,email',
                'seaDutyRecord:id,name',
                'icasExamProof:id,name',
                'supportingDoc:id,name',
            ]),
        ]);
    }

    /* ============================================================
       DESTROY — soft delete
       DELETE /exoIcasCompletions/{exoIcasCompletion}
    ============================================================ */
    public function destroy(ExoIcasCompletion $exoIcasCompletion): JsonResponse
    {
        if ($exoIcasCompletion->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $exoIcasCompletion->isPending()) {
            return response()->json([
                'message' => 'Only pending requests can be deleted.',
            ], 422);
        }

        $exoIcasCompletion->delete();

        return response()->json(['message' => 'EXO Billet Completion & ICAS Award request deleted.']);
    }

    /* ============================================================
       ADMIN INDEX — Inertia page + JSON for ajax refetch
       GET /exoIcasCompletions/admin
    ============================================================ */
    public function adminIndex(Request $request): JsonResponse|InertiaResponse
    {
        $query = ExoIcasCompletion::with([
                'user:id,name,email',
                'seaDutyRecord:id,name',
                'icasExamProof:id,name',
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
            $paginated->getCollection()->transform(fn (ExoIcasCompletion $record) => [
                'id'                         => $record->id,
                'status'                     => $record->status,
                'remarks'                    => $record->remarks,
                'scheduled_at'               => $record->scheduled_at,
                'user'                       => $record->user,
                'sea_duty_record'            => $record->seaDutyRecord,
                'icas_exam_proof'            => $record->icasExamProof,
                'supporting_doc'             => $record->supportingDoc,
                'created_at'                 => $record->created_at,
                // Checklist flags — needed by the admin expanded row
                'checklist_sea_duty_records' => $record->checklist_sea_duty_records,
                'checklist_min_residency'    => $record->checklist_min_residency,
                'checklist_steaming_hours'   => $record->checklist_steaming_hours,
                'checklist_icas_exam'        => $record->checklist_icas_exam,
            ]);

            return response()->json($paginated);
        }

        return Inertia::render('ExoIcascompletion/ExoIcasAdmin', [
            'title'              => 'EXO Billet Completion & ICAS Award — Admin',
            'filters'            => $request->only(['search', 'status']),
            'exoIcasCompletions' => $paginated,
        ]);
    }

    /* ============================================================
       PRIVATE HELPERS
    ============================================================ */
    private function dropdownData(int $userId): array
    {
        return [
            'seaDutyRecords' => DB::table('documents')
                ->where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'icasExamProofs' => DB::table('documents')
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