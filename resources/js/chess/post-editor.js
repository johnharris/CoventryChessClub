import { Chessground } from 'chessground';
import { Chess } from 'chess.js';
import { parsePgn } from './pgn';

/**
 * The authoring side of chess posts.
 *
 * 1. Post type switching — shows only the fields relevant to the chosen type.
 * 2. FEN editor — a live board that also works the other way round: the author
 *    can drag pieces on the board and the FEN field updates itself.
 * 3. PGN editor — paste text or drop in a .pgn file; the game is validated,
 *    the header tags fill in the form, and a preview board plays it through.
 */

const START_FEN = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
const EMPTY_FEN = '8/8/8/8/8/8/8/8 w - - 0 1';

/* ------------------------------------------------------------------------ */
/* Post type switching                                                      */
/* ------------------------------------------------------------------------ */

const mountTypeSwitcher = () => {
    const inputs = document.querySelectorAll('[data-post-type-input]');

    if (!inputs.length) return;

    const apply = (type) => {
        document.querySelectorAll('[data-post-type-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.postTypePanel !== type;
        });

        document.querySelectorAll('[data-post-type-option]').forEach((option) => {
            const active = option.dataset.postTypeOption === type;

            option.dataset.active = active ? 'true' : 'false';
        });
    };

    inputs.forEach((input) => {
        input.addEventListener('change', () => input.checked && apply(input.value));
    });

    const checked = document.querySelector('[data-post-type-input]:checked');

    apply(checked ? checked.value : 'general');
};

/* ------------------------------------------------------------------------ */
/* FEN editor                                                               */
/* ------------------------------------------------------------------------ */

const mountFenEditor = () => {
    const root = document.querySelector('[data-fen-editor]');

    if (!root) return;

    const boardEl = root.querySelector('[data-fen-board]');
    const input = document.querySelector('[data-fen-input]');
    const feedback = root.querySelector('[data-fen-feedback]');
    const turnInputs = root.querySelectorAll('[data-fen-turn]');
    const orientationInputs = document.querySelectorAll('[data-orientation-input]');

    if (!boardEl || !input) return;

    const chess = new Chess();
    let orientation = document.querySelector('[data-orientation-input]:checked')?.value || 'white';

    const isValid = (fen) => {
        try {
            new Chess(fen);

            return true;
        } catch {
            return false;
        }
    };

    const board = Chessground(boardEl, {
        fen: isValid(input.value) ? input.value : START_FEN,
        orientation: orientation === 'black' ? 'black' : 'white',
        coordinates: true,
        // The author may place pieces anywhere; legality is not enforced while
        // composing a study position.
        movable: { free: true, color: 'both', showDests: false },
        draggable: { enabled: true, deleteOnDropOff: true },
        selectable: { enabled: true },
        highlight: { lastMove: false },
        animation: { enabled: false },
        drawable: { enabled: false, visible: false },
        events: {
            change: () => syncFromBoard(),
        },
    });

    boardEl.removeAttribute('data-board-pending');

    /** Board -> FEN field */
    const syncFromBoard = () => {
        const placement = board.getFen(); // board layout only
        const turn = root.querySelector('[data-fen-turn]:checked')?.value || 'w';
        const castling = deriveCastling(placement);

        input.value = `${placement} ${turn} ${castling} - 0 1`;
        report(input.value);
    };

    /** FEN field -> board */
    const syncFromInput = () => {
        const value = input.value.trim();

        if (!value) {
            report('');

            return;
        }

        const placement = value.split(/\s+/)[0];

        board.set({ fen: placement });

        const turn = value.split(/\s+/)[1];

        if (turn === 'w' || turn === 'b') {
            turnInputs.forEach((i) => { i.checked = i.value === turn; });
        }

        report(value);
    };

    /**
     * Work out plausible castling rights from where the kings and rooks sit, so
     * a hand-built position does not silently claim impossible castling.
     */
    const deriveCastling = (placement) => {
        const ranks = placement.split('/');
        const expand = (rank) =>
            rank
                .split('')
                .flatMap((c) => (/\d/.test(c) ? Array(Number(c)).fill('') : [c]));

        const first = expand(ranks[7] ?? '');
        const eighth = expand(ranks[0] ?? '');

        let rights = '';

        if (first[4] === 'K') {
            if (first[7] === 'R') rights += 'K';
            if (first[0] === 'R') rights += 'Q';
        }

        if (eighth[4] === 'k') {
            if (eighth[7] === 'r') rights += 'k';
            if (eighth[0] === 'r') rights += 'q';
        }

        return rights || '-';
    };

    const report = (fen) => {
        if (!feedback) return;

        if (!fen) {
            feedback.textContent = 'Set up a position on the board, or paste a FEN.';
            feedback.dataset.state = 'neutral';

            return;
        }

        try {
            chess.load(fen);

            const turn = chess.turn() === 'w' ? 'White' : 'Black';
            const checks = [];

            if (chess.isCheckmate()) checks.push('checkmate');
            else if (chess.isCheck()) checks.push('check');
            if (chess.isStalemate()) checks.push('stalemate');

            feedback.textContent = checks.length
                ? `Valid position — ${turn} to move (${checks.join(', ')}).`
                : `Valid position — ${turn} to move.`;
            feedback.dataset.state = 'ok';
        } catch (error) {
            feedback.textContent =
                error instanceof Error
                    ? `Check this position: ${error.message}`
                    : 'This position is not valid.';
            feedback.dataset.state = 'error';
        }
    };

    input.addEventListener('input', syncFromInput);
    input.addEventListener('change', syncFromInput);

    turnInputs.forEach((i) => i.addEventListener('change', syncFromBoard));

    orientationInputs.forEach((i) =>
        i.addEventListener('change', () => {
            if (!i.checked) return;

            orientation = i.value;
            board.set({ orientation: orientation === 'black' ? 'black' : 'white' });
        })
    );

    root.querySelectorAll('[data-fen-preset]').forEach((button) => {
        button.addEventListener('click', () => {
            const preset = button.dataset.fenPreset;

            input.value = preset === 'empty' ? EMPTY_FEN : START_FEN;
            syncFromInput();
        });
    });

    root.querySelector('[data-fen-flip]')?.addEventListener('click', () => {
        board.toggleOrientation();
    });

    report(input.value.trim());
};

