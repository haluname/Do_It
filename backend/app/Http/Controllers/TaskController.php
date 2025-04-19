<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::whereHas('goal', function ($query) {
            $query->where('user_id', Auth::id());
        })->with('goal')->get();

        return response()->json($tasks);
    }

    // Crea un nuovo task
    public function store(Request $request)
    {
        $request->validate([
            'goal_id' => 'required|exists:goals,id',
            'title' => 'required|string|max:10000',
            'description' => 'nullable|string',
            'exp' => 'required|date',
        ]);

        $goal = Auth::user()->goals()->find($request->goal_id);

        if (!$goal) {
            return response()->json(['message' => 'Unauthorized goal access'], 403);
        }

        $task = $goal->tasks()->create($request->only(['title', 'description', 'exp']));

        return response()->json($task, 201);
    }

    public function show($id)
    {
        $task = Task::with('goal')->find($id);

        if (!$task || $task->goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::with('goal')->find($id);

        if (!$task || $task->goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Task not found or unauthorized'], 404);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:10000',
            'description' => 'nullable|string',
            'exp' => 'sometimes|required|date',
        ]);

        $task->update($request->only(['title', 'description', 'exp']));

        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::with('goal')->find($id);

        if (!$task || $task->goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Task not found or unauthorized'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
