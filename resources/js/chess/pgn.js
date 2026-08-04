import { Chess } from 'chess.js';

/**
 * Turns a PGN into the flat list of positions the viewer steps through.
 *
 * chess.js gives us legality and SAN, but discards the comments and NAGs that
 * make an annotated game worth reading, so the move text is walked here as well
 * to pair each move with the annotation that follows it.
 */

/** Standard NAGs, shown as the symbols players expect. */
const NAG_SYMBOLS = {
    1: '!',
    2: '?',
    3: '!!',
    4: '??',
    5: '!?',
    6: '?!',
    10: '=',
    13: '∞',
    14: '⩲',
    15: '⩱',
    16: '±',
    17: '∓',
    18: '+−',
    19: '−+',
    22: '⨀',
    32: '⟳',
    36: '↑',
    40: '→',
    44: '⇆',
};

const stripHeaders = (pgn) => pgn.replace(/^\s*\[\s*\w+\s+"[^"]*"\s*\]\s*$/gm, '').trim();

/**
 * Walk the move text, skipping variations, and collect for each move in the main
 * line: its SAN, any `!`/`?` suffix, any `$n` NAGs and any `{...}` comment.
 */
const readMainLine = (moveText) => {
    const entries = [];
    let index = 0;
    let depth = 0;
    let pendingPreComment = null;

    const attach = (key, value) => {
        const target = entries[entries.length - 1];

        if (!target) {
            // A comment before the first move belongs to the starting position.
            if (key === 'comment') pendingPreComment = value;

            return;
        }

        if (key === 'comment') {
            target.comment = target.comment ? `${target.comment} ${value}` : value;
        } else if (key === 'nag') {
            target.nags.push(value);
        }
    };

    while (index < moveText.length) {
        const char = moveText[index];

        // --- Comments -------------------------------------------------------
        if (char === '{') {
            const end = moveText.indexOf('}', index);
            const body = moveText.slice(index + 1, end === -1 ? undefined : end);

            if (depth === 0) {
                attach('comment', body.replace(/\s+/g, ' ').trim());
            }

            index = end === -1 ? moveText.length : end + 1;
            continue;
        }

        if (char === ';') {
            const end = moveText.indexOf('\n', index);

            if (depth === 0) {
                attach('comment', moveText.slice(index + 1, end === -1 ? undefined : end).trim());
            }

            index = end === -1 ? moveText.length : end + 1;
            continue;
        }

        // --- Variations (kept out of the main line) -------------------------
        if (char === '(') {
            depth += 1;
            index += 1;
            continue;
        }

        if (char === ')') {
            depth = Math.max(0, depth - 1);
            index += 1;
            continue;
        }

        // --- NAGs -----------------------------------------------------------
        if (char === '$') {
            const match = /^\$(\d+)/.exec(moveText.slice(index));

            if (match) {
                if (depth === 0) {
                    attach('nag', NAG_SYMBOLS[Number(match[1])] ?? `$${match[1]}`);
                }

                index += match[0].length;
                continue;
            }
        }

        // --- Move numbers, results and whitespace ---------------------------
        if (/\s/.test(char)) {
            index += 1;
            continue;
        }

        const numbered = /^\d+\s*\.{1,3}/.exec(moveText.slice(index));

        if (numbered) {
            index += numbered[0].length;
            continue;
        }

        const result = /^(1-0|0-1|1\/2-1\/2|\*)/.exec(moveText.slice(index));

        if (result) {
            index += result[0].length;
            continue;
        }

        // --- A move ---------------------------------------------------------
        const move = /^([KQRBNOa-h][^\s(){};$]*)/.exec(moveText.slice(index));

        if (move) {
            if (depth === 0) {
                const token = move[1];
                const suffix = /([!?]{1,2})$/.exec(token);

                entries.push({
                    san: token,
                    glyph: suffix ? suffix[1] : '',
                    nags: [],
                    comment: null,
                });
            }

            index += move[0].length;
            continue;
        }

        index += 1;
    }

    return { entries, preComment: pendingPreComment };
};

/**
 * @returns {{
 *   ok: boolean,
 *   error?: string,
 *   headers: Record<string,string>,
 *   startFen: string,
 *   preComment: string|null,
 *   moves: Array<{ san: string, glyph: string, nags: string[], comment: string|null,
 *                  fen: string, from: string, to: string, ply: number,
 *                  moveNumber: number, colour: 'w'|'b', check: boolean }>
 * }}
 */
export const parsePgn = (pgn) => {
    const game = new Chess();

    try {
        game.loadPgn(pgn, { strict: false });
    } catch (error) {
        return {
            ok: false,
            error: error instanceof Error ? error.message : 'This PGN could not be read.',
            headers: {},
            startFen: new Chess().fen(),
            preComment: null,
            moves: [],
        };
    }

    const headers = game.getHeaders();
    const verbose = game.history({ verbose: true });
    const { entries, preComment } = readMainLine(stripHeaders(pgn));

    // A FEN header means the game does not start from the initial position.
    const startFen = headers.FEN || (verbose.length ? verbose[0].before : game.fen());

    const moves = verbose.map((move, i) => {
        const annotation = entries[i] ?? { glyph: '', nags: [], comment: null };
        const ply = i + 1;

        return {
            san: move.san,
            glyph: annotation.glyph,
            nags: annotation.nags,
            comment: annotation.comment,
            fen: move.after,
            from: move.from,
            to: move.to,
            ply,
            moveNumber: Math.floor(i / 2) + (startFen.split(' ')[1] === 'b' ? 0 : 1),
            colour: move.color,
            check: /[+#]$/.test(move.san),
        };
    });

    // Recompute move numbers from the starting FEN so games from a position are
    // numbered the way the players wrote them down.
    const startNumber = Number.parseInt(startFen.split(' ')[5] ?? '1', 10) || 1;
    const startsWithBlack = startFen.split(' ')[1] === 'b';

    moves.forEach((move, i) => {
        const offset = startsWithBlack ? i + 1 : i;
        move.moveNumber = startNumber + Math.floor(offset / 2);
    });

    return {
        ok: true,
        headers,
        startFen,
        preComment,
        moves,
    };
};

export { NAG_SYMBOLS };
