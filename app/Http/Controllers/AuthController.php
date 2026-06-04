<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

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

    /**
     * Email a password reset link to the given address. Always responds with a
     * generic success message so we never reveal whether an account exists.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // A logged-in user can only ever reset their own account — ignore any
        // submitted address (the field is read-only in the UI, but enforce it here).
        $email = Auth::check() ? Auth::user()->email : $request->input('email');

        Password::sendResetLink(['email' => $email]);

        $message = 'If an account exists for that email, a reset link is on its way.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Show the page where the user picks a new password (reached from the email link).
     */
    public function showReset(Request $request, string $token)
    {
        $email = $request->query('email', '');

        // A link is single-use: the broker deletes the token after a successful
        // reset. If the token is missing/expired/already-used, show the dead-link
        // state instead of a form that would only fail on submit.
        $user = User::where('email', $email)->first();
        $valid = $user && Password::tokenExists($user, $token);

        return view('pages.reset-password', [
            'token' => $token,
            'email' => $email,
            'valid' => $valid,
        ]);
    }

    /**
     * Validate the reset token and persist the new password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            return redirect()->route('home')->with('success', 'Your password has been reset. You can now log in.');
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
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
            // Normalize the email so casing/whitespace can never slip past the
            // unique check (e.g. a Google account stored as John@Gmail.com).
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);

            $creds = $request->validate([
                'name' => 'required|min:2|max:50',
                'email' => 'required|email:dns|unique:users',
                'password' => 'required|min:8',
                'referral_code' => 'nullable|string|max:16',
            ], [
                'email.unique' => 'This email is already registered. If you signed up with Google, use "Continue with Google" to log in.',
            ]);

            $referralCode = trim((string) ($creds['referral_code'] ?? ''));
            unset($creds['referral_code']);

            $creds['referral_code'] = strtoupper(Str::random(8));

            $user = User::create($creds);

            $referralMessage = null;
            if ($referralCode !== '') {
                $outcome = app(ReferralService::class)->claimCode($user, $referralCode);
                $referralMessage = match ($outcome['status']) {
                    ReferralService::CLAIM_OK => 'Referral applied! You received '.($outcome['points_awarded'] ?? 0).' bonus points.',
                    ReferralService::CLAIM_NOT_FOUND => 'We couldn\'t find that referral code, but your account was created.',
                    ReferralService::CLAIM_SELF => 'A referral code can\'t be your own — account created anyway.',
                    default => null,
                };
            }

            Auth::login($user);
            $request->session()->regenerate();

            $redirect = Auth::user()->isAdmin()
                ? route('admin.dashboard')
                : route('home');

            $message = 'Registration successful! Welcome to Ridly.';
            if ($referralMessage) {
                $message .= ' '.$referralMessage;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect' => $redirect,
                ], 200);
            }

            return redirect()->to($redirect)->with('success', $message);
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
    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('home');
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Same normalization as manual signup so a Google login matches an
            // existing manual account (auto-link) regardless of casing.
            $email = strtolower(trim((string) $googleUser->getEmail()));

            $user = User::where('google_id', $googleUser->getId())->first()
                ?? User::where('email', $email)->first();

            if ($user) {
                if (! $user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                $user = User::create([
                    'name'          => $googleUser->getName(),
                    'email'         => $email,
                    'google_id'     => $googleUser->getId(),
                    'password'      => null,
                    'referral_code' => strtoupper(Str::random(8)),
                ]);
            }

            Auth::login($user, remember: true);

            return redirect()->intended(
                $user->isAdmin() ? route('admin.dashboard') : route('home')
            );
        } catch (\Throwable $e) {
            Log::warning('Google sign-in failed: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('home')->with('error', 'Google sign-in failed. Please try again.');
        }
    }

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
