<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * The two permission levels in practice:
 *   - member: may create posts, and edit or delete only their own
 *   - admin:  may edit or delete anybody's, and may feature posts
 */
class PostPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Post $post): bool
    {
        if ($post->is_published && ! ($post->published_at?->isFuture() ?? false)) {
            return true;
        }

        return $user !== null && $user->ownsOrAdmin($post);
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->is_active && $user->ownsOrAdmin($post);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->is_active && $user->ownsOrAdmin($post);
    }

    public function feature(User $user): bool
    {
        return $user->isAdmin();
    }
}
