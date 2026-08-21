# Coventry Chess Club — How This Site Was Built

**Author:** Manus AI
**Date:** 4 August 2026
**Stack:** Laravel 13.23 · PHP 8.3 · Tailwind CSS 4 · Chessground (lichess) · chess.js · SQLite or MySQL

---

## Purpose of this document

This is a complete, ordered record of how the Coventry Chess Club website was built, written so that the club can rebuild it from nothing, understand any part of it, or hand it to a different developer without loss of context. Every command is reproduced exactly as it was run, and every significant design decision is explained rather than merely stated.

The companion documents are `DEPLOYMENT.md`, which covers putting the site on a live server, and `README.md`, which is the short orientation for anyone opening the repository for the first time.

---

## Part 1 — Preparing the environment

Laravel is a PHP framework, so the first requirement is a PHP toolchain. The site was built on Ubuntu 24.04 with PHP 8.3, which is the minimum Laravel 13 supports.

```bash
sudo apt-get update
sudo apt-get install -y php-cli php-mbstring php-xml php-curl php-zip \
                        php-sqlite3 php-mysql php-bcmath php-gd unzip
```

Each extension earns its place: `mbstring` and `xml` are hard requirements of the framework, `curl` and `zip` are needed by Composer, `sqlite3` and `mysql` are the two database drivers the site supports, `bcmath` is used by Laravel's internals, and `gd` handles images should the club later add photographs.

Composer, PHP's dependency manager, was then installed globally:

```bash
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version    # Composer version 2.10.2
```

Node.js 22 was already present and is used only at build time, to compile the CSS and JavaScript. **A live server does not need Node.js**, because the compiled assets are committed to the repository. This matters for cheap shared hosting, where Node is often unavailable.

> Throughout this document, `pnpm` is used because it was available in the build environment. `npm` behaves identically — substitute `npm install` for `pnpm install` and `npm run build` for `pnpm run build`.

---

## Part 2 — Creating the application

```bash
cd ~
composer create-project laravel/laravel coventry-chess-club
cd coventry-chess-club
php artisan --version    # Laravel Framework 13.23.0
```

Laravel's installer writes a `.env` configuration file, generates the application encryption key, creates `database/database.sqlite`, and runs the framework's initial migrations for users, sessions, cache and queued jobs.

The default database is **SQLite**, which is a single file requiring no server process, no username and no password. For a club site with a few hundred visitors a week this is entirely adequate and removes an entire category of hosting problems. Moving to MySQL later is a change to four lines of `.env` and nothing else, because Laravel's query builder abstracts the difference; `DEPLOYMENT.md` gives the exact values.

---

## Part 3 — Choosing the chess libraries

This is the decision that most affects how the site feels, so it deserves explanation.

The brief asked for chess posts that look like lichess. Rather than imitate lichess's appearance, the site uses **lichess's actual board component**:

```bash
pnpm install
pnpm add chessground chess.js
```

| Library | Licence | Role |
|---|---|---|
| **chessground** | GPL-3.0 | lichess.org's own board renderer. Provides the exact board geometry, the `#f0d9b5` / `#b58863` brown squares, the cburnett piece set, coordinate labels, last-move highlighting and drag behaviour that lichess users recognise instantly. |
| **chess.js** | BSD-2-Clause | The rules engine. Validates FEN strings, parses PGN files, generates legal moves, and produces the position after each move — this is what turns a static PGN into a game a reader can step through. |

Chessground embeds its piece images as base64 data URIs inside its stylesheet, so there are no separate sprite files to lose during deployment, and the pieces render even if the server is misconfigured for static assets.

Because Chessground is GPL-3.0, the club must keep the licence notice intact and, if the site's source is distributed, make the corresponding source available. The site credits Chessground in the footer of every page, which is both good manners and good practice.

---

## Part 4 — Designing the database

Five migrations describe the whole application:

```bash
php artisan make:migration add_role_and_profile_to_users_table
php artisan make:migration create_whitelist_entries_table
php artisan make:migration create_posts_table
php artisan make:migration create_pages_table
php artisan make:migration create_enquiries_table
php artisan migrate
```

### The tables

**`users`** extends Laravel's default table with a `role` column holding either `admin` or `member`, an `is_active` flag for suspending an account without deleting its posts, and chess-specific profile fields (`display_name`, `ecf_code`, `ecf_rating`, `bio`).

