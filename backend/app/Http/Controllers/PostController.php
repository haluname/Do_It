<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// app/Http/Controllers/Api\PostController.php

use App\Models\Post;

class PostController extends Controller
{
    // Creazione risposta
        public function store(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|exists:threads,id',
            'parent_id' => 'nullable|exists:posts,id', // Valida la risposta annidata
            'content' => 'required|string|max:1000'
        ]);

        $post = Post::create([
            'thread_id' => $request->thread_id,
            'parent_id' => $request->parent_id, // Può essere null per post principali
            'user_id' => $request->user()->id,
            'content' => $request->content
        ]);

        // Aggiorna contatore risposte nel thread
        $thread = $post->thread;
        $thread->replies_count = $thread->posts()->count(); // Conta tutti i post (principali + risposte)
        $thread->save();

        return response()->json([
            'id' => $post->id,
            'content' => $post->content,
            'created_at' => $post->created_at,
            'user' => [
                'name' => $request->user()->name
            ]
        ], 201);
    }
}