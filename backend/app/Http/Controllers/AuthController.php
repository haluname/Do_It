<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validazione
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'gender' => 'required|in:Male,Female',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Utente registrato con successo',
            'user' => $user,
            'token' => $token
        ], 201);
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([ 
            'email' => 'required', 
            'password' => 'required' 
        ]);


        if (Auth::attempt($credentials)) {
            $user = User::where('email', $credentials['email'])->first();
            if ($user && Hash::check($credentials['password'], $user->password)) {
                            /** @var \App\Models\User $user **/
                $user = Auth::user();
                $token = $user->createToken('api-token')->plainTextToken;

               
            return response()->json([
                'message' => 'Autenticazione riuscita',
                'token' => $token
            ]);
            }
        }
        return response()->json(['error' => 'Email o password sbagliata'], 401);

    
    }


    

    
}