**`whitelist_entries`** is the gate on registration and the heart of the access model. Each row records an `email`, the `role` that address will receive, a single-use `invite_token`, a `claimed_at` timestamp, and foreign keys recording who issued the invitation and which account claimed it. Keeping the intended role on the invitation means an administrator decides someone's permissions *before* the account exists, rather than creating an account and then adjusting it.

**`posts`** uses a single table with a `type` column rather than three separate tables, because the three kinds of post share almost everything — title, slug, author, body, publication state — and differ only in a handful of chess fields:

| `type` | Purpose | Fields it uses |
|---|---|---|
| `general` | Club news, fixtures, results, articles | title, body |
| `position` | One diagram from a FEN | `fen`, `orientation`, `side_to_move`, `caption`, `solution` |
| `game` | A full annotated game from a PGN | `pgn`, `white_player`, `black_player`, `result`, `event`, `played_on`, `orientation` |

Publication is governed by three columns working together: `is_published` is the author's intent, `published_at` is when it becomes visible (a future value schedules it), and `is_featured` pins a post to the top of the home page. A post is public only when `is_published` is true *and* `published_at` has passed.

**`pages`** holds the club's standing pages — Fixtures, Teams, 1-1 Coaching, Juniors, Fun Stuff, Useful Links — with `show_in_nav` and `nav_order` so an administrator controls the navigation menu from the site itself, with no code change.

**`enquiries`** stores every contact form submission with `is_read` and `is_archived` flags. Storing enquiries in the database rather than only emailing them means nothing is ever lost to a spam filter or a mail server misconfiguration, and the club has a searchable history.

---

## Part 5 — The Eloquent models

The models are where the domain rules live, so that controllers stay short and the same rule is never written twice.

`User` exposes the permission model as three readable methods: `isAdmin()`, `isMember()` and `ownsOrAdmin($post)`. Anywhere the code asks "may this person do this?", it reads as a sentence.

`Post` carries query scopes — `published()`, `newestFirst()`, `ofType()`, `search()` — which compose freely, so a controller can write `Post::published()->ofType('game')->newestFirst()->paginate(12)` and the intent is obvious. It also owns `uniqueSlug()`, which turns "End of season dates" into `end-of-season-dates` and appends `-2` if that URL is taken.

`WhitelistEntry::unclaimedFor($email)` is the single function that decides whether a registration attempt may proceed. Concentrating that decision in one place means the whitelist cannot be bypassed by a second code path appearing later.

---

## Part 6 — Whitelist-only authentication

```bash
php artisan make:controller Auth/LoginController
php artisan make:controller Auth/RegisterController
php artisan make:middleware EnsureUserIsAdmin
```

### How registration is gated

There is deliberately **no open sign-up**. A registration attempt succeeds only if the email address matches an *unclaimed* row in `whitelist_entries`, or the visitor arrived by a valid invitation link. On success the entry is marked claimed, its token is destroyed so the link cannot be reused, and the new account inherits the role the administrator chose when whitelisting the address.

The practical flow for the club is therefore:

1. An administrator enters a member's email on the Members screen and picks Club member or Administrator.
2. The site generates an invitation link, which the administrator copies and sends to the member by email or hands over on a club night.
3. The member follows the link, chooses their own password, and is signed in immediately.

The member chooses their own password, so no temporary password is ever transmitted or needs changing afterwards.

### Two middleware, two concerns

`EnsureUserIsAdmin` returns HTTP 403 unless the signed-in user's role is `admin`, and guards every administrative route. `EnsureUserIsActive` signs out a suspended account on its very next request, so revoking access takes effect at once rather than whenever the session happens to expire. Both are registered as route aliases in `bootstrap/app.php`, which is also where guests are redirected to the members' login page.

`LoginController` rate limits sign-in attempts to five per minute per email-and-IP combination, regenerates the session identifier on success to defeat session fixation, and refuses suspended accounts with a clear message.

---

## Part 7 — Teaching the server about chess

`app/Support/ChessNotation.php` is the single place the server understands chess notation. Its most important quality is that it explains itself to the member who made a mistake.

`fenError()` validates a FEN rank by rank and returns a specific, human message — *"Rank 5 describes 9 squares, but every rank must describe exactly 8"* — rather than a generic failure. It also insists on exactly one king of each colour, because a position with no king cannot be displayed sensibly. `normaliseFen()` then completes any omitted trailing fields so that a stored FEN is always safe to hand to the board.

