<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Chirp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Models\Notification;

// sobre
Route::get('/about', function () {
    return view('about');
})->name('about');

// perfil
Route::get('/profile/edit', [ProfileController::class, 'edit'])->middleware('auth');
Route::post('/profile/update', [ProfileController::class, 'update'])->middleware('auth');
Route::delete('/profile/delete', [ProfileController::class, 'destroy']);


//notificações
Route::get('/notifications', function () {
    $notifications = \App\Models\Notification::with('fromUser')
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

    return view('notifications', compact('notifications'));
})->middleware('auth');


Route::get('/notifications/{id}', function ($id) {

    $notification = \App\Models\Notification::where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    $notification->update(['read' => true]);

    //  se for comentário → vai pro comentário
    if ($notification->type === 'comment' && $notification->comment_id) {
        return redirect('/#comment-' . $notification->comment_id);
    }

    // se for like → vai pro post
    return redirect('/#chirp-' . $notification->chirp_id);

})->middleware('auth');

//comentário
Route::post('/chirps/{chirp}/comment', [CommentController::class, 'store'])->middleware('auth');

//like
Route::post('/chirps/{chirp}/like', [LikeController::class, 'toggle'])->middleware('auth');

// home
Route::get('/', function () {
    $chirps = Chirp::with(['user', 'likes', 'comments.user'])->latest()->get();
    return view('home', compact('chirps'));
})->name('chirps.index');

//cadastro
// abrir página
Route::get('/signup', function () {
    return view('signup');
});

// salvar usuário + foto
Route::post('/signup', function (Request $request) {

    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:4',
        'photo' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
    ]);

    $path = null;

    // salvar imagem
    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('photos', 'public');
    }

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'photo' => $path
    ]);

    // login automático
    Auth::login($user);

    return redirect('/');
});


// login
// abrir página
Route::get('/login', function () {
    return view('login');
});

// processar login
Route::post('/login', function (Request $request) {

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/');
    }

    return back()->withErrors([
        'email' => 'Email ou senha inválidos'
    ]);
});


// logout

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
});


// chirps

Route::post('/chirps', function (Request $request) {

    // precisa estar logado
    if (!auth()->check()) {
        return redirect('/login');
    }

    // validação atualizada para aceitar imagem
    $validated = $request->validate([
        'message' => 'required|max:255',
        'image' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:20480',
    ]);

    // lógica para salvar a imagem do post
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('photos', 'public');
        $validated['image'] = $path;
    }

    // cria o Chirp com os dados validados
    $request->user()->chirps()->create($validated);

    return redirect('/');
});

// excluir chirp
Route::delete('/chirps/{chirp}', [ChirpController::class, 'destroy'])->name('chirps.destroy')->middleware('auth');