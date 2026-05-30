<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;

class AuthenticatedSessionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected LoginRateLimiter $limiter) {}

    /**
     * Show the login view.
     */
    public function create(): View
    {
        return view('auth.login', ['isAdminLogin' => true]);
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @throws ValidationException
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $this->ensureLoginIsNotThrottled($request);

        $this->attemptToAuthenticateAdminUser($request);

        $this->prepareAuthenticatedSession($request);

        return redirect()->intended(route('admin.attendances.index'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('admin.login');
    }

    /**
     * Ensure login is not throttled.
     *
     * @throws ValidationException
     */
    protected function ensureLoginIsNotThrottled(AdminLoginRequest $request): void
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
     *
     * @throws ValidationException
     */
    protected function attemptToAuthenticateAdminUser(AdminLoginRequest $request): void
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
     *
     * @throws ValidationException
     */
    protected function throwFailedAuthenticationException(Request $request): void
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Prepare for an authenticated session.
     */
    protected function prepareAuthenticatedSession(Request $request)
    {
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $this->limiter->clear($request);
    }
}