`pgnHeaders()`, `pgnMoveText()`, `pgnDate()` and `parseGame()` read a PGN's `[White "..."]` tags, separate the header block from the moves, and extract the `{annotations}`. This is what allows the post editor to fill in the players, event, result and date automatically when a member pastes a PGN — the member types nothing that the file already contains. `pgnDate()` copes with the partial dates common in historical games, so `1858.??.??` becomes 1 January 1858 rather than an error.

`pgnError()` rejects paste accidents: a file with headers but no moves, no numbered moves at all, or unbalanced `{}` or `()`. Full legality checking is left to chess.js in the browser, which reports problems live in the editor preview — the server's job is to catch the obvious mistakes, not to duplicate a rules engine.

These two functions are wrapped as Laravel validation rules in `app/Rules/ValidFen.php` and `app/Rules/ValidPgn.php`, so they can be used in a validation array like any built-in rule.

### Boards inside ordinary articles

`app/Support/Markdown.php` renders post bodies using `league/commonmark`:

```bash
composer require league/commonmark
```

Beyond ordinary Markdown it recognises a fenced block that becomes a full board in the middle of any article, including a general news post:

````text
```fen
r1bqkbnr/pppp1ppp/2n5/4p3/2B1P3/5N2/PPPP1PPP/RNBQK2R b KQkq - 5 4
Caption: How should Black meet the threat?
Orientation: black
Solution: 4...Nf6 keeps the balance.
```
````

The `Caption`, `Orientation` and `Solution` lines are all optional. CommonMark is configured with `html_input => escape`, so anything a member pastes is neutralised and cannot break the page or inject a script.

---

## Part 8 — Controllers, policy and routes

`PostController` serves both the public blog — with type filtering, search and pagination — and the members' authoring screens. Its validation rules switch on post type, so a FEN is required for a position, a PGN for a game, and neither for a general post. A private `withDerivedFields()` method fills in what can be inferred: the publication timestamp, the side to move taken from the FEN, and any PGN header the author left blank.

`PostPolicy` encodes the permission model in one file: members may edit and delete only their own posts, administrators may act on anybody's, and only administrators may feature a post on the home page.

`MemberController` holds the dashboard, profile and password screens, plus administration of the whitelist and accounts. It contains two deliberate safeguards: an administrator cannot remove their own access, and the site refuses to be left with no active administrator.

`EnquiryController` handles the public contact form and the administrators' inbox. The form is rate limited to three submissions per ten minutes per IP address and carries a honeypot field that real visitors never see. Email is attempted only when `CLUB_ENQUIRY_EMAIL` is configured, and a mail failure is logged without ever losing the enquiry.

`PageController` serves the home page and the standing pages, together with their administration screens.

`config/club.php` keeps the club's name, meeting times, venue address and league links out of the code, so they can be changed through `.env` without editing a template.

Finally, `routes/web.php` places the catch-all page-slug route **last**, so that `/blog`, `/contact` and `/members` can never be shadowed by a page whose slug happens to collide. `php artisan route:list` confirms 36 application routes.

---

## Part 9 — The front end

### Styling

`resources/css/app.css` imports Tailwind CSS 4 and then lichess's three board stylesheets — `chessground.base.css`, `chessground.brown.css` and `chessground.cburnett.css` — so the diagrams use lichess's exact colours and pieces. On top of that it defines the club's own design system:

- a **club palette** built around a deep board green, registered as Tailwind theme colours so utilities such as `bg-club-700` work everywhere;
- **`.board-frame`**, a square container using `aspect-ratio: 1 / 1`. This single rule is what makes every diagram scale correctly from a wide desktop down to a narrow phone with no JavaScript resizing at all;
- a **checkerboard placeholder** drawn in pure CSS, so the page never jumps while a board's JavaScript initialises;
- **`.move-list`**, the lichess-style numbered two-column notation;
- **`.prose-club`**, the typography for post bodies, in which wide tables scroll rather than squash on a phone.

### JavaScript

The JavaScript is deliberately split into small modules with one responsibility each:

