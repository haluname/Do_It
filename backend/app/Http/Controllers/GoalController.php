<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index()
    {
        $goals = Auth::user()->goals()
            ->with(['tasks' => function($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->orderBy('exp', 'asc')
            ->orderBy('priority', 'desc')
            ->get();

        return response()->json($goals);
    }

    public function today()
    {
        $today = date('Y-m-d');

        $goals = Auth::user()->goals()
            ->whereDate('exp', $today)
            ->with(['tasks' => function($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->orderBy('priority', 'desc')
            ->orderBy('exp', 'asc')
            ->get();

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
        $goal = Auth::user()->goals()->with('tasks')->find($id);
        
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

  public function destroy($id)
{
    $goal = Auth::user()->goals()->find($id);

    if (!$goal) {
        return response()->json(['message' => 'Goal not found'], 404);
    }

    $isCompletion = request()->query('complete') === 'true';

    if ($isCompletion) {
        $baseXp = match($goal->priority) {
            2 => 100,
            1 => 50,
            0 => 25,
            default => 0,
        };

        $createdAt = $goal->created_at;
        $expDate = $goal->exp instanceof \Carbon\Carbon
            ? $goal->exp
            : \Carbon\Carbon::parse($goal->exp); // <-- questa riga ti salva in ogni caso

        $completedAt = now();

        $totalTimeAllotted = $expDate->diffInSeconds($createdAt);
        $multiplier = 0;

        if ($totalTimeAllotted > 0 && $completedAt->lte($expDate)) {
            $timeRemaining = $expDate->diffInSeconds($completedAt);
            $multiplier = $timeRemaining / $totalTimeAllotted;
        }

        $totalXp = round($baseXp * (1 + $multiplier));
        Auth::user()->addExperience($totalXp);
    }

    $goal->delete();
    return response()->json(['message' => 'Goal deleted successfully']);
}

    public function penalty(Request $request)
    {
        $user = Auth::user();
        
   
        
        // Nuova logica con scalata livelli
        $penalty = $request->penalty_points;
        $currentLevel = $user->level;
        $currentXp = $user->experience;

        $totalXp = $currentXp;
        for ($l = 1; $l < $currentLevel; $l++) {
            $totalXp += pow($l * 30, 1.5);
        }

        $totalXp -= $penalty;

        if ($totalXp < 0) {
            $user->level = 1;
            $user->experience = 0;
        } else {
            $newLevel = 1;
            while ($totalXp >= pow($newLevel * 30, 1.5)) {
                $totalXp -= pow($newLevel * 30, 1.5);
                $newLevel++;
            }
            
            $user->level = $newLevel;
            $user->experience = $totalXp;
        }

        $user->save();

        Goal::whereIn('id', $request->goal_ids)
            ->update(['penalty_applied' => true]);

        return response()->json(['message' => 'Penalties applied']);
    }
}
