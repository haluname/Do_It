<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Post;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|exists:threads,id',
            'parent_id' => 'nullable|exists:posts,id', 
            'content' => 'required|string|max:1000'
        ]);

        $post = Post::create([
            'thread_id' => $request->thread_id,
            'parent_id' => $request->parent_id, 
            'user_id' => $request->user()->id,
            'content' => $request->content
        ]);

        $thread = $post->thread;
        $thread->replies_count = $thread->posts()->count(); 
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

    public function show($id)
    {
        $post = Post::with('user', 'thread')->findOrFail($id);

        return response()->json([
            'id' => $post->id,
            'content' => $post->content,
            'created_at' => $post->created_at,
            'user' => [
                'name' => $post->user->name,
                'id' => $post->user->id
            ],
            'thread' => [
                'id' => $post->thread->id,
                'title' => $post->thread->title
            ]
        ]);
    }
}