| File | Responsibility |
|---|---|
| `app.js` | Entry point; boots the modules below once the DOM is ready |
| `chess/static-board.js` | Draws read-only FEN diagrams, lazily via `IntersectionObserver`, so a page full of diagrams stays fast on a phone |
| `chess/pgn.js` | Parses a PGN into a list of positions, preserving the `{comments}`, `$NAG` codes and `!`/`?` glyphs that chess.js discards, and skipping variations from the main line |
| `chess/game-viewer.js` | The annotated game viewer: board, clickable move list, first/back/forward/last, flip, autoplay and arrow-key navigation |
| `chess/post-editor.js` | Authoring: post-type switching, a two-way FEN editor in which dragging a piece updates the FEN and vice versa, and PGN paste or drag-and-drop with live validation and preview |
| `ui/navigation.js` | Mobile menu, account dropdown, delete confirmations, copy-to-clipboard |

Everything is **progressive**. The server always renders the FEN, the move text and the annotations as plain HTML, and the JavaScript upgrades that into an interactive board. If a script fails or is blocked, the content remains readable — which also means search engines index the games rather than an empty container.

---

## Part 10 — The Blade views

The templates are organised as three layouts, a set of small reusable components, and one view per screen.

`layouts/app.blade.php` is the public shell, setting page titles, meta descriptions, Open Graph tags and canonical URLs. `layouts/guest.blade.php` is the narrower shell for the login and registration screens. `layouts/members.blade.php` extends the public layout and adds the members' tab bar, so a signed-in member never loses the site's navigation.

