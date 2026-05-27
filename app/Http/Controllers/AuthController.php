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



    public function showProfile()
    {
        return view('pages.profile');
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

            $redirect = Auth::user()->isAdmin()
                ? route('admin.dashboard')
                : route('home');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login successful.',
                    'redirect' => $redirect,
                ]);
            }

            return redirect()->intended($redirect);
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

            $redirect = Auth::user()->isAdmin()
                ? route('admin.dashboard')
                : route('home');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Registration successful! Welcome to Ridly.',
                    'redirect' => $redirect,
                ], 200);
            }

            return redirect()->to($redirect)->with('success', 'Registration successful! Welcome to Ridly.');
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

    /**
     * Delete the authenticated user's account after password verification.
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => ['password' => ['The password you entered is incorrect.']],
            ], 422);
        }

        // Clean up related data
        $user->cartItems()->delete();
        $user->favorites()->delete();
        $user->tickets()->delete();
        $user->userDiscounts()->delete();
        $user->gachaState()?->delete();
        $user->activeBoosters()->delete();

        // Log out before deletion
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Account deleted successfully.', 'redirect' => route('home')]);
        }

        return redirect(route('home'));
    }
}
