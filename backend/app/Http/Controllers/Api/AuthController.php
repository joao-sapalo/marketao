<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\OtpCode;
use App\Models\Role;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use App\Notifications\SendOtpCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::with('role')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Email não verificado. Verifique o código OTP enviado ao seu email.'], 403);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Conta desactivada. Contacte o administrador.'], 403);
        }

        $token = $user->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'position' => $request->position,
            'email_verified_at' => null,
            'role_id' => Role::firstOrCreate(
                ['name' => 'Operador'],
                ['description' => 'Acesso limitado ao sistema']
            )->id,
        ]);

        $this->generateAndSendOtp($user->email, 'registration');

        return response()->json([
            'message' => 'Conta criada. Verifique o código OTP enviado ao seu email.',
            'email' => $user->email,
        ], 201);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'type' => ['required', 'in:registration,password_reset'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($request->type === 'registration' && $user->email_verified_at) {
            return response()->json(['message' => 'Email já verificado.'], 400);
        }

        $this->generateAndSendOtp($request->email, $request->type);

        return response()->json(['message' => 'Código OTP enviado ao email.']);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'type' => ['required', 'in:registration,password_reset'],
        ]);

        $otp = OtpCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('type', $request->type)
            ->latest()
            ->first();

        if (!$otp || !$otp->isValid()) {
            return response()->json(['message' => 'Código inválido ou expirado.'], 400);
        }

        $otp->markAsUsed();

        if ($request->type === 'registration') {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->update(['email_verified_at' => now()]);

                $token = $user->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

                return response()->json([
                    'message' => 'Email verificado com sucesso.',
                    'token' => $token,
                    'user' => $user->load('role'),
                ]);
            }
        }

        return response()->json([
            'message' => 'Código verificado com sucesso.',
            'verified' => true,
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Email não verificado.'], 400);
        }

        $this->generateAndSendOtp($request->email, 'password_reset');

        return response()->json([
            'message' => 'Código de recuperação enviado ao email.',
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $otp = OtpCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('type', 'password_reset')
            ->latest()
            ->first();

        if (!$otp || !$otp->isValid()) {
            return response()->json(['message' => 'Código inválido ou expirado.'], 400);
        }

        $otp->markAsUsed();

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Senha redefinida com sucesso.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('role'));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'password' => 'sometimes|min:8|confirmed',
            'current_password' => 'required_with:password|string',
        ]);

        if (isset($validated['password'])) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Senha actual incorrecta.'], 400);
            }
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->load('role'));
    }

    private function generateAndSendOtp(string $email, string $type): void
    {
        OtpCode::where('email', $email)->where('type', $type)->whereNull('used_at')->where('expires_at', '>', now())->update(['used_at' => now()]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'email' => $email,
            'code' => $code,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->notify(new SendOtpCode($code, $type));
            } else {
                Notification::route('mail', $email)->notify(new SendOtpCode($code, $type));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send OTP email: ' . $e->getMessage());
        }
    }
}
