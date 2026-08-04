<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Renders post and page bodies from Markdown to safe HTML.
 *
 * On top of ordinary Markdown, authors can drop a board into the middle of any
 * post using a fenced block, which becomes a full Chessground diagram:
 *
 *     ```fen
 *     r1bqkbnr/pppp1ppp/2n5/4p3/2B1P3/5N2/PPPP1PPP/RNBQK2R b KQkq - 5 4
 *     Caption: Black to play — how should the knight be met?
 *     Orientation: black
 *     ```
 */
class Markdown
{
    protected static ?MarkdownConverter $converter = null;

    public static function toHtml(?string $markdown): HtmlString
    {
        if (blank($markdown)) {
            return new HtmlString('');
        }

        // Pull out the fenced FEN blocks first, leaving a placeholder behind so
        // the Markdown parser never sees them.
        [$text, $boards] = self::extractBoards($markdown);

        $html = (string) self::converter()->convert($text);

        foreach ($boards as $token => $board) {
            $html = str_replace(
                ['<p>'.$token.'</p>', $token],
                view('partials.inline-board', $board)->render(),
                $html
            );
        }

        return new HtmlString($html);
    }

    /**
     * Replace ```fen ... ``` blocks with placeholders and collect their data.
     *
     * @return array{0: string, 1: array<string, array<string, string|null>>}
     */
    protected static function extractBoards(string $markdown): array
    {
        $boards = [];
        $index = 0;

        $text = preg_replace_callback(
            '/^```fen\s*\n(.*?)\n?```\s*$/ms',
            function (array $m) use (&$boards, &$index) {
                $board = self::parseBoardBlock($m[1]);

                if ($board === null) {
                    return $m[0];
                }

                $token = 'CHESSBOARDPLACEHOLDER'.$index++;
                $boards[$token] = $board;

                return $token;
            },
            $markdown
        ) ?? $markdown;

        return [$text, $boards];
    }

    /**
     * First non-empty line is the FEN; subsequent "Key: value" lines are options.
     *
     * @return array<string, string|null>|null
     */
    protected static function parseBoardBlock(string $block): ?array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), 'strlen'));

        if ($lines === []) {
            return null;
        }

        $fen = array_shift($lines);

        if (! ChessNotation::isValidFen($fen)) {
            return null;
        }

        $options = [
            'fen' => ChessNotation::normaliseFen($fen),
            'caption' => null,
            'orientation' => 'white',
            'solution' => null,
        ];

        foreach ($lines as $line) {
            if (! str_contains($line, ':')) {
                // A bare trailing line is treated as the caption.
                $options['caption'] ??= $line;

                continue;
            }

            [$key, $value] = array_map('trim', explode(':', $line, 2));
            $key = Str::lower($key);

            if ($key === 'orientation') {
                $options['orientation'] = Str::lower($value) === 'black' ? 'black' : 'white';
            } elseif (in_array($key, ['caption', 'solution'], true)) {
                $options[$key] = $value;
            }
        }

        $options['side_to_move'] = ChessNotation::sideToMove($options['fen']);

        return $options;
    }

    protected static function converter(): MarkdownConverter
    {
        if (self::$converter instanceof MarkdownConverter) {
            return self::$converter;
        }

        $environment = new Environment([
            // Escape any raw HTML members paste in, so posts can never break the
            // page layout or inject scripts.
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);
        $environment->addExtension(new AutolinkExtension);

        return self::$converter = new MarkdownConverter($environment);
    }
}
