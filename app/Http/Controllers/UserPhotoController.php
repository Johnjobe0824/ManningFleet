<?php

namespace App\Http\Controllers;

use App\Models\UserPhoto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UserPhotoIndexRequest;
use App\Http\Requests\UserPhotoStoreRequest;
use App\Http\Requests\UserPhotoUpdateRequest;
use Spatie\Permission\Models\Permission;

class UserPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserPhotoStoreRequest $request)
{
    DB::beginTransaction();
    
    try {
        // Check if the user already has a photo
        $existingPhoto = UserPhoto::where('user_id', $request->user_id)->first();

        // If the user already has a photo, delete the old one from storage
        if ($existingPhoto) {
            $oldPhotoPath = public_path('storage/' . $existingPhoto->photo_path);
            if (file_exists($oldPhotoPath)) {
                unlink($oldPhotoPath);  // Delete the old photo
            }

            // Update the existing photo record
            $existingPhoto->update([
                'photo_path' => $request->file('photo_path')->store('user_photos', 'public')
            ]);
            $userPhoto = $existingPhoto;  // Set the updated photo record
        } else {
            // If no existing photo, create a new one
            $path = $request->file('photo_path')->store('user_photos', 'public');
            $userPhoto = UserPhoto::create([
                'user_id' => $request->user_id,
                'photo_path' => $path
            ]);
        }

        // Commit the transaction
        DB::commit();

        // Return a success message
        return back()->with('success', 'Photo ' . ($existingPhoto ? 'updated' : 'created') . ' successfully.');

    } catch (\Throwable $th) {
        // Rollback in case of any errors
        DB::rollback();
        return back()->with('error', 'Error: ' . $th->getMessage());
    }
}


    /**
     * Display the specified resource.
     */
    public function show(UserPhoto $userPhoto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserPhoto $userPhoto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserPhotoUpdateRequest $request, UserPhoto $userPhoto)
    {
        DB::beginTransaction();
        try {
            $updateData = [
                'user_id' => $request->user_id,
                'level' => $userPhoto->id
            ];

            if ($request->hasFile('photo_path')) {
                $updateData['photo_path'] = $request->file('photo_path')->store('user_photos', 'public');
            }

            $userPhoto->update($updateData);

            DB::commit();
            return back()->with('success', $userPhoto->id . ' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error updating: ' . $th->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserPhoto $userPhoto)
    {
        //
    }
}
