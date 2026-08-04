# Coventry Chess Club

The website for Coventry Chess Club — a blog-style site where members publish club news alongside annotated games and positions, rendered on lichess's own chess board.

Built with **Laravel 13** and **PHP 8.3**, with boards by **Chessground** (the board component from lichess.org) and rules by **chess.js**.

---

## What the site does

**For visitors**, it is a club website: news and fixtures on the blog, standing pages for teams, coaching and the junior section, a contact form, and details of where and when the club meets. Annotated games can be played through move by move, and positions appear as proper diagrams rather than photographs of a board.

**For members**, there is a private area for writing three kinds of post:

| Post type | What you provide | What readers get |
|---|---|---|
| **General post** | A title and some text | An article, which may still contain boards (see below) |
| **Chess position** | A FEN, or pieces dragged onto a board | A diagram with an optional caption and a hidden answer |
| **Annotated game** | A PGN, pasted or dropped in as a file | A board readers step through, with your `{comments}` shown beside each move |

Any post can also contain a board part-way through the text, by adding a fenced block:

````text
```fen
r1bqkbnr/pppp1ppp/2n5/4p3/2B1P3/5N2/PPPP1PPP/RNBQK2R b KQkq - 5 4
Caption: How should Black meet the threat?
Orientation: black
Solution: 4...Nf6 keeps the balance.
```
````

**For administrators**, there is additionally the whitelist and account management, the enquiry inbox, and control of the standing pages and the navigation menu.

---

## How access works

There is **no public sign-up**. Accounts exist only for people an administrator has invited:

1. An administrator adds a member's email address on the *Members* screen and chooses **Club member** or **Administrator**.
2. The site produces a single-use invitation link, which the administrator sends to the member.
3. The member follows the link and chooses their own password.

Members may write, edit and delete their own posts. Administrators may do all of that for anybody, plus feature posts on the home page, manage accounts, edit the standing pages and read enquiries. Suspending an account revokes access on that person's very next request.

---

## Running it locally

You need PHP 8.3 or newer, Composer and Node.js.

```bash
git clone https://github.com/johnharris/CoventryChessClub.git
cd CoventryChessClub

composer install
pnpm install                  # or: npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed    # creates the tables, content and first admin

pnpm run build                # or: npm run build
php artisan serve
```

The site is then at **http://127.0.0.1:8000**.

The seeder creates two accounts for development. **Change these before going anywhere near a live server** — see `DEPLOYMENT.md`.

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@coventrychessclub.test` | `password` |
| Club member | `member@coventrychessclub.test` | `password` |

### Working on the front end

```bash
pnpm run dev     # rebuilds CSS and JS as you edit
```

### Running the tests

```bash
php artisan test
```

Thirty-nine tests cover the whitelist gate, authentication, role permissions, all three post types with FEN and PGN validation, the contact form, and the chess notation helpers.

---

## Where things live

```
app/
├── Http/Controllers/     Posts, pages, enquiries, members, authentication
├── Http/Middleware/      EnsureUserIsAdmin, EnsureUserIsActive
├── Models/               User, Post, Page, Enquiry, WhitelistEntry
├── Policies/             PostPolicy — who may edit what
├── Rules/                ValidFen, ValidPgn
└── Support/              ChessNotation (FEN/PGN), Markdown (post bodies)

resources/
├── css/app.css           Tailwind, the club palette, lichess board styles
├── js/chess/             static-board, pgn, game-viewer, post-editor
├── js/ui/                navigation
└── views/                Blade templates

config/club.php           Club name, meeting times, venue, league links
database/                 Migrations and seeders
tests/Feature/            The test suite
```

`config/club.php` is worth knowing about: the club's name, meeting times, venue address and league links live there and can be changed through `.env` without editing any template.

---

## Documentation

| Document | Contents |
|---|---|
| **`BUILD_STEPS.md`** | How the site was built, step by step, with every command and the reasoning behind each decision |
| **`DEPLOYMENT.md`** | Putting the site live: choosing a host, uploading, HTTPS, backups, troubleshooting |

---

## Planned next

A **Swiss tournament module** is the intended next addition: entering players, generating pairings round by round, recording results and publishing a live cross-table. The database and permission model were designed with this in mind, so it can be added without disturbing the blog.

---

## Licensing and credits

The club's own code, content and design belong to the club. Two dependencies deserve particular acknowledgement:

- **[Chessground](https://github.com/lichess-org/chessground)** — the board component, by the lichess.org team, licensed **GPL-3.0**. Because the site uses it, the GPL's terms apply to the distributed source, and the licence notice must be kept intact. Chessground is credited in the footer of every page.
- **[chess.js](https://github.com/jhlywa/chess.js)** — FEN and PGN handling and move generation, licensed **BSD-2-Clause**.

The cburnett piece set is by Colin M. L. Burnett, licensed CC-BY-SA 3.0, and is embedded within Chessground's stylesheet.
