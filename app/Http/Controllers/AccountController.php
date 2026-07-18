<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Публичная авторизация активаторов (в стиле сайта, штатный web-guard).
 * Модерация остаётся в Filament /admin (только role moderator/admin).
 */
class AccountController extends Controller
{
    // --- Регистрация ---

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'callsign' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9\/]+$/i', 'unique:users,callsign'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'name' => 'имя',
            'callsign' => 'позывной',
            'email' => 'email',
            'password' => 'пароль',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'callsign' => strtoupper($data['callsign']),
            'email' => $data['email'],
            'password' => $data['password'], // хешируется каст-ом 'hashed'
            'role' => User::ROLE_USER,
        ]);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        // Письмо с подтверждением email (не роняем регистрацию, если почта не настроена)
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('verification.notice')
            ->with('success', 'Аккаунт создан. Мы отправили письмо для подтверждения email.');
    }

    // --- Вход ---

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], [
            'login' => 'позывной или email',
            'password' => 'пароль',
        ]);

        // Вход по email или по позывному
        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'callsign';
        $attempt = [
            $field => $field === 'callsign' ? strtoupper($credentials['login']) : $credentials['login'],
            'password' => $credentials['password'],
        ];

        if (! Auth::attempt($attempt, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Неверный позывной/email или пароль.',
            ]);
        }

        $request->session()->regenerate();

        // Модератора отправляем в админку, активатора — в кабинет
        $user = Auth::user();

        return redirect()->intended($user->isModerator() ? '/admin' : route('cabinet'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // --- Подтверждение email ---

    public function verifyNotice()
    {
        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->fulfill(); // помечает email подтверждённым + событие Verified
        }

        return redirect()->route('cabinet')->with('success', 'Email подтверждён — спасибо!');
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('cabinet');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['email' => 'Не удалось отправить письмо. Проверьте настройки почты или попробуйте позже.']);
        }

        return back()->with('success', 'Письмо отправлено повторно.');
    }

    // --- Личный кабинет ---

    public function cabinet(Request $request)
    {
        $activations = $request->user()
            ->activations()
            ->with('park')
            ->withCount(['proofs as photos_count' => fn ($q) => $q->where('type', 'photo')])
            ->latest()
            ->get();

        return view('cabinet.index', compact('activations'));
    }
}
