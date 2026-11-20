<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()->isActive()) {
                ActivityLog::record(
                    ActivityLog::MODULE_AUTH,
                    ActivityLog::ACTION_INACTIVE_LOGIN,
                    Auth::user()->name.' attempted to sign in with an inactive account.',
                    Auth::user(),
                    Auth::user(),
                );

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withErrors(['email' => 'This account is inactive. Please contact the administrator.'])
                    ->onlyInput('email');
            }

            $request->session()->regenerate();

            ActivityLog::record(
                ActivityLog::MODULE_AUTH,
                ActivityLog::ACTION_LOGGED_IN,
                Auth::user()->name.' signed in.',
            );

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back to ByteStock!');
        }

        ActivityLog::record(
            ActivityLog::MODULE_AUTH,
            ActivityLog::ACTION_FAILED_LOGIN,
            'A sign-in attempt failed for '.$credentials['email'].'.',
        );

        return back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::record(
                ActivityLog::MODULE_AUTH,
                ActivityLog::ACTION_LOGGED_OUT,
                $user->name.' signed out.',
                null,
                $user,
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
