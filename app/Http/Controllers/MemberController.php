<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Post;
use App\Models\User;
use App\Models\WhitelistEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * The landing page after signing in.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('members.dashboard', [
            'myPosts' => Post::where('user_id', $user->id)->count(),
            'myDrafts' => Post::where('user_id', $user->id)->where('is_published', false)->count(),
            'recent' => Post::with(['user', 'featuredImage'])
                ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(),
            'unreadEnquiries' => $user->isAdmin() ? Enquiry::unread()->count() : null,
            'pendingInvites' => $user->isAdmin()
                ? WhitelistEntry::whereNull('claimed_at')->count()
                : null,
            'memberCount' => $user->isAdmin() ? User::where('is_active', true)->count() : null,
        ]);
    }

    /* =====================================================================
     | Own profile
     * ================================================================== */

    public function editProfile(Request $request): View
    {
        return view('members.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'ecf_code' => ['nullable', 'string', 'max:20'],
            'ecf_rating' => ['nullable', 'integer', 'min:0', 'max:3500'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $request->input('password')]);

        return back()->with('status', 'Password changed.');
    }

    /* =====================================================================
     | Admin: whitelist management
     * ================================================================== */

    public function whitelist(Request $request): View
    {
        return view('members.admin.whitelist', [
            'entries' => WhitelistEntry::with(['claimedBy', 'invitedBy'])
                ->orderByRaw('claimed_at IS NULL DESC')
                ->orderByDesc('created_at')
                ->paginate(20),
            'users' => User::orderBy('name')->paginate(20, ['*'], 'users_page'),
        ]);
    }

    public function storeWhitelist(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190', 'unique:whitelist_entries,email', 'unique:users,email'],
            'name' => ['nullable', 'string', 'max:120'],
            'role' => ['required', Rule::in([User::ROLE_MEMBER, User::ROLE_ADMIN])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $entry = WhitelistEntry::create([
            ...$data,
            'email' => strtolower($data['email']),
            'invite_token' => WhitelistEntry::freshToken(),
            'invited_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Added to the whitelist. Send them this invitation link: '
            .$entry->inviteUrl());
    }

    public function regenerateInvite(Request $request, WhitelistEntry $entry): RedirectResponse
    {
        abort_if($entry->isClaimed(), 422, 'That invitation has already been used.');

        $entry->update(['invite_token' => WhitelistEntry::freshToken()]);

        return back()->with('status', 'New invitation link: '.$entry->inviteUrl());
    }

    public function destroyWhitelist(WhitelistEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('status', 'Whitelist entry removed. '
            .'Note that removing an entry does not delete an account already created from it.');
    }

    /* =====================================================================
     | Admin: user management
     * ================================================================== */

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_MEMBER, User::ROLE_ADMIN])],
            'is_active' => ['boolean'],
        ]);

        // Guard against an admin removing their own access and locking everyone out.
        if ($user->id === $request->user()->id) {
            if ($data['role'] !== User::ROLE_ADMIN || ! $request->boolean('is_active')) {
                return back()->withErrors([
                    'role' => 'You cannot remove your own administrator access. Ask another admin to do it.',
                ]);
            }
        }

        if ($user->isAdmin() && $data['role'] !== User::ROLE_ADMIN
            && User::where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1) {
            return back()->withErrors([
                'role' => 'The club site must keep at least one active administrator.',
            ]);
        }

        $user->update([
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', $user->publicName().' updated.');
    }
}
