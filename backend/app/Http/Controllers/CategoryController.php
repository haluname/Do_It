<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('order')
                ->get(['id', 'name', 'color']);

            return response()->json([
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'color' => 'required|string|size:7|starts_with:#',
            'order' => 'nullable|integer',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'color' => $request->color,
            'order' => $request->order ?? Category::max('order') + 1,
            'is_active' => true,
            'slug' => \Illuminate\Support\Str::slug($request->name)
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }
}
