<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Thread;
use Illuminate\Support\Facades\Validator;


class ThreadController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = 5;
            $page = $request->input('page', 1);

            $threads = Thread::with(['category', 'user'])
                ->orderBy('views_count', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

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
}
