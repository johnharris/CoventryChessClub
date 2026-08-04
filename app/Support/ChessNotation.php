<?php

namespace App\Support;

/**
 * Server-side FEN and PGN handling.
 *
 * The interactive board is drawn in the browser by Chessground (lichess's own
 * board component) and driven by chess.js. This class covers what the server
 * still needs to do: validate what members submit, and read PGN headers so the
 * post form can fill itself in.
 */
class ChessNotation
{
    public const START_FEN = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

    /**
     * Validate a FEN. Returns null when valid, or a human-readable reason.
     */
    public static function fenError(string $fen): ?string
    {
        $fen = trim(preg_replace('/\s+/', ' ', $fen) ?? '');

        if ($fen === '') {
            return 'Please provide a FEN string.';
        }

        $parts = explode(' ', $fen);

        if (count($parts) < 2) {
            return 'A FEN needs at least a board layout and the side to move, for example "8/8/8/8/8/8/8/8 w - -".';
        }

        [$board, $active] = [$parts[0], $parts[1]];

        $ranks = explode('/', $board);

        if (count($ranks) !== 8) {
            return 'The board layout must have 8 ranks separated by "/". Yours has '.count($ranks).'.';
        }

        foreach ($ranks as $i => $rank) {
            $squares = 0;

            foreach (str_split($rank) as $char) {
                if (ctype_digit($char)) {
                    if ($char === '0') {
                        return 'Rank '.(8 - $i).' contains a "0", which is not a valid gap.';
                    }
                    $squares += (int) $char;
                } elseif (preg_match('/^[prnbqkPRNBQK]$/', $char)) {
                    $squares++;
                } else {
                    return 'Rank '.(8 - $i).' contains an unexpected character "'.$char.'".';
                }
            }

            if ($squares !== 8) {
                return 'Rank '.(8 - $i)." describes {$squares} squares, but every rank must describe exactly 8.";
            }
        }

        if (! in_array(strtolower($active), ['w', 'b'], true)) {
            return 'The side to move must be "w" or "b".';
        }

        // Exactly one king each, otherwise the position cannot be displayed sensibly.
        foreach (['K' => 'white', 'k' => 'black'] as $symbol => $colour) {
            $count = substr_count($board, $symbol);

            if ($count !== 1) {
                return "The position must contain exactly one {$colour} king (found {$count}).";
            }
        }

        if (isset($parts[2]) && ! preg_match('/^(-|K?Q?k?q?|[A-Ha-h]{1,4})$/', $parts[2])) {
            return 'The castling field must be "-" or a combination of K, Q, k and q.';
        }

        if (isset($parts[3]) && ! preg_match('/^(-|[a-h][36])$/', $parts[3])) {
            return 'The en passant field must be "-" or a square such as "e3".';
        }

        return null;
    }

    public static function isValidFen(string $fen): bool
    {
        return self::fenError($fen) === null;
    }

    /**
     * Tidy whitespace and supply the optional trailing fields if omitted, so the
     * stored FEN is always complete and safe to hand to the board.
     */
    public static function normaliseFen(string $fen): string
    {
        $parts = preg_split('/\s+/', trim($fen)) ?: [];

        $parts[1] = strtolower($parts[1] ?? 'w');
        $parts[2] = $parts[2] ?? '-';
        $parts[3] = $parts[3] ?? '-';
        $parts[4] = $parts[4] ?? '0';
        $parts[5] = $parts[5] ?? '1';

        return implode(' ', array_slice($parts, 0, 6));
    }

    /**
     * 'w' or 'b' — used to print "White to move" beneath a diagram.
     */
    public static function sideToMove(string $fen): string
    {
        $parts = preg_split('/\s+/', trim($fen)) ?: [];

        return strtolower($parts[1] ?? 'w') === 'b' ? 'b' : 'w';
    }

    /**
     * Read the bracketed header tags of a PGN, e.g. [White "Smith, J"].
     *
     * @return array<string, string>
     */
    public static function pgnHeaders(string $pgn): array
    {
        preg_match_all('/^\s*\[\s*(\w+)\s+"([^"]*)"\s*\]\s*$/m', $pgn, $matches, PREG_SET_ORDER);

        $headers = [];

        foreach ($matches as $match) {
            $headers[$match[1]] = trim($match[2]);
        }

        return $headers;
    }

    /**
     * The move text of a PGN, with the header block removed.
     */
    public static function pgnMoveText(string $pgn): string
    {
        $body = preg_replace('/^\s*\[\s*\w+\s+"[^"]*"\s*\]\s*$/m', '', $pgn) ?? $pgn;

        return trim(preg_replace('/\n{3,}/', "\n\n", $body) ?? $body);
    }

    /**
     * Convert a PGN date (`2026.02.28`, `2026.??.??`) to `Y-m-d`, or null.
     */
    public static function pgnDate(string $value): ?string
    {
        if (! preg_match('/^(\d{4})\.(\d{2}|\?\?)\.(\d{2}|\?\?)/', trim($value), $m)) {
            return null;
        }

        $year = (int) $m[1];
        $month = $m[2] === '??' ? 1 : (int) $m[2];
        $day = $m[3] === '??' ? 1 : (int) $m[3];

        if (! checkdate(max($month, 1), max($day, 1), $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * Validate a PGN well enough to reject paste accidents. Returns null when
     * acceptable, otherwise a human-readable reason. Full legality checking is
     * left to chess.js in the browser, which reports it in the editor preview.
     */
    public static function pgnError(string $pgn): ?string
    {
        $pgn = trim($pgn);

        if ($pgn === '') {
            return 'Please paste a PGN, or upload a .pgn file.';
        }

        $moveText = self::pgnMoveText($pgn);

        if ($moveText === '') {
            return 'That PGN contains header tags but no moves.';
        }

        // Strip comments, variations and NAGs before looking for move numbers.
        $stripped = preg_replace('/\{[^}]*\}/', ' ', $moveText) ?? $moveText;
        $stripped = preg_replace('/;[^\n]*/', ' ', $stripped) ?? $stripped;
        $stripped = preg_replace('/\$\d+/', ' ', $stripped) ?? $stripped;

        if (! preg_match('/\b1\s*\.\s*\S/', $stripped)) {
            return 'That does not look like a PGN — no numbered moves were found (expected something like "1. e4 e5").';
        }

        if (substr_count($moveText, '(') !== substr_count($moveText, ')')) {
            return 'The variation brackets "(" and ")" in the PGN are not balanced.';
        }

        if (substr_count($moveText, '{') !== substr_count($moveText, '}')) {
            return 'The annotation braces "{" and "}" in the PGN are not balanced.';
        }

        return null;
    }

    public static function isValidPgn(string $pgn): bool
    {
        return self::pgnError($pgn) === null;
    }

    /**
     * A summary of a game post used by the server-rendered fallback view, so the
     * moves and annotations are present in the HTML even before JavaScript runs
     * (good for search engines and for users with scripting disabled).
     *
     * @return array{headers: array<string,string>, moveText: string, annotations: list<string>}
     */
    public static function parseGame(string $pgn): array
    {
        $headers = self::pgnHeaders($pgn);
        $moveText = self::pgnMoveText($pgn);

        preg_match_all('/\{([^}]*)\}/', $moveText, $matches);

        $annotations = array_values(array_filter(array_map(
            fn ($c) => trim(preg_replace('/\s+/', ' ', $c) ?? ''),
            $matches[1] ?? []
        )));

        return [
            'headers' => $headers,
            'moveText' => $moveText,
            'annotations' => $annotations,
        ];
    }
}
