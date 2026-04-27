<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billet;
use Inertia\Inertia;

class BilletController extends Controller
{
    public function index()
    {
        $billets = Billet::latest()->get();

        // If request expects JSON (Axios), return JSON
        if (request()->expectsJson()) {
            return response()->json([
                'billets' => $billets,
            ]);
        }

        // Otherwise render Inertia page
        return Inertia::render('Billet/Index', [
            'billets' => $billets,
        ]);
    }

     public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $path = $request->file('file')->store('billets', 'public');

        $billet = Billet::create([
            'name'      => $request->name,
            'file_path' => $path,
            'user_id'   => auth()->id(), // optional
        ]);

        return response()->json([
            'billets' => $billet,
        ], 201);
    }

    public function destroy(Billet $billet)
    {
        \Storage::disk('public')->delete($billet->file_path);
        $billet->delete();

        return response()->noContent();
    }
}
