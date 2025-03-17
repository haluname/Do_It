<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;



Route::get('/user', function (Request $request) {
    return response()->json(['user' => Auth::user()]);
})->middleware('auth');



Route::get('/prova', function (Request $request) {
    return response()->json(['user' => Auth::user()]);
});


Route::post('/register', function (Request $request) {
    // Ottieni i parametri direttamente dalla richiesta
    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');

    // Log dei dati in ingresso
    Log::info('Name: ' . $name);
    Log::info('Email: ' . $email);
    Log::info('Password: ' . $password);

    // Crea un nuovo utente nel database
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
    ]);

    // Restituisci una risposta JSON con i dettagli dell'utente appena creato
    return response()->json([
        'message' => 'Utente creato con successo',
        'user' => $user,
    ], 201);
});



Route::post('/login', function (Request $request) {
    // Validazione dei dati in ingresso
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    // Tentativo di autenticazione con le credenziali fornite
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'message' => 'Credenziali non valide'
        ], 401);
    }

    // Se l'autenticazione ha successo, otteniamo l'utente autenticato
    $user = Auth::user();

    // Qui potresti generare un token per l'accesso alle API (es. con Laravel Sanctum o Passport)
    // $token = $user->createToken('API Token')->plainTextToken;

    return response()->json([
        'message' => 'Login effettuato con successo',
        'user'    => $user,
        // 'token'   => $token, // scommenta se utilizzi token
    ], 200);
});



Route::post('/logout', function () {
    Auth::logout();
    return response()->json(['message' => 'Logout effettuato']);
});





