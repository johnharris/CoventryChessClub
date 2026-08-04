<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'email', 'name', 'role', 'invite_token', 'claimed_at',
    'claimed_by_user_id', 'invited_by_user_id', 'notes',
])]
class WhitelistEntry extends Model
{
    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
        ];
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function isClaimed(): bool
    {
        return $this->claimed_at !== null;
    }

    /**
     * Look up an unclaimed invitation for an email address.
     */
    public static function unclaimedFor(string $email): ?self
    {
        return static::whereRaw('LOWER(email) = ?', [Str::lower(trim($email))])
            ->whereNull('claimed_at')
            ->first();
    }

    public static function freshToken(): string
    {
        return Str::random(48);
    }

    /**
     * The self-service link an admin sends to a new member.
     */
    public function inviteUrl(): string
    {
        return route('register', ['token' => $this->invite_token]);
    }
}
