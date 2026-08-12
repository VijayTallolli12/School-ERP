<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetSchoolContext;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Services\LoginActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly LoginActivityService $loginActivityService) {}

    public function create(): View
    {
        return view('modules.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
        } catch (ValidationException $exception) {
            $this->loginActivityService->recordFailure($request, 'Invalid credentials');

            throw $exception;
        }

        $request->session()->regenerate();

        $this->loginActivityService->recordSuccess($request, $request->user());

        $user = $request->user();

        // Apply school context BEFORE any role/permission checks.
        // The SetSchoolContext middleware hasn't run yet at this point,
        // so we need to set the team ID manually for hasRole() to work.
        SetSchoolContext::applySchoolContext($user, $request);

        // The School ERP web application is an internal staff application.
        // Parents and Students use the mobile app / portal experiences and
        // must NOT be able to log into the administrative web ERP.
        foreach (config('access.external_roles', ['Parent', 'Student']) as $externalRole) {
            if ($user->hasRole($externalRole)) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'This portal is for school staff only. Parents and students use the mobile app.',
                ]);
            }
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $this->loginActivityService->recordLogout($request, $user);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
