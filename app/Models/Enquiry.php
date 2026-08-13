<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'email', 'phone', 'subject', 'enquiry_type',
    'playing_strength', 'message', 'is_read', 'is_archived', 'ip_address',
])]
class Enquiry extends Model
{
    public const TYPES = [
        'general' => 'General enquiry',
        'join' => 'Joining the club',
        'juniors' => 'Junior section',
        'coaching' => 'One-to-one coaching',
        'match' => 'Match or fixture',
        'website' => 'Website feedback',
    ];

    /**
     * Self-assessed playing strength, so club officers have some idea of what
     * to expect before an enquirer turns up. Deliberately coarse: a newcomer
     * cannot be expected to know an ECF rating, and the club only needs to know
     * roughly which board to sit them at.
     */
    public const STRENGTHS = [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
    ];

    /**
     * Longer descriptions shown beside each option on the contact form, so that
     * people pick the right one rather than guessing modestly or optimistically.
     */
    public const STRENGTH_HINTS = [
        'beginner' => 'Knows the moves, or is still learning them',
        'intermediate' => 'Plays regularly, comfortable with a clock',
        'advanced' => 'Experienced club, league or rated player',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    public function scopeInbox(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false)->where('is_archived', false);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->enquiry_type] ?? 'Enquiry';
    }

    /**
     * The field is optional, so this returns null rather than a placeholder and
     * lets each view decide how to present a missing answer.
     */
    public function strengthLabel(): ?string
    {
        return self::STRENGTHS[$this->playing_strength] ?? null;
    }

    public function strengthHint(): ?string
    {
        return self::STRENGTH_HINTS[$this->playing_strength] ?? null;
    }
}
