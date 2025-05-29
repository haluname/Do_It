<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Database\Eloquent\Relations\Relation;

class LikeController extends Controller
{
    private $morphMap = [
        'posts' => 'App\Models\Post',
        'threads' => 'App\Models\Thread'
    ];

    public function store(Request $request)
    {
        $request->validate([
            'likeable_type' => 'required|in:posts,threads',
            'likeable_id' => 'required|integer'
        ]);

        // Converti il tipo nel nome completo della classe
        $class = $this->morphMap[$request->likeable_type] ?? null;
        
        if (!$class) {
            return response()->json(['error' => 'Invalid likeable type'], 400);
        }

        $like = Like::create([
            'user_id' => $request->user()->id,
            'likeable_type' => $class,
            'likeable_id' => $request->likeable_id
        ]);

        // Aggiorna il contatore
        $likeable = $class::find($request->likeable_id);
        if ($likeable) {
            $likeable->likes_count = $likeable->likes()->count();
            $likeable->save();
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'likeable_type' => 'required|in:posts,threads',
            'likeable_id' => 'required|integer'
        ]);

        // Converti il tipo nel nome completo della classe
        $class = $this->morphMap[$request->likeable_type] ?? null;
        
        if (!$class) {
            return response()->json(['error' => 'Invalid likeable type'], 400);
        }

        $like = Like::where([
            'user_id' => $request->user()->id,
            'likeable_type' => $class,
            'likeable_id' => $request->likeable_id
        ])->first();

        if ($like) {
            $like->delete();
            
            // Aggiorna il contatore
            $likeable = $class::find($request->likeable_id);
            if ($likeable) {
                $likeable->likes_count = $likeable->likes()->count();
                $likeable->save();
            }
        }

        return response()->json(['success' => true]);
    }
}