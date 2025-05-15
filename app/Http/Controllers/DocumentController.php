<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\DocumentIndexRequest;
use App\Http\Requests\DocumentStoreRequest;
use App\Http\Requests\DocumentUpdateRequest;
use Spatie\Permission\Models\Permission;
use App\Models\Document;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct()
    {

        // $this->middleware('permission:create document', ['only' => ['create', 'store']]);
        $this->middleware('permission:read document', ['only' => ['index', 'show']]);
        $this->middleware('permission:update document', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete document', ['only' => ['destroy', 'destroyBulk']]);
    }
    
    public function index(DocumentIndexRequest $request)
    {
        $documents = Document::query();
        if ($request->has('search')) {
            $documents->where('name', 'LIKE', "%" . $request->search . "%");
            $documents->orWhere('status', 'LIKE', "%" . $request->search . "%");
        }

        if ($request->has(['field', 'order'])) {
            $documents->orderBy($request->field, $request->order);
        }
        
        return Inertia::render('Document/Index', [
            'title'         => "Document",
            'filters'       => $request->all(['search', 'field', 'order']),
            'documents'         => $documents->paginate(10),
        ]);
    }

    public function store(DocumentStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $name = $request->input('name');
            $user_id = $request->input('user_id');
            $status = $request->input('status');
    
            $path = $file->store('documents', 'public');
    
            $doc = Document::create([
                'user_id' => $user_id,
                'name' => $name,
                'path' => $path,
                'status' => $status,
            ]);
    
            DB::commit();
            return response()->json([
                'message' => 'Document uploaded successfully.',
                'document' => $doc,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Error creating document: ' . $th->getMessage(),
            ], 500);
        }
    }
    


    public function update(DocumentUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // Find the existing document by ID
            $doc = Document::findOrFail($id);

            if ($request->hasFile('file')) {
                if (Storage::exists('public/' . $doc->path)) {
                    Storage::delete('public/' . $doc->path);
                }

                $file = $request->file('file');
                $path = $file->store('documents', 'public');

                $doc->path = $path;
                $doc->name = $file->getClientOriginalName();
            }

            $doc->status = $request->input('status', $doc->status); 
            $doc->save();

            DB::commit();

            return back()->with('success', $doc->name. ' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error updating document: ' .  $th->getMessage());
        }
    }

    public function updateStatus(Request $request, Document $document)
{
    $request->validate([
        'status' => 'required|string|in:pending,approved,rejected', // adjust as needed
    ]);

    $document->status = $request->status;
    $document->save();

    return response()->json([
        'message' => 'Status updated successfully.',
        'status' => $document->status,
    ]);
}

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Find the document by ID
            $doc = Document::findOrFail($id);

            if (Storage::exists('public/' . $doc->path)) {
                Storage::delete('public/' . $doc->path);
            }

            $doc->delete();

            DB::commit();

            // Return success message
            return back()->with('success', 'Document deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error deleting document: ' .  $th->getMessage());
        }
    }
}
