<?php

namespace App\Rules;

use App\Support\ChessNotation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects malformed FEN strings with a message that explains what is wrong,
 * rather than a generic "invalid" error.
 */
class ValidFen implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The position must be given as a FEN string.');

            return;
        }

        if ($error = ChessNotation::fenError($value)) {
            $fail($error);
        }
    }
}
