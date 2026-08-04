<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WhitelistEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Registration is whitelist-only. There is no open sign-up: an administrator
 * must first add the member's email address to the whitelist. The member then
 * either follows their personal invitation link, or simply registers using the
 * whitelisted email address.
 */
class RegisterController extends Controller
{
    public function show(Request $request): View
    {
        $token = $request->query('token');
        $entry = null;

        if ($token) {
            $entry = WhitelistEntry::where('invite_token', $token)
                ->whereNull('claimed_at')
                ->first();
        }

        return view('auth.register', [
            'entry' => $entry,
            'token' => $token,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'token' => ['nullable', 'string'],
        ]);

        // The whitelist check: either the invite token or the email address must
        // match an unclaimed whitelist entry.
        $entry = filled($data['token'] ?? null)
            ? WhitelistEntry::where('invite_token', $data['token'])->whereNull('claimed_at')->first()
            : WhitelistEntry::unclaimedFor($data['email']);

        if (! $entry) {
            throw ValidationException::withMessages([
                'email' => 'That email address is not on the club whitelist. '
                    .'Please ask a club administrator to add you, then try again.',
            ]);
        }

        // If an invitation link was used, the account must match that address.
        if (filled($data['token'] ?? null)
            && Str::lower($entry->email) !== Str::lower($data['email'])) {
            throw ValidationException::withMessages([
                'email' => 'This invitation was issued to '.$entry->email.'.',
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
            'role' => $entry->role,
            'is_active' => true,
        ]);

        $entry->update([
            'claimed_at' => now(),
            'claimed_by_user_id' => $user->id,
            'invite_token' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('members.dashboard')
            ->with('status', 'Your account is ready. Welcome to the club site.');
    }
}
