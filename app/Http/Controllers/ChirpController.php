<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

class ChirpController extends Controller
{
    public function index()
    {
        return view('home', [
            'chirps' => Chirp::with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:20480',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('photos', 'public');
            $validated['image'] = $path;
        }

        $request->user()->chirps()->create($validated);

        return redirect(route('chirps.index'));
    }

    public function destroy(Chirp $chirp)
    {
        if ($chirp->user_id !== auth()->id()) {
            abort(403);
        }

        $chirp->delete();

        return redirect(route('chirps.index'));
    }
}