<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Auth;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Auth::user()->reports()
            ->orderBy('year', 'desc')
            ->orderBy('week', 'desc')
            ->get();

        return response()->json($reports);
    }

    // Salva un nuovo report
    public function store(Request $request)
    {
        $request->validate([
            'week' => 'required|integer',
            'year' => 'required|integer',
            'content' => 'required|string'
        ]);

        $user = Auth::user();
        
        // Controlla se esiste già un report per questa settimana
        $existingReport = Report::where('user_id', $user->id)
            ->where('week', $request->week)
            ->where('year', $request->year)
            ->first();

        if ($existingReport) {
            return response()->json([
                'message' => 'Hai già un report per questa settimana'
            ], 409);
        }

        $report = $user->reports()->create([
            'week' => $request->week,
            'year' => $request->year,
            'content' => $request->content
        ]);

        // Aggiungi esperienza per aver creato il report
        $user->addExperience(25);

        return response()->json([
            'message' => 'Report creato con successo',
            'report' => $report,
            'user' => $user
        ], 201);
    }

    // Ottieni un singolo report
    public function show(Report $report)
    {
        // Autorizzazione: l'utente può vedere solo i propri report
        if (Auth::id() !== $report->user_id) {
            return response()->json(['message' => 'Non autorizzato'], 403);
        }

        return response()->json($report);
    }

    // Elimina un report
    public function destroy(Report $report)
    {
        if (Auth::id() !== $report->user_id) {
            return response()->json(['message' => 'Non autorizzato'], 403);
        }

        $report->delete();
        return response()->json(['message' => 'Report eliminato con successo']);
    }

    // Verifica se esiste un report per la settimana
    public function check(Request $request)
    {
        $request->validate([
            'week' => 'required|integer',
            'year' => 'required|integer'
        ]);

        $exists = Report::where('user_id', Auth::id())
            ->where('week', $request->week)
            ->where('year', $request->year)
            ->exists();

        return response()->json(['exists' => $exists]);
    }
}
