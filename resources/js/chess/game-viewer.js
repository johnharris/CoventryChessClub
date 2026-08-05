import { Chessground } from 'chessground';
import { parsePgn } from './pgn';

/**
 * Interactive annotated game viewer, modelled on lichess's study/analysis layout:
 * a board on the left, a two-column move list on the right, first/back/forward/last
 * controls, keyboard arrows, board flip, and the annotation for the current move.
 *
 * Expected markup (resources/views/partials/game-viewer.blade.php):
 *
 *   <div data-game-viewer data-pgn="...">
 *     <div data-viewer-board class="board-frame"></div>
 *     <div data-viewer-moves></div>
 *     <p  data-viewer-comment></p>
 *     ... buttons with data-viewer-action="first|prev|next|last|flip|autoplay"
 *   </div>
 */

const AUTOPLAY_MS = 1400;

/**
 * Whose turn it is in a position, as chessground names colours.
 *
 * Chessground does not read the active colour from the FEN — `turnColor` stays at
 * its default of white unless told otherwise — so it has to be set explicitly
 * every time the position changes. Getting this wrong puts the check highlight on
 * the wrong king, because `check: true` means "whoever is to move".
 */
const turnColour = (fen) => (fen.split(' ')[1] === 'b' ? 'black' : 'white');

class GameViewer {
    constructor(root) {
        this.root = root;
        this.boardEl = root.querySelector('[data-viewer-board]');
        this.movesEl = root.querySelector('[data-viewer-moves]');
        this.commentEl = root.querySelector('[data-viewer-comment]');
        this.statusEl = root.querySelector('[data-viewer-status]');
        this.fenEl = root.querySelector('[data-viewer-fen]');
        this.errorEl = root.querySelector('[data-viewer-error]');

        this.index = -1; // -1 is the starting position
        this.autoplayTimer = null;

        const pgn = root.dataset.pgn ?? '';
        this.game = parsePgn(pgn);

        if (!this.game.ok || this.game.moves.length === 0) {
            this.showError(
                this.game.error ||
                    'This game could not be replayed automatically. The moves are listed below.'
            );

            return;
        }

        this.orientation = root.dataset.orientation === 'black' ? 'black' : 'white';

        this.board = Chessground(this.boardEl, {
            fen: this.game.startFen,
            turnColor: turnColour(this.game.startFen),
            orientation: this.orientation,
            viewOnly: true,
            coordinates: true,
            disableContextMenu: true,
            highlight: { lastMove: true, check: true },
            animation: { enabled: true, duration: 200 },
            drawable: { enabled: false, visible: false },
        });

        this.boardEl.removeAttribute('data-board-pending');

        this.renderMoves();
        this.bindControls();
        this.goTo(-1);

        this.root.dataset.viewerReady = 'true';
    }

    showError(message) {
        if (this.errorEl) {
            this.errorEl.textContent = message;
            this.errorEl.hidden = false;
        }

        // Leave the server-rendered move text in place as the fallback.
        this.root.dataset.viewerFailed = 'true';
    }

    /* ------------------------------------------------------------------ */

    renderMoves() {
        if (!this.movesEl) return;

        const fragment = document.createDocumentFragment();
        const { moves } = this.game;

        let i = 0;

        // Black to move first: pad the white column with an ellipsis.
        if (moves.length && moves[0].colour === 'b') {
            fragment.append(this.numberCell(moves[0].moveNumber));
            fragment.append(this.emptyCell());
            fragment.append(this.moveButton(moves[0], 0));

            if (moves[0].comment) fragment.append(this.commentRow(moves[0].comment));

            i = 1;
        }

        while (i < moves.length) {
            const white = moves[i];
            const black = moves[i + 1];

            fragment.append(this.numberCell(white.moveNumber));
            fragment.append(this.moveButton(white, i));

            if (black) {
                fragment.append(this.moveButton(black, i + 1));
            } else {
                fragment.append(this.emptyCell());
            }

            if (white.comment) fragment.append(this.commentRow(white.comment));
            if (black?.comment) fragment.append(this.commentRow(black.comment));

            i += 2;
        }

        this.movesEl.replaceChildren(fragment);
        this.moveButtons = Array.from(this.movesEl.querySelectorAll('[data-move-index]'));
    }

    numberCell(number) {
        const cell = document.createElement('div');
        cell.className = 'move-number';
        cell.textContent = `${number}.`;

        return cell;
    }

    emptyCell() {
        const cell = document.createElement('div');
        cell.className = 'move is-empty';
        cell.textContent = '…';

        return cell;
    }

    moveButton(move, index) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'move';
        button.dataset.moveIndex = String(index);
        button.textContent = move.san;

        if (move.nags.length) {
            const nag = document.createElement('span');
            nag.className = 'nag';
            nag.textContent = move.nags.join('');
            button.append(nag);
        }

