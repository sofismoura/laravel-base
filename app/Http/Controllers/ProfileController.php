<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'photo' => 'nullable|image|max:2048'
        ]);

        $user = auth()->user();

        $user->name = $request->name;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $user->photo = $path;
        }

        $user->save();

        return redirect('/')->with('success', 'Perfil atualizado!');
    }

    // 🔥 NOVA FUNÇÃO
    public function destroy(Request $request)
    {
        $user = Auth::user();

        // 🧨 deleta dados relacionados (ajuda a evitar erro)
        $user->chirps()->delete();
        $user->comments()->delete();

        // 🔥 deleta a conta
        $user->delete();

        // 🚪 desloga
        Auth::logout();

        return redirect('/')->with('success', 'Conta deletada com sucesso');
    }
}