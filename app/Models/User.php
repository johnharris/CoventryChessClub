<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'role', 'is_active',
    'display_name', 'ecf_code', 'ecf_rating', 'bio',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MEMBER = 'member';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'ecf_rating' => 'integer',
        ];
    }

    /**
     * Posts written by this member.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Administrators can manage the whitelist, all posts, all pages and enquiries.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    /**
     * Admins may act on anything; members only on their own content.
     */
    public function ownsOrAdmin(Post $post): bool
    {
        return $this->isAdmin() || $post->user_id === $this->id;
    }

    /**
     * Name shown on posts and in the members list.
     */
    public function publicName(): string
    {
        return $this->display_name ?: $this->name;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->publicName())) ?: [];
        $letters = array_map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)), $parts);

        return implode('', array_slice($letters, 0, 2)) ?: '?';
    }
}
