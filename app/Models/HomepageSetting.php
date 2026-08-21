<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    public const DEFAULT_FEN = 'r1bqk2r/pppp1ppp/2n2n2/2b1p3/2B1P3/2NP1N2/PPP2PPP/R1BQK2R w KQkq - 4 6';

    public const DEFAULT_ORIENTATION = 'white';

    public const DEFAULT_CAPTION = 'The Italian Game — one of the openings you will meet on a club night';

    protected $fillable = [
        'hero_fen',
        'hero_orientation',
        'hero_caption',
    ];

    /**
     * Return the club's saved homepage position, or the built-in Italian Game
     * without writing to the database during a public page request.
     */
    public static function current(): self
    {
        return self::query()->first() ?? new self(self::defaults());
    }

    /** @return array{hero_fen: string, hero_orientation: string, hero_caption: string} */
    public static function defaults(): array
    {
        return [
            'hero_fen' => self::DEFAULT_FEN,
            'hero_orientation' => self::DEFAULT_ORIENTATION,
            'hero_caption' => self::DEFAULT_CAPTION,
        ];
    }
}
