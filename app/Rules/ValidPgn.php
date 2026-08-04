<?php

namespace App\Rules;

use App\Support\ChessNotation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Sanity-checks a pasted or uploaded PGN. Deep legality checking happens in the
 * browser via chess.js, which shows the author a live preview of the game.
 */
class ValidPgn implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The game must be given as PGN text.');

            return;
        }

        if ($error = ChessNotation::pgnError($value)) {
            $fail($error);
        }
    }
}
