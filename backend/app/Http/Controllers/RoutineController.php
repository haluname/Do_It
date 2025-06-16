<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Routine;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\RoutineReminder;
use App\Mail\RoutineMissed;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RoutineController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $routines = Routine::where('user_id', $user->id)->get();

        return response()->json($routines);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,every_2_days,weekly,monthly',
            'start_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $dueDate = Carbon::parse($request->start_date)->setTime(23, 59, 0);
        $startDate = Carbon::parse($request->start_date);

        $routine = Routine::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'frequency' => $request->frequency,
            'start_date' => $startDate,
            'next_due' => $dueDate,
            'completions' => json_encode([])
        ]);

        return response()->json($routine, 201);
    }

    public function destroy($id)
    {
        $routine = Routine::where('user_id', Auth::id())->findOrFail($id);
        $routine->delete();

        return response()->json(['message' => 'Routine eliminata']);
    }

    public function complete($id)
    {
        $routine = Routine::where('user_id', Auth::id())->findOrFail($id);

        $today = now()->toDateString();
        $completions = json_decode($routine->completions, true) ?? [];

        // Evita duplicati
        if (!in_array($today, $completions)) {
            $completions[] = $today;
        }

        $routine->update([
            'completions' => json_encode($completions),
            'last_executed' => now(),
            'next_due' => $this->calculateNextDueDate($routine->frequency, now())
        ]);

        return response()->json([
            'message' => 'Routine completata',
            'next_due' => $routine->next_due
        ]);
    }


    public function sendReminders()
    {
        // DATA PER TEST $now = Carbon::createFromFormat('Y-m-d H:i:s', '2025-07-10 22:30:00'); 
        $now = now();
        $today = $now->toDateString();

        // 2. Routine scadute e non completate
        $overdueRoutines = Routine::where('next_due', '<', $now)
            ->where(function ($query) use ($today) {
                $query->whereJsonDoesntContain('completions', $today)
                    ->orWhereNull('completions');
            })
            ->with('user')
            ->get();

        foreach ($overdueRoutines as $routine) {
            Mail::to($routine->user->email)->send(new RoutineMissed($routine));
        }

        return response()->json([
            'message' => 'Inviate ' .
                count($overdueRoutines) . ' notifiche di mancato completamento'
        ]);
    }
    private function calculateNextDueDate($frequency, $fromDate = null)
    {
        $fromDate = $fromDate ?: now();

        switch ($frequency) {
            case 'daily':
                return $fromDate->addDay()->setTime(23, 59, 0);
            case 'every_2_days':
                return $fromDate->addDays(2)->setTime(23, 59, 0);
            case 'weekly':
                return $fromDate->addWeek()->setTime(23, 59, 0);
            case 'monthly':
                return $fromDate->addMonth()->setTime(23, 59, 0);
            default:
                return $fromDate->addDay()->setTime(23, 59, 0);
        }
    }
}
