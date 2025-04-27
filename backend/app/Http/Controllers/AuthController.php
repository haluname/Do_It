<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\TempUser;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mail;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        /** @var User $user */
        $user = Auth::user(); // <-- Type hinting per IDE

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken // <-- Ora dovrebbe funzionare
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('goals'); // Carica i goals dell'utente
        return response()->json($user);
    }



    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255|unique:users,name|unique:temp_users,name',
            'email' => 'required|string|email|max:255|unique:users,email|unique:temp_users,email',
            'password' => 'required|string|min:8',
            'gender' => 'required|string|in:Male,Female'
        ]);
    
        $tempUser = TempUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender
        ]);


        $this->sendVerification($tempUser);

        return response()->json([
            'message' => 'OTP inviato con successo',
            'email' => $tempUser->email
        ]);
    }

    public function sendVerification(TempUser $user)
    {

        PasswordResetOtp::where('email', $user->email)->delete();

        $otp = strtoupper(Str::random(6));
        $expiresAt = now()->addMinutes(15);

        PasswordResetOtp::updateOrCreate(
            ['email' => $user->email],
            [
                'code' => Hash::make($otp),
                'expires_at' => $expiresAt,
            ]
        );

        Mail::to($user->email)->send(new PasswordResetOtpMail($otp , 'verify'));

        return response()->json(['message' => 'Nuovo OTP inviato']);
    }
}
