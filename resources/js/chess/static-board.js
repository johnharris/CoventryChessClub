import { Chessground } from 'chessground';
import { Chess } from 'chess.js';

/**
 * Renders every `[data-chess-board]` element as a read-only lichess board.
 *
 * Expected markup (produced by resources/views/partials/*-board.blade.php):
 *
 *   <div class="board-frame" data-chess-board
 *        data-fen="..." data-orientation="white" data-last-move="e2e4"></div>
 *
 * Boards are only initialised when they scroll into view, so a blog index full
 * of diagrams stays fast on a phone.
 */

/**
 * Which colour, if any, is in check in this position.
 *
 * Chessground will accept `check: true`, but that means "whoever `turnColor`
 * says is to move" — and `turnColor` defaults to white and is *not* read from the
 * FEN. Passing a boolean therefore highlights the white king whoever is really
 * in check. Always pass an explicit colour.
 */
const checkedColour = (fen) => {
    try {
        const chess = new Chess(fen);

        if (!chess.inCheck()) return false;

        return chess.turn() === 'w' ? 'white' : 'black';
    } catch {
        // An unusual but drawable position (a puzzle with no kings, say) is not
        // worth losing the diagram over.
        return false;
    }
};

const turnColour = (fen) => (fen.split(' ')[1] === 'b' ? 'black' : 'white');

const parseLastMove = (value) => {
    if (!value) return undefined;

    const from = value.slice(0, 2);
    const to = value.slice(2, 4);

    return /^[a-h][1-8]$/.test(from) && /^[a-h][1-8]$/.test(to) ? [from, to] : undefined;
};

export const createStaticBoard = (element) => {
    const fen = element.dataset.fen;

    if (!fen) return null;

    const api = Chessground(element, {
        fen,
        orientation: element.dataset.orientation === 'black' ? 'black' : 'white',
        coordinates: element.dataset.coordinates !== 'false',
        viewOnly: true,
        disableContextMenu: true,
        lastMove: parseLastMove(element.dataset.lastMove),
        turnColor: turnColour(fen),
        check: checkedColour(fen),
        highlight: { lastMove: true, check: true },
        animation: { enabled: false },
        drawable: { enabled: false, visible: false },
    });

    element.removeAttribute('data-board-pending');

    return api;
};

export const mountStaticBoards = () => {
    const boards = document.querySelectorAll('[data-chess-board]:not([data-board-ready])');

    if (!boards.length) return;

    const activate = (element) => {
        if (element.dataset.boardReady) return;

        element.dataset.boardReady = 'true';
        createStaticBoard(element);
    };

    // Without IntersectionObserver support, just draw them all.
    if (!('IntersectionObserver' in window)) {
        boards.forEach(activate);

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                activate(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '200px 0px' }
    );

    boards.forEach((board) => observer.observe(board));
};
