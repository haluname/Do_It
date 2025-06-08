<?php

namespace App\Http\Controllers;

use App\Models\Solution;
use App\Models\SolutionLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolutionController extends Controller
{
    public function index(Request $request)
    {
        $query = Solution::with(['user', 'likes'])
            ->withCount('likes')
            ->withExists(['likedByUser as liked_by_user' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);

        // Filtro di ricerca
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('problem', 'like', '%' . $request->search . '%')
                    ->orWhere('solution', 'like', '%' . $request->search . '%');
            });
        }

        // Ordinamento
        switch ($request->get('sort', 'most_liked')) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('likes_count', 'desc');
                break;
        }

        // Paginazione
        $perPage = $request->get('per_page', 5);
        $solutions = $query->paginate($perPage);

        return response()->json([
            'data' => $solutions->items(),
            'meta' => [
                'current_page' => $solutions->currentPage(),
                'last_page' => $solutions->lastPage(),
                'per_page' => $solutions->perPage(),
                'total' => $solutions->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'problem' => 'required|string',
            'solution' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = auth()->id();
        $existingPosts = Solution::where('user_id', $userId)->count();
        $totalLikes = Solution::where('user_id', $userId)->sum('likes_count');
        $requiredLikes = $existingPosts * 10;

        if ($totalLikes < $requiredLikes) {
            return response()->json([
                'error' => 'Non hai abbastanza like per pubblicare una nuova soluzione.'
            ], 403);
        }

        $solution = Solution::create([
            'user_id' => $userId,
            'title' => $request->title,
            'problem' => $request->problem,
            'solution' => $request->solution,
        ]);

        return response()->json($solution, 201);
    }

    public function toggleLike(Solution $solution)
    {
        $user = auth()->user();

        $like = SolutionLike::where('user_id', $user->id)
            ->where('solution_id', $solution->id)
            ->first();

        if ($like) {
            $like->delete();
            $solution->decrement('likes_count');
            $liked = false;
        } else {
            SolutionLike::create([
                'user_id' => $user->id,
                'solution_id' => $solution->id,
            ]);
            $solution->increment('likes_count');
            $liked = true;
        }


        $solution->loadCount('likes');
        $likesCount = $solution->likes_count;

        return response()->json([
            'likes_count' => $likesCount,
            'liked' => $liked
        ]);
    }
}
