<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Chirp;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
public function toggle(Chirp $chirp)
{
    $like = $chirp->likes()->where('user_id', auth()->id())->first();

    if ($like) {
        $like->delete();
        $liked = false;
    } else {
        $chirp->likes()->create([
            'user_id' => auth()->id()
        ]);
        $liked = true;
    }

    return response()->json([
        'likes' => $chirp->likes()->count(),
        'liked' => $liked
    ]);
}
}