/**
 * Front-end entry point.
 *
 * Everything chess-related is progressive: the server always renders the FEN,
 * the move list and the annotations as plain HTML first, and these modules then
 * upgrade them into interactive lichess-style boards.
 */

import { mountStaticBoards } from './chess/static-board';
import { mountGameViewers } from './chess/game-viewer';
import { mountPostEditor } from './chess/post-editor';
import { mountNavigation } from './ui/navigation';

const boot = () => {
    mountNavigation();
    mountStaticBoards();
    mountGameViewers();
    mountPostEditor();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
