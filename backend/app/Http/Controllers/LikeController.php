<?php

namespace App\Http\Controllers;

// app/Http/Controllers/Api\LikeController.php

use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    // Like
    public function store(Request $request)
    {
        $request->validate([
            'likeable_type' => 'required|in:threads,posts',
            'likeable_id' => 'required|integer'
        ]);

        $like = Like::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'likeable_type' => $request->likeable_type,
                'likeable_id' => $request->likeable_id
            ],
            ['user_id' => $request->user()->id]
        );

        // Aggiorna contatore likes
        $likeable = $request->likeable_type::find($request->likeable_id);
        $likeable->likes_count = $likeable->likes()->count();
        $likeable->save();

        return response()->json(['success' => true]);
    }

    // Dislike
    public function destroy(Request $request)
    {
        $request->validate([
            'likeable_type' => 'required|in:threads,posts',
            'likeable_id' => 'required|integer'
        ]);

        $like = Like::where([
            'user_id' => $request->user()->id,
            'likeable_type' => $request->likeable_type,
            'likeable_id' => $request->likeable_id
        ])->first();

        if ($like) {
            $like->delete();
            
            // Aggiorna contatore likes
            $likeable = $request->likeable_type::find($request->likeable_id);
            $likeable->likes_count = $likeable->likes()->count();
            $likeable->save();
        }

        return response()->json(['success' => true]);
    }
}