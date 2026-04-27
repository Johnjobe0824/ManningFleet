<?php

namespace App\Http\Controllers;


use App\Models\ExoDeliberation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ExoDeliberationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function __construct()
    {
        $this->middleware('permission:read exo_deliberation',   ['only' => ['index', 'show', 'fetch']]);
        $this->middleware('permission:create exo_deliberation', ['only' => ['store']]);
        $this->middleware('permission:update exo_deliberation', ['only' => ['update']]);
        $this->middleware('permission:delete exo_deliberation', ['only' => ['destroy']]);
    }



    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        return response()->json($this->dropdownData($userId));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'page'     => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search'   => 'nullable|string|max:255',
        ]);

        $query = ExoDeliberation::with([
                'juniorBilletCompletion:id,name',
                'slmcEndorsement:id,name',
                'slmcOrders:id,name',
                'slmcGraduationCert:id,name',
            ])
            ->forUser($request->user_id);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('status',  'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('juniorBilletCompletion', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('slmcEndorsement',        fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('slmcOrders',             fn ($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('slmcGraduationCert',     fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $paginated = $query->latest()->paginate($request->per_page ?? 10);

        // Re-key relations to match Vue column bindings
        $paginated->getCollection()->transform(fn (ExoDeliberation $record) => [
            'id'                       => $record->id,
            'status'                   => $record->status,
            'remarks'                  => $record->remarks,
            'junior_billet_completion' => $record->juniorBilletCompletion,
            'slmc_endorsement'         => $record->slmcEndorsement,
            'slmc_orders'              => $record->slmcOrders,
            'slmc_graduation_cert'     => $record->slmcGraduationCert,
            'created_at'               => $record->created_at,
        ]);

        return response()->json($paginated);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'user_id'                     => ['required', 'integer', Rule::in([$userId])],
            'junior_billet_completion_id' => ['required', 'exists:certifications,id'],
            'slmc_endorsement_id'         => ['required', 'exists:certifications,id'],
            'slmc_orders_id'              => ['required', 'exists:certifications,id'],
            'slmc_graduation_cert_id'     => ['required', 'exists:certifications,id'],
        ]);

        // Prevent duplicate pending application
        $existing = ExoDeliberation::forUser($userId)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending EXO Deliberation application. Please wait for the board decision before submitting a new one.',
            ], 422);
        }

        $record = ExoDeliberation::create($validated + ['status' => 'pending']);

        return response()->json($record->load([
            'juniorBilletCompletion',
            'slmcEndorsement',
            'slmcOrders',
            'slmcGraduationCert',
        ]), 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(ExoDeliberation $exoDeliberation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExoDeliberation $exoDeliberation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   // ExoDeliberationController.php
public function updateStatus(Request $request, ExoDeliberation $exoDeliberation)
{
    $validated = $request->validate([
        'status'       => 'required|in:pending,approved,rejected,scheduled',
        'remarks'      => 'nullable|string',
        'scheduled_at' => 'nullable|date',
    ]);

    $exoDeliberation->update($validated);

    return response()->json(['message' => 'Status updated successfully.']);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExoDeliberation $exoDeliberation): JsonResponse
    {
        if ($exoDeliberation->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $exoDeliberation->isPending()) {
            return response()->json([
                'message' => 'Only pending applications can be deleted.',
            ], 422);
        }

        $exoDeliberation->delete();

        return response()->json(['message' => 'EXO Deliberation application deleted.']);
    }



public function adminIndex(Request $request): InertiaResponse
{
    $query = ExoDeliberation::with([
            'user:id,name,email',
            'juniorBilletCompletion:id,name',
            'slmcEndorsement:id,name',
            'slmcOrders:id,name',
            'slmcGraduationCert:id,name',
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

    return Inertia::render('ExoDeliberation/AdminIndex', [
        'title'             => 'EXO Deliberation — Admin',
        'filters'           => $request->only(['search', 'status']),
        'exoDeliberations'  => $query->paginate(10)->withQueryString(),
    ]);
}

 private function dropdownData(int $userId): array
    {
        return [
            'juniorBilletCompletions' => DB::table('certifications')
                                    ->where('user_id', $userId)
                                    ->select('id', 'name')
                                    ->get(),

            'slmcEndorsements'    => DB::table('certifications')
                                    ->where('user_id', $userId)
                                    ->select('id', 'name')
                                    ->get(),

            'slmcOrders'  => DB::table('certifications')
                                    ->where('user_id', $userId)
                                    ->select('id', 'name')
                                    ->get(),

            'slmcGraduationCerts' => DB::table('certifications')
                                        ->where('user_id', $userId)
                                        ->select('id', 'name')
                                        ->get(),
        ];
    }
}
