<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json([
            'data' => $users,
        ]);
    }

    public function shosh(Request $request)
    {
        // Verifica se l'utente è autenticato tramite Sanctum
        if (Auth::check()) {
            return response()->json([
                'message' => 'Accesso consentito',
                'user' => Auth::user()
            ]);
        }

        return response()->json(['error' => 'Token non valido o scaduto'], 401);
    }

}
