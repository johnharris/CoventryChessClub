<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'email', 'phone', 'subject', 'enquiry_type',
    'message', 'is_read', 'is_archived', 'ip_address',
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
}
