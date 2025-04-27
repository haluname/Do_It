<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PasswordResetOtp;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOtpMail;
use App\Models\TempUser;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        PasswordResetOtp::where('email', $request->email)->delete();

        $otp = strtoupper(Str::random(6));
        $expiresAt = now()->addMinutes(15);

        PasswordResetOtp::create([
            'email' => $request->email,
            'code' => Hash::make($otp),
            'expires_at' => $expiresAt
        ]);

        Mail::to($request->email)->send(new PasswordResetOtpMail($otp, 'reset'));

        return response()->json(['message' => 'OTP inviato con successo']);
    }

    // Verifica OTP e resetta password
    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|min:8|confirmed'
        ]);

        // Trova l'OTP più recente
        $otpRecord = PasswordResetOtp::where('email', $request->email)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord || !Hash::check($request->otp, $otpRecord->code)) {
            return response()->json(['message' => 'OTP non valido o scaduto'], 422);
        }

        // Aggiorna password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Elimina l'OTP usato
        $otpRecord->delete();

        return response()->json(['message' => 'Password resettata con successo']);
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        $tempUser = TempUser::where('email', $request->email)
        ->first();

        $otpRecord = PasswordResetOtp::where('email', $request->email)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord || !Hash::check($request->otp, $otpRecord->code)) {
            return response()->json(['message' => 'OTP non valido o scaduto'], 422);
        }

        $user = User::create([
            'name' => $tempUser->name,
            'email' => $tempUser->email,
            'password' => $tempUser->password,
            'gender' => $tempUser->gender,
        ]);
    
        $tempUser->delete();

        $otpRecord->delete();

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ], 201);
    }


  
   

}
