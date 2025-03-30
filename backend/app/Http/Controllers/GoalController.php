<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index()
    {
        $goals = Auth::user()->goals;
        return response()->json($goals);
    }

    // Crea un nuovo obiettivo
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'priority' => 'integer|min:0|max:2',
            'description' => 'nullable|string',
            'exp' => 'nullable|date',
        ]);
        

        $goal = Auth::user()->goals()->create($request->all());

        return response()->json($goal, 201);
    }

    // Mostra un obiettivo specifico
    public function show($id)
    {
        $goal = Auth::user()->goals()->find($id);
        
        if (!$goal) {
            return response()->json(['message' => 'Goal not found'], 404);
        }

        return response()->json($goal);
    }

    // Modifica un obiettivo
    public function update(Request $request, $id)
    {
        $goal = Auth::user()->goals()->find($id);

        if (!$goal) {
            return response()->json(['message' => 'Goal not found'], 404);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:100',
            'priority' => 'sometimes|integer|min:0|max:2',
            'description' => 'nullable|string',
            'exp' => 'nullable|date',
        ]);

        $goal->update($request->all());

        return response()->json($goal);
    }

    // Elimina un obiettivo
    public function destroy($id)
    {

        $goal = Auth::user()->goals()->find($id);

        if (!$goal) {
            return response()->json(['message' => 'Goal not found'], 404);
        }

        $goal->delete();

        return response()->json(['message' => 'Goal deleted successfully']);
    }
}