Three chess partials are reused across the whole site: `partials/static-board.blade.php` for a read-only diagram, `partials/inline-board.blade.php` for a captioned diagram with an optional hidden answer (used by the ```` ```fen ```` blocks), and `partials/game-viewer.blade.php` for the full viewer with board, move list, controls, current annotation, live FEN box and a "Full notation" fallback.

On the public side, `home.blade.php` presents a hero with a real board, the featured post, recent posts, the latest positions and games, and the joining and venue sections. `posts/index.blade.php` adds filter pills by type and a search box. `posts/show.blade.php` renders all three post types, including the game header table and a link out to the lichess analysis board. `pages/show.blade.php` and `contact.blade.php` complete the set.

In the members' area there is a dashboard with counts and shortcuts, a posts list that becomes a card list on phones, the shared post editor, the profile screen, and the administration screens for the whitelist and accounts, the enquiry inbox and the pages.

The single post editor form, `members/posts/_form.blade.php`, is shared by the create and edit screens and switches its panels according to the selected post type, so an author only ever sees the fields that apply to what they are writing.

---

## Part 11 — Seeding the club's content

Two seeders populate a working site immediately. `PageSeeder` creates the six standing pages with the content migrated from the club's existing Blogspot site, and `PostSeeder` creates the news posts along with two demonstration chess posts — a rook endgame position and Morphy's Opera House game, fully annotated — so that both chess post types can be seen working from the first minute.

`DatabaseSeeder` creates the first administrator, which must be done by a seeder rather than through the site, because registration is gated on the whitelist and only an administrator can add to the whitelist. Its credentials are read from the environment so that a production installation never uses the documented development password:

```bash
php artisan migrate:fresh --seed
```

---

## Part 12 — Testing

The suite is written with Pest, installed as a development dependency:

```bash
composer require pestphp/pest pestphp/pest-plugin-laravel --dev
php artisan test
```

Ninety-six tests make 306 assertions across ten areas:

| Area | What is proved |
|---|---|
| Public pages | The home page, blog and club pages render; drafts and future-dated posts stay hidden; an author can preview their own draft |
| Whitelist registration | A non-whitelisted address is refused; a whitelisted one succeeds and claims its invitation; the invited role is granted; an invitation cannot be reused |
| Authentication | Correct passwords sign in, wrong ones fail, suspended accounts are refused, guests are redirected |
| Access control | Members are kept out of every administrative screen; administrators are let in |
| Authoring | All three post types save; invalid FENs and PGNs are rejected; members cannot edit others' posts; administrators can; slugs stay unique |
| Contact form | Enquiries are stored; validation, the honeypot and the rate limit all work; administrators can read and archive |
| Chess helpers | FEN validation and its error messages, side to move, FEN normalisation, PGN header and annotation extraction, PGN date conversion |
| Permissions in detail | Every combination of member and administrator against every post action; that an administrator editing a post does not become its author; that a member cannot read another member's draft |
| Images | Uploads succeed and are resized; non-images and oversized files are refused; guests cannot upload; the Markdown written into a post body is root-relative |
| Puzzle answers | The answer is present in the page but inside the collapsed element, never printed openly |
| Contact acknowledgement | Both emails are dispatched, addressed correctly, and a failure of either does not lose the enquiry |

---

## Part 13 — Photographs

Members can upload photographs from the post editor — by file, by dragging onto the text box, or by pasting — and from a standalone image library.

Each upload is processed by `App\Support\ImageProcessor` using PHP's GD extension, which is available on essentially every host. Three versions are produced: a full-size version capped at 2400 px, a display version at 1200 px, and a thumbnail at 400 px. This matters more than it sounds: a photograph straight from a modern phone is often 4000 px wide and several megabytes, and serving that to a reader on mobile data would make the site feel broken.

Two decisions are worth recording:

**Metadata is stripped.** Phone photographs commonly carry GPS coordinates. Publishing those alongside a junior chess session would be careless, so every image is re-encoded rather than copied, which discards EXIF data as a side effect. Re-encoding also means a file that is secretly something else — an image with executable content appended — cannot survive the process.

**Orientation is corrected before stripping.** Phones often store a photograph rotated with an EXIF flag saying "display this the other way up". Strip the metadata first and the picture appears sideways, so the rotation is applied to the pixels first.

Uploaded files live in `storage/app/public/`, outside the web root, served through the `public/storage` symlink created by `php artisan storage:link`. Posts reference them with root-relative Markdown such as `![The A team](/storage/images/2026/08/...)`.

> **A bug caught before delivery.** The first version wrote *absolute* URLs into post bodies. Every photograph would have worked perfectly — until the club moved from the preview address to `coventrychessclub.co.uk`, at which point every image in every post would have broken, with the old address baked into the text of each one. Root-relative paths survive the move. A test now asserts the inserted Markdown never contains `http`.

---

## Part 14 — Hiding puzzle answers

Position posts always had a solution field, but it was printed straight onto the page, which defeats the purpose of setting a puzzle. The answer now sits behind a *Show the answer* button.

It is built on the HTML `<details>` and `<summary>` elements rather than a JavaScript panel, for one reason: it works when JavaScript does not. A reader on a locked-down work computer or an ageing phone still gets a working button. CSS turns what browsers draw as a small triangle into something that plainly looks like a button, and swaps the wording to *Hide the answer* when open.

The same component is used for answers attached to diagrams dropped mid-article, so the behaviour is consistent wherever a puzzle appears.

---

## Part 15 — The contact form's automatic reply

An enquiry now produces two emails: an acknowledgement to the enquirer, signed by the club secretary, and a notification to the club. The enquiry is stored either way.

The acknowledgement's content is driven entirely by `config/club.php` — meeting time, venue, junior details and charge, officers' names and telephone numbers, the Facebook group, whether to mention the 4NCL team, and who signs it. Nothing is hard-coded into the template, so the club can change what newcomers are told without a developer.

Each send is wrapped individually in `EnquiryController::trySend()`. If the mail server is misconfigured or unreachable, the failure is logged, the visitor still sees a confirmation, and the enquiry is still saved. Losing a prospective member's message because of an SMTP problem would be the worst possible failure for this form, so it cannot happen.

The form also collects an optional playing strength — beginner, intermediate, advanced, or prefer not to say — shown in the admin inbox and in the notification email, so whoever greets a newcomer knows roughly who to pair them with.

---

## Part 16 — Verifying the appearance

Every screen was captured at desktop (1440 px) and mobile (390 px) widths with a Playwright script, and reviewed individually.

The boards are initialised lazily, and a full-page capture of a tall page stitches strips together, so a board could be caught mid-initialisation and photographed as an empty placeholder. The script therefore scrolls the whole page first and waits until every board reports itself finished. Each image is then checked programmatically by counting board and piece pixels, rather than trusting a visual glance.

### Bugs this process caught

Six defects were found and fixed by reviewing the output rather than the code:

| Defect | Why it mattered |
|---|---|
| Cramped move list, stray separator, sticky action bar overlapping the form | Cosmetic, but on the screens members use most |
| The bundled board theme was **not** lichess's colour — its dark squares composite to `#c0ad90` rather than `#b58863` | The whole point was that the boards look like lichess. Confirmed by sampling rendered pixels, not by eye |
| Check was highlighted on the **wrong king** — always White's | Chessground infers the checked side from whose turn it is, and defaults to White unless told. Every check in every game marked the white king. Reported by the club, which is exactly why real use matters |
| Static diagrams never highlighted check at all | The mirror image of the same bug: highlighting enabled, no value passed. Silent rather than wrong |
| A *Remove* button and caption box appeared on a blank photograph form | Buttons that cannot do anything make people distrust the rest of the form |
| **`hidden` did not work on any button anywhere on the site** | The most instructive of the six. See below |

### The lesson from the last one

The custom `.btn-*` classes set `display: inline-flex`. Tailwind emits those rules *after* its own utilities, so on equal specificity `display: inline-flex` beat `.hidden { display: none }`. Every button on the site carrying `hidden` stayed visible.

What makes it worth recording is how it hid from testing. A browser check asserted that the class was being added and removed correctly — and it was, perfectly. The element was visible the entire time. **A test that checks a class name instead of the effect can pass while the user still sees the bug.** The verification was rewritten to read `getComputedStyle().display`, and the fix (`.btn-secondary:not(.hidden)`) restores the utility's authority sitewide rather than patching the one card where it was noticed.

---

## Part 17 — Letting administrators change the homepage position

The homepage originally embedded one Italian Game FEN directly in `home.blade.php`. That looked right, but changing it for a tournament result would have required editing and redeploying code. The replacement keeps the board live and responsive while moving its FEN, orientation and caption into a single `homepage_settings` database record.

`HomepageSetting::current()` returns the saved record or an unsaved Italian Game default. A fresh installation therefore keeps the original homepage without inserting data during a public request. `HomepageController` exposes one administrator-only screen under **Members → Homepage**, validates the FEN with the existing `ChessNotation` helper, normalises it before storage and provides a separate action that restores all three default values.

The administrator screen reuses the position builder already proven in the post editor. An administrator can paste a FEN from a completed game or drag pieces directly on the preview board, choose the side to move, select White's or Black's viewpoint, add a short event caption and save. The public hero reads that record on its next request, so a Summer Cup winning position can replace the Italian Game immediately without altering a template.

Six focused feature tests cover the fresh-install fallback, administrator-only access, saving and public rendering, invalid FEN rejection, unsupported orientation rejection and restoration of the Italian Game. The full suite now passes **105 tests with 344 assertions**. Desktop browser review and a separate 390 px mobile capture confirmed that both the administrator builder and the public hero remain usable and free of horizontal overflow.

---

## Appendix — Command summary

```bash
# Environment
sudo apt-get install -y php-cli php-mbstring php-xml php-curl php-zip \
                        php-sqlite3 php-mysql php-bcmath php-gd unzip
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Application
composer create-project laravel/laravel coventry-chess-club
cd coventry-chess-club
composer require league/commonmark
composer require pestphp/pest pestphp/pest-plugin-laravel --dev
pnpm install
pnpm add chessground chess.js

# Database and uploaded files
php artisan migrate:fresh --seed
php artisan storage:link

# Build and run
pnpm run build
php artisan serve

# Test
php artisan test
```

---

## References

[1] [Laravel 13 Documentation](https://laravel.com/docs) — framework reference for routing, Eloquent, validation, middleware and testing.
[2] [Chessground](https://github.com/lichess-org/chessground) — lichess.org's board component, GPL-3.0.
[3] [chess.js](https://github.com/jhlywa/chess.js) — FEN and PGN parsing and move generation, BSD-2-Clause.
[4] [Tailwind CSS 4](https://tailwindcss.com/docs) — utility-first CSS framework.
[5] [league/commonmark](https://commonmark.thephpleague.com/) — Markdown parser used for post bodies.
[6] [Pest](https://pestphp.com/docs) — the testing framework used for the suite.
[7] [Coventry Chess Club (existing site)](https://coventrychessclub.blogspot.com) — source of the migrated pages and posts.
[8] [MDN: `<details>` element](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/details) — the basis of the reveal-answer control, chosen because it needs no JavaScript.
[9] [PHP GD](https://www.php.net/manual/en/book.image.php) — image resizing and re-encoding, used in preference to Imagick because GD is available on far more shared hosts.
