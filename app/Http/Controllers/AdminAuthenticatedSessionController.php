<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;

class AdminAuthenticatedSessionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected LoginRateLimiter $limiter) {}

    /**
     * Show the login view.
     */
    public function create()
    {
        return view('auth.login', ['admin' => true]);
    }

    /**
     * Attempt to authenticate a new session.
     */
    public function store(AdminLoginRequest $request)
    {
        $this->ensureLoginIsNotThrottled($request);
        
        $this->attemptToAuthenticateAdminUser($request);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $this->limiter->clear($request);

        return redirect()->intended(route('admin.attendances.index'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('admin.login');
    }

    /**
     * Ensure if login is not throttled.
     */
    protected function ensureLoginIsNotThrottled(AdminLoginRequest $request)
    {
        if ($this->limiter->tooManyAttempts($request)) {
            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.throttle', [
                    'seconds' => $this->limiter->availableIn($request),
                ])],
            ]);
        }
    }

    /**
     * Attempt to authenticate an admin user.
     */
    protected function attemptToAuthenticateAdminUser(AdminLoginRequest $request)
    {
        if (! Auth::attempt($request->validated())) {
            $this->throwFailedAuthenticationException($request);
        }

        if (! Auth::user()->is_admin) {
            Auth::logout();

            $this->throwFailedAuthenticationException($request);
        }
    }

    /**
     * Throw a failed authentication validation exception.
     */
    protected function throwFailedAuthenticationException(Request $request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }
}
