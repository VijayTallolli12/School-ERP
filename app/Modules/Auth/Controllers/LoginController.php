<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
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

        if ($user->hasRole('Parent')) {
            return redirect()->intended(route('admin.parent-portal.dashboard'));
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