/* ------------------------------------------------------------------------ */
/* PGN editor                                                               */
/* ------------------------------------------------------------------------ */

const mountPgnEditor = () => {
    const root = document.querySelector('[data-pgn-editor]');

    if (!root) return;

    const textarea = document.querySelector('[data-pgn-input]');
    const fileInput = root.querySelector('[data-pgn-file]');
    const dropZone = root.querySelector('[data-pgn-drop]');
    const feedback = root.querySelector('[data-pgn-feedback]');
    const previewWrap = root.querySelector('[data-pgn-preview]');

    if (!textarea) return;

    const fill = (selector, value) => {
        const field = document.querySelector(selector);

        if (field && value && !field.value) field.value = value;
    };

    const analyse = () => {
        const pgn = textarea.value.trim();

        if (!pgn) {
            setFeedback('Paste a PGN, or drop a .pgn file onto the box above.', 'neutral');
            previewWrap?.replaceChildren();

            return;
        }

        const game = parsePgn(pgn);

        if (!game.ok) {
            setFeedback(`This PGN could not be read: ${game.error}`, 'error');
            previewWrap?.replaceChildren();

            return;
        }

        if (!game.moves.length) {
            setFeedback('That PGN contains no moves.', 'error');

            return;
        }

        const { headers } = game;

        fill('[name="white_player"]', headers.White);
        fill('[name="black_player"]', headers.Black);
        fill('[name="event"]', headers.Event);

        if (headers.Result) {
            const result = document.querySelector('[name="result"]');

            if (result && !result.value) result.value = headers.Result;
        }

        if (headers.Date) {
            const match = /^(\d{4})\.(\d{2}|\?\?)\.(\d{2}|\?\?)/.exec(headers.Date);

            if (match) {
                const month = match[2] === '??' ? '01' : match[2];
                const day = match[3] === '??' ? '01' : match[3];

                fill('[name="played_on"]', `${match[1]}-${month}-${day}`);
            }
        }

        const title = document.querySelector('[name="title"]');

        if (title && !title.value && headers.White && headers.Black) {
            title.value = `${headers.White} vs ${headers.Black}`;
        }

        const annotated = game.moves.filter((m) => m.comment).length;

        setFeedback(
            `Valid game — ${game.moves.length} moves` +
                (annotated ? `, ${annotated} annotated` : '') +
                (headers.Result ? `, result ${headers.Result}` : '') +
                '.',
            'ok'
        );

        renderPreview(pgn);
    };

    const renderPreview = (pgn) => {
        if (!previewWrap) return;

        // Reuse the real viewer so the author sees exactly what readers will see.
        previewWrap.replaceChildren();

        const template = document.querySelector('[data-pgn-preview-template]');

        if (!template) return;

        const clone = template.content.cloneNode(true);
        const viewer = clone.querySelector('[data-game-viewer]');

        if (viewer) viewer.dataset.pgn = pgn;

        previewWrap.append(clone);

        import('./game-viewer').then(({ mountGameViewers }) => mountGameViewers());
    };

    const setFeedback = (message, state) => {
        if (!feedback) return;

        feedback.textContent = message;
        feedback.dataset.state = state;
    };

    const readFile = (file) => {
        if (!file) return;

        if (!/\.(pgn|txt)$/i.test(file.name)) {
            setFeedback('Please choose a .pgn file.', 'error');

            return;
        }

        const reader = new FileReader();

        reader.addEventListener('load', () => {
            textarea.value = String(reader.result ?? '').trim();
            analyse();
        });

        reader.readAsText(file);
    };

    let debounce;

    textarea.addEventListener('input', () => {
        window.clearTimeout(debounce);
        debounce = window.setTimeout(analyse, 400);
    });

    fileInput?.addEventListener('change', (event) => readFile(event.target.files?.[0]));

    if (dropZone) {
        ['dragenter', 'dragover'].forEach((type) =>
            dropZone.addEventListener(type, (event) => {
                event.preventDefault();
                dropZone.dataset.dragging = 'true';
            })
        );

        ['dragleave', 'drop'].forEach((type) =>
            dropZone.addEventListener(type, (event) => {
                event.preventDefault();
                delete dropZone.dataset.dragging;
            })
        );

        dropZone.addEventListener('drop', (event) => readFile(event.dataTransfer?.files?.[0]));
    }

    analyse();
};

export const mountPostEditor = () => {
    mountTypeSwitcher();
    mountFenEditor();
    mountPgnEditor();
};
