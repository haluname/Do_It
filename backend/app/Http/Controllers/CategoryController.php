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
}