        button.addEventListener('click', () => {
            this.stopAutoplay();
            this.goTo(index);
        });

        return button;
    }

    commentRow(text) {
        const row = document.createElement('div');
        row.className = 'move-comment';
        row.textContent = text;

        return row;
    }

    /* ------------------------------------------------------------------ */

    bindControls() {
        this.root.querySelectorAll('[data-viewer-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.dataset.viewerAction;

                if (action !== 'autoplay') this.stopAutoplay();

                switch (action) {
                    case 'first':
                        this.goTo(-1);
                        break;
                    case 'prev':
                        this.goTo(this.index - 1);
                        break;
                    case 'next':
                        this.goTo(this.index + 1);
                        break;
                    case 'last':
                        this.goTo(this.game.moves.length - 1);
                        break;
                    case 'flip':
                        this.flip();
                        break;
                    case 'autoplay':
                        this.toggleAutoplay(button);
                        break;
                    default:
                        break;
                }
            });
        });

        // Arrow keys work once the viewer has been focused or clicked.
        this.root.setAttribute('tabindex', '0');
        this.root.addEventListener('keydown', (event) => {
            const keys = {
                ArrowLeft: () => this.goTo(this.index - 1),
                ArrowRight: () => this.goTo(this.index + 1),
                ArrowUp: () => this.goTo(-1),
                ArrowDown: () => this.goTo(this.game.moves.length - 1),
                f: () => this.flip(),
            };

            const handler = keys[event.key];

            if (!handler) return;

            event.preventDefault();
            this.stopAutoplay();
            handler();
        });
    }

    flip() {
        this.orientation = this.orientation === 'white' ? 'black' : 'white';
        this.board.set({ orientation: this.orientation });
    }

    toggleAutoplay(button) {
        if (this.autoplayTimer) {
            this.stopAutoplay();

            return;
        }

        button.dataset.playing = 'true';
        button.setAttribute('aria-pressed', 'true');

        this.autoplayTimer = window.setInterval(() => {
            if (this.index >= this.game.moves.length - 1) {
                this.stopAutoplay();

                return;
            }

            this.goTo(this.index + 1);
        }, AUTOPLAY_MS);
    }

    stopAutoplay() {
        if (this.autoplayTimer) {
            window.clearInterval(this.autoplayTimer);
            this.autoplayTimer = null;
        }

        this.root.querySelectorAll('[data-viewer-action="autoplay"]').forEach((button) => {
            delete button.dataset.playing;
            button.setAttribute('aria-pressed', 'false');
        });
    }

    /* ------------------------------------------------------------------ */

    goTo(index) {
        const { moves, startFen, preComment } = this.game;
        const clamped = Math.max(-1, Math.min(index, moves.length - 1));

        this.index = clamped;

        const current = clamped >= 0 ? moves[clamped] : null;
        const fen = current ? current.fen : startFen;

        // The side in check after a checking move is the side now to move, i.e.
        // the mover's opponent. Pass that colour explicitly: a bare `true` would
        // be resolved against chessground's `turnColor`, which it never derives
        // from the FEN, so every check would land on the white king.
        const toMove = turnColour(fen);

        this.board.set({
            fen,
            turnColor: toMove,
            lastMove: current ? [current.from, current.to] : undefined,
            check: current?.check ? toMove : false,
        });

        this.moveButtons?.forEach((button) => {
            button.classList.toggle('is-current', Number(button.dataset.moveIndex) === clamped);
        });

        // Keep the active move visible in a long scrolling move list.
        const active = this.moveButtons?.[clamped];

        if (active && this.movesEl.scrollHeight > this.movesEl.clientHeight) {
            active.scrollIntoView({ block: 'nearest' });
        }

        if (this.commentEl) {
            const comment = current ? current.comment : preComment;

            this.commentEl.textContent = comment ?? '';
            this.commentEl.hidden = !comment;
        }

        if (this.statusEl) {
            this.statusEl.textContent = current
                ? `Move ${clamped + 1} of ${moves.length} — ${current.moveNumber}${
                      current.colour === 'w' ? '.' : '…'
                  } ${current.san}`
                : 'Starting position';
        }

        if (this.fenEl) {
            this.fenEl.value = current ? current.fen : startFen;
        }

        this.root.querySelectorAll('[data-viewer-action="prev"], [data-viewer-action="first"]')
            .forEach((b) => { b.disabled = clamped === -1; });
        this.root.querySelectorAll('[data-viewer-action="next"], [data-viewer-action="last"]')
            .forEach((b) => { b.disabled = clamped >= moves.length - 1; });
    }
}

export const mountGameViewers = () => {
    document.querySelectorAll('[data-game-viewer]:not([data-viewer-ready])').forEach((root) => {
        try {
            new GameViewer(root);
        } catch (error) {
            console.error('Game viewer failed to start', error);
        }
    });
};

export { GameViewer };
