<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class ThreadController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = 5;
            $page = $request->input('page', 1);

            $query = Thread::query()->with(['category', 'user'])
                ->orderBy('closed', 'asc')
                ->orderBy('pinned', 'desc')
                ->orderBy('created_at', 'desc') // Modificato per ordinare per data
                ->orderBy('views_count', 'desc'); // Modificato per ordinare per data


            // Filtro per categoria
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filtro per testo di ricerca
            if ($request->has('query')) {
                $searchTerm = $request->input('query');
                $query->where('title', 'like', "%{$searchTerm}%");
            }

            $threads = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $threads->items(),
                'meta' => [
                    'current_page' => $threads->currentPage(),
                    'last_page' => $threads->lastPage(),
                    'total' => $threads->total()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching threads',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:120',
            'content' => 'required|string|max:1000',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $thread = $request->user()->threads()->create([
                'title' => $request->title,
                'content' => $request->content,
                'category_id' => $request->category_id,
                'slug' => \Illuminate\Support\Str::slug($request->title)
            ]);

            return response()->json([
                'message' => 'Thread created successfully',
                'data' => $thread->load('category', 'user')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating thread',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $thread = Thread::with(['user', 'category', 'posts.user'])->findOrFail($id);

        // Incrementa visualizzazioni
        $thread->increment('views_count');

        return response()->json([
            'id' => $thread->id,
            'title' => $thread->title,
            'content' => $thread->content,
            'created_at' => $thread->created_at,
            'pinned' => $thread->pinned,
            'closed' => $thread->closed,
            'replies_count' => $thread->replies_count,
            'views_count' => $thread->views_count,
            'user' => [
                'name' => $thread->user->name,
                'id' => $thread->user->id
            ],
            'category' => [
                'name' => $thread->category->name,
                'color' => $thread->category->color
            ]
        ]);
    }

    public function getPosts(Request $request, $threadId)
    {
        $thread = Thread::findOrFail($threadId);

        // Carica i post principali con le risposte annidate
        $posts = $thread->posts()
            ->with(['user', 'replies.user'])
            ->withCount('likes')
            ->whereNull('parent_id')
            ->paginate(10);

        // Formatta la risposta con struttura annidata
        $formattedPosts = $posts->map(function ($post) use ($request) {
            return $this->formatPost($post, $request);
        });

        return response()->json([
            'data' => $formattedPosts,
            'total' => $posts->total(),
            'per_page' => $posts->perPage(),
            'current_page' => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
        ]);
    }

    private function formatPost($post, $request)
    {
        $likedByUser = false;
        if ($request->user()) {
            $likedByUser = $post->likes()->where('user_id', $request->user()->id)->exists();
        }

        $replies = $post->replies->map(function ($reply) use ($request) {
            return $this->formatPost($reply, $request);
        });

        return [
            'id' => $post->id,
            'content' => $post->content,
            'created_at' => $post->created_at,
            'likes_count' => $post->likes_count,
            'liked_by_user' => $likedByUser,
            'user_id' => $post->user_id,
            'user' => [
                'name' => $post->user->name,
                'id' => $post->user->id
            ],
            'replies' => $replies
        ];
    }

    // Toggle pin
    public function updatePin(Request $request, $id)
    {
        $thread = Thread::findOrFail($id);
        $thread->pinned = !$thread->pinned;
        $thread->save();

        return response()->json(['pinned' => $thread->pinned]);
    }

    // Toggle close
    public function updateClose(Request $request, $id)
    {
        $thread = Thread::findOrFail($id);
        $thread->closed = !$thread->closed;
        $thread->save();

        return response()->json(['closed' => $thread->closed]);
    }

    public function search(Request $request)
    {
        try {
            $query = Thread::query()->with(['category', 'user']);

            // Filtro per testo di ricerca
            if ($request->has('query')) {
                $searchTerm = $request->input('query');
                $query->where('title', 'like', "%{$searchTerm}%");
            }

            // Filtro per categoria
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $perPage = 5;
            $page = $request->input('page', 1);
            $threads = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $threads->items(),
                'meta' => [
                    'current_page' => $threads->currentPage(),
                    'last_page' => $threads->lastPage(),
                    'total' => $threads->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error searching threads',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $thread = Thread::with(['user', 'category'])->findOrFail($id);


            $thread->delete();

            return response()->json([
                'message' => 'Thread eliminato con successo',
                'thread_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione del thread',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stats(Request $request)
    {
        return response()->json([
            'total_threads' => Thread::count(),
            'total_users' => User::count(),
            'top_users' => User::orderByDesc('experience')
                ->limit(3)
                ->get(['name', 'experience', 'level']),
        ]);
    }
}
