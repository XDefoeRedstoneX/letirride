<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // LOGIN & REGISTER
    public function showLogin()
    {
        return view('');
    }

    public function showRegister()
    {
        return view('ganti');
    }

    public function showSettings()
    {
        return view('pages.settings');
    }

    public function showProfile()
    {
        return view('pages.profile');
    }

    public function showInv()
    {
        return view('pages.inventory');
    }

    public function showTrans()
    {
        return view('pages.transactions');
    }

    public function showForgot()
    {
        return view('pages.forgot-password');
    }

    public function logAuth(Request $request)
    {
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($creds)) {
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login successful.',
                    'redirect' => route('home'),
                ]);
            }

            return redirect()->intended(route('home'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Those credentials do not match our records.',
                'errors' => [
                    'email' => ['Those credentials do not match our records.'],
                ],
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('home'));
    }

    public function regAuth(Request $request)
    {
        try {
            $creds = $request->validate([
                'name' => 'required|min:2|max:50',
                'email' => 'required|email:dns|unique:users',
                'password' => 'required|min:8',
            ]);

            $creds['referral_code'] = strtoupper(Str::random(8));

            $user = User::create($creds);

            Auth::login($user);
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Registration successful! Welcome to Ridly.',
                    'redirect' => route('home'),
                ], 200);
            }

            return redirect()->route('home')->with('success', 'Registration successful! Welcome to Ridly.');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
    }

    // SETTINGS
    public function changeEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email:dns|unique:users',
        ]);

        Auth::user()->update($request->only('email'));

        return back()->with('success', 'Email updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['The current password you entered is incorrect.']],
            ], 422); // 422 so your AJAX catches it as an error!
        }

        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'errors' => ['new_password' => ['New password cannot be the same as the old password.']],
            ], 422);
        }
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Password updated successfully.',
                'name' => Auth::user()->name,
            ], 200);
        }
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        Auth::user()->update($request->only('name'));
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'name' => Auth::user()->name,
            ], 200);
        }
    }
}
