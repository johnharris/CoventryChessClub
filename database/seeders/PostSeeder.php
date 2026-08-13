<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Blog content.
 *
 * The general posts are carried over from the club's old Blogspot site so nothing
 * is lost in the move. The position and game posts demonstrate what the new site
 * can do that the old one could not.
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->orderBy('id')->first()
            ?? User::orderBy('id')->firstOrFail();

        $member = User::where('role', User::ROLE_MEMBER)->orderBy('id')->first() ?? $admin;

        foreach ($this->posts($admin, $member) as $post) {
            Post::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }

    private function posts(User $admin, User $member): array
    {
        return [
            /* ---------------------------------------------------------------
             | Annotated game — the flagship demonstration of the PGN viewer
             --------------------------------------------------------------- */
            [
                'slug' => 'morphy-duke-of-brunswick-opera-house-1858',
                'user_id' => $member->id,
                'type' => Post::TYPE_GAME,
                'title' => 'The Opera House Game, and what it still teaches us',
                'excerpt' => 'Morphy against the Duke of Brunswick and Count Isouard, Paris 1858 — seventeen moves that explain development better than any textbook chapter.',
                'white_player' => 'Paul Morphy',
                'black_player' => 'Duke of Brunswick and Count Isouard',
                'result' => '1-0',
                'event' => 'Casual game, Paris Opera House',
                'played_on' => '1858-10-01',
                'orientation' => 'white',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => Carbon::parse('2026-07-14 19:30'),
                'pgn' => <<<'PGN'
[Event "Paris Opera House"]
[Site "Paris FRA"]
[Date "1858.10.01"]
[White "Paul Morphy"]
[Black "Duke of Brunswick and Count Isouard"]
[Result "1-0"]

1. e4 e5 2. Nf3 d6 {The Philidor Defence. Solid enough, but it does nothing for
Black's development, and against Morphy that is a luxury nobody could afford.}
3. d4 Bg4 {A natural-looking move that turns out to be the root of all Black's
problems: the bishop will simply be chased, losing time.} 4. dxe5 Bxf3 5. Qxf3
dxe5 6. Bc4 {Every white move so far has developed a piece or opened a line.
Black, by contrast, has moved a bishop twice and traded it off.} Nf6 7. Qb3
{Hitting f7 twice and asking a question Black cannot answer comfortably.} Qe7
8. Nc3 c6 9. Bg5 b5 {A desperate attempt at counterplay, but it loosens
everything.} 10. Nxb5 $1 {The first sacrifice. The knight cannot be taken
because of the pin along the a4-e8 diagonal.} cxb5 11. Bxb5+ Nbd7 12. O-O-O $1
{Castling into an attack — with tempo. The rook lands on d1 and the pin down the
d-file is decisive.} Rd8 13. Rxd7 $1 Rxd7 14. Rd1 {The second rook joins. Black
is a rook and a knight up in material and completely lost.} Qe6 15. Bxd7+ Nxd7
16. Qb8+ $1 {The final point: a queen sacrifice to force the mate.} Nxb8 17.
Rd8# {Two minor pieces deliver mate while Morphy is down a queen and a rook.
Development, not material, decided the game.} 1-0
PGN,
                'body' => <<<'MD'
Every club has an evening where somebody loses a game they never really played. Pieces
stay at home, a bishop wanders out and gets chased back, and by move twelve the position
is technically equal and practically hopeless. The game above, played by Paul Morphy in
a box at the Paris Opera House in 1858, is the purest illustration of why that happens.

## What Morphy actually does

Count the moves. By move nine, White has developed four pieces, castled is available at
any moment, and every single move has either brought a piece out or opened a line. Black
has moved a bishop twice, traded it off, and pushed pawns.

That is the whole game. The sacrifices from move ten onwards are not brilliance conjured
from nothing — they are the natural consequence of having three more pieces in play than
your opponent.

## The move worth memorising

**12. O-O-O!** is the move to take away from this. Castling is usually a quiet,
housekeeping move. Here it is an attacking move: the king gets to safety *and* the rook
lands on the open d-file where the pin is already fatal. When you are looking for a way
to bring your last piece into an attack, ask whether castling does it.

## How to use this game

Play it through on the board above, then try this: set the position up after **9. Bg5**
and play it out against a club mate, taking the black side. It is instructive precisely
because Black is not yet lost by any material count — and losing it anyway teaches the
lesson better than reading about it.

If you would like more of this sort of thing, the [Fun Stuff](/fun-stuff) page collects
puzzles and links, and one-to-one [coaching](/1-1-coaching) is available through the club.
MD,
            ],

            /* ---------------------------------------------------------------
             | Position post — demonstrates the FEN diagram
             --------------------------------------------------------------- */
            [
                'slug' => 'club-night-puzzle-a-rook-endgame-worth-knowing',
                'user_id' => $member->id,
                'type' => Post::TYPE_POSITION,
                'title' => 'Club night puzzle: a rook ending worth knowing',
                'excerpt' => 'This position, or something very like it, decides more league games than any opening you will ever study.',
                'fen' => '8/8/8/8/8/1k6/1p6/1K1R4 w - - 0 1',
                'side_to_move' => 'w',
                'orientation' => 'white',
                'caption' => 'White to play and draw. There is exactly one move that holds.',
                'solution' => '1. Rd8! and White draws. The rook goes as far away as possible along the file so it can check the king from behind without ever being trapped. If instead 1. Rd2? then 1...Kc3 wins, and 1. Rd3+? Kc4 followed by ...Kb4 also loses. The technique is called the "rook on the long side" and it is worth learning properly.',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => Carbon::parse('2026-07-28 20:00'),
                'body' => <<<'MD'
Rook endings are where league points are quietly won and lost. Most club players know
roughly how to push a passed pawn, and almost nobody has looked at what to do when the
defence is a single well-placed rook.

Try the position above before reading the answer. White is a pawn down against a rook
and a king, and there is precisely one move that saves the half point.

## Why this one matters

It is not an artificial study. Positions of this shape — one pawn, one rook each side,
kings close — turn up several times a season in the Coventry League, usually at about
half past ten when everybody is tired. Knowing the rule saves you the trouble of finding
it at the board.

## The rule to remember

> With a rook against a pawn on the seventh, get your rook **as far away as the board
> allows** along the file, so it can check from behind without being caught. Short-side
> checks fail; long-side checks hold.

## Practise it

Set the position up with a club mate and play both sides. Then move the pawn to c2, and
to a2, and see which of them changes the assessment. That half hour will be worth more
to your grade than a new opening line.

If you want to work through endgames systematically, the free
[lichess practice](https://lichess.org/practice) lessons cover this material well, and
our [coaching page](/1-1-coaching) explains how to arrange one-to-one sessions.
MD,
            ],

            /* ---------------------------------------------------------------
             | General posts — migrated from Blogspot, newest first
             --------------------------------------------------------------- */
            [
                'slug' => 'end-of-season-dates-for-2026',
                'user_id' => $admin->id,
                'type' => Post::TYPE_GENERAL,
                'title' => 'End of season dates for 2026',
                'excerpt' => 'The Summer Cup begins on 24 March, and the club AGM is on 19 May. Here are the dates for the rest of the season.',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => Carbon::parse('2026-02-28 18:00'),
                'body' => <<<'MD'
With the league programme drawing to a close, here are the dates for the remainder of the
season. All events are on our usual Tuesday club night at the Massey Ferguson Social
Club, starting at 7:30pm.

| Date | Event |
|---|---|
| Tuesday 24 March | Summer Cup, Round 1 |
| Tuesday 31 March | Summer Cup, Round 2 |
| Tuesday 7 April | Free week — Easter break |
| Tuesday 14 April | Summer Cup, Round 3 |
| Tuesday 21 April | Summer Cup, Round 4 |
| Tuesday 28 April | Summer Cup, Round 5 |
| Tuesday 5 May | Summer Cup, Round 6 |
| Tuesday 19 May | **Club AGM** — members only |
| June, date to be confirmed | League AGM |

## The Summer Cup

The Summer Cup is our own internal competition and it is open to all members. It is
handicapped, so a newer player has a genuine chance against an established league
player — several of our stronger members have been knocked out by juniors over the years.

If you would like to enter, put your name down on a club night before the first round.

## The AGM

The club AGM on **19 May** is for members only. This is where we agree how many teams to
enter for next season, elect officers, and settle subscriptions, so it is worth coming
along if you have a view on any of that.

## Non-cup weeks

On any Tuesday without a scheduled fixture or cup round, the club is open as usual for
social and friendly chess. Visitors and prospective members are welcome — just turn up.
MD,
            ],
            [
                'slug' => 'how-to-improve-at-chess',
                'user_id' => $admin->id,
                'type' => Post::TYPE_GENERAL,
                'title' => 'How to improve at chess',
                'excerpt' => 'The advice our stronger players give most often, collected in one place — from tactics and openings through to the endgame and the mental side of the game.',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => Carbon::parse('2025-10-20 19:00'),
                'body' => <<<'MD'
Improving at chess is less mysterious than it looks. Almost everyone who gets
meaningfully better does the same handful of things, and almost everyone who plateaus is
skipping one of them. Here is the advice our stronger players give most often.

## Get the fundamentals right first

Before anything clever, make sure the basics are automatic: develop your pieces towards
the centre, castle early, do not move the same piece repeatedly in the opening, and do
not bring the queen out to be chased. The
[Opera House Game](/blog/morphy-duke-of-brunswick-opera-house-1858) on this site is
seventeen moves of exactly this point being proved.

## Study tactics, regularly and in small doses

Tactics decide club games. Learn to spot the recurring patterns until you see them
without looking:

- **Pins** — a piece that cannot move without exposing something more valuable
- **Skewers** — the same idea, with the valuable piece in front
- **Discovered attacks** — moving one piece to unleash another
- **Forks** — one piece attacking two targets, the knight's speciality

Fifteen minutes of puzzles a day beats three hours once a fortnight. The
[lichess puzzle trainer](https://lichess.org/training) is free and adjusts to your level.

## Build a small opening repertoire and understand it

You need far less opening knowledge than you think, but you need to understand what you
do know. Pick one reply as White and one against each of 1.e4 and 1.d4, and learn the
resulting *positions* — where the pieces belong, which pawn structures arise, what the
typical plans are. The Italian Game is a good starting point for White because the ideas
are clear and the structures repeat.

Memorising twenty moves of a line you do not understand is worse than useless, because
you will be lost on move twenty-one.

## Learn to play the middlegame with a plan

Once the pieces are out, ask three questions: which of my pieces is worst placed and how
do I improve it; where are the weak squares in my opponent's position; and which side of
the board should I be playing on. A mediocre plan followed consistently beats a series of
individually reasonable moves with no plan behind them.

## Take endgames seriously

This is the biggest single source of dropped points at club level. Start with:

- **King and pawn against king** — the opposition, and which squares matter
- **Rook endings** — the most common ending in practice, and the least understood
- **Basic mates** — king and queen, king and rook, delivered without thinking

There is a [rook ending puzzle](/blog/club-night-puzzle-a-rook-endgame-worth-knowing) on
the site that shows the sort of thing worth knowing cold.

## Learn from your own games, especially the losses

Go through every league game you lose. Not with an engine first — do it yourself, write
down where you think it went wrong, and *then* check. The recurring mistakes are the ones
worth fixing, and you will only find them by looking at your own games rather than
grandmaster ones.

## Do not neglect the mental side

Calculate concretely rather than hoping. Practise visualising two or three moves ahead
without touching the pieces. And learn to sit on your hands when you find a good move:
spend thirty more seconds checking it is not refuted.

## Play stronger opponents in a real club

Online blitz is entertaining and it will not make you much better. Long games against
stronger opposition, over the board, with the post-mortem afterwards, will. That is
precisely what a club night is for — and the conversation at the bar after the game is
often where the most instructive chess of the evening happens.

## Stay curious

Read about the game. Follow a tournament. Look at the games of a player whose style
appeals to you. The people who improve most are usually the ones who simply find chess
interesting, and keep turning up.

---

**New to the club?** We meet every Tuesday at the Massey Ferguson Social Club from
7:30pm — just come along, or [send us a message](/contact) first if you prefer. Our
[junior section](/juniors) runs on Tuesday afternoons at St Oswald's Church Hall, Tile
Hill, and [one-to-one coaching](/1-1-coaching) is available for both adults and juniors.
MD,
            ],
            [
                'slug' => 'summer-information-and-coaching-details',
                'user_id' => $admin->id,
                'type' => Post::TYPE_GENERAL,
                'title' => 'Summer information and coaching details',
                'excerpt' => 'The league programme is complete, the club stays open through the summer for friendly chess, and we are hoping to enter five teams next season.',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => Carbon::parse('2025-07-02 18:30'),
                'body' => <<<'MD'
The league season, the Summer Cup and both AGMs are now complete, so the competitive
programme is done until the autumn. The club, however, is not going anywhere.

## Through the summer

We remain open **every Tuesday** at the Massey Ferguson Social Club for social and
friendly chess. There is no fixture pressure, nobody is playing for a team, and it is
comfortably the best time of year to come along for the first time. All are welcome,
members and visitors alike.

## Next season

We are hoping to enter **five teams** into the Coventry & District League next season:

- one side in the First Division
- three sides in the Second Division
- one side in the Third Division

That depends on player availability, which is agreed at the AGM and confirmed before the
league's own AGM. If you would like to play league chess next season, now is the time to
say so.

The season is expected to start in the **first or second week of October**. Fixtures will
be published on the [Fixtures](/fixtures) page and announced here as soon as the league
confirms them.

## One-to-one coaching

Coaching for both adults and juniors is available from **Rhys Edwards**, who has played
for our A team in the First Division. Full details are on the
[1-1 Coaching](/1-1-coaching) page.

The summer is a good time to take this up: there are no matches to prepare for, so there
is space to work on something properly rather than patching holes mid-season.
MD,
            ],
            [
                'slug' => 'spring-summer-2025',
                'user_id' => $admin->id,
                'type' => Post::TYPE_GENERAL,
                'title' => 'Spring and summer 2025 dates',
                'excerpt' => 'A ten-minute tournament in March, then the Summer Cup running from April into May, with the AGM on 3 June.',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => Carbon::parse('2025-02-26 18:00'),
                'body' => <<<'MD'
The dates for the end of the 2024–25 season are settled. All events are on Tuesdays at
the Massey Ferguson Social Club.

| Date | Event |
|---|---|
| Tuesday 18 March | Ten-minute tournament — invitation only |
| Tuesday 8 April | Summer Cup, Round 1 |
| Tuesday 15 April | Summer Cup, Round 2 |
| Tuesday 22 April | Free week — Easter |
| Tuesday 29 April | Summer Cup, Round 3 |
| Tuesday 6 May | Summer Cup, Round 4 |
| Tuesday 13 May | Summer Cup, Round 5 |
| Tuesday 20 May | Summer Cup, Round 6 — final round |
| Tuesday 3 June | **Club AGM** — members only |

## The ten-minute tournament

The March rapidplay is by invitation. If you have not played in one before and would like
to, mention it on a club night — it is a quick, enjoyable format and a good introduction
to playing with a clock.

## The Summer Cup

Six rounds through April and May, open to all members. Put your name down before the
first round on 8 April.

## AGM

The AGM on 3 June is for members only. Team entries for next season, officers and
subscriptions are all decided there.
MD,
            ],
            [
                'slug' => 'christmas-and-new-year-opening-times',
                'user_id' => $admin->id,
                'type' => Post::TYPE_GENERAL,
                'title' => 'Christmas and new year opening times',
                'excerpt' => 'The club is open on 10 and 17 December for social chess, closed over Christmas and new year, and reopens on 7 January.',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => Carbon::parse('2024-12-23 12:00'),
                'body' => <<<'MD'
The first half of the Coventry League season is complete, and there are no further league
matches until **Tuesday 7 January**.

## Opening times over the holidays

| Date | Club |
|---|---|
| Tuesday 10 December | Open — club tournament (entries closed) |
| Tuesday 17 December | Open — social chess |
| Tuesday 24 December | **Closed** |
| Tuesday 31 December | **Closed** |
| Tuesday 7 January | Open — league matches resume |

## Second half of the season

League fixtures resume on 7 January. Captains will be in touch with their teams about
availability over the following weeks — please let them know your dates as early as you
can, as January and February are when availability problems cost us matches.

Best wishes for Christmas and the new year from everyone at the club.
MD,
            ],
            [
                'slug' => 'malcolm-harding',
                'user_id' => $admin->id,
                'type' => Post::TYPE_GENERAL,
                'title' => 'Malcolm Harding: funeral arrangements',
                'excerpt' => 'Arrangements for Malcolm Harding, whose funeral was held at Rainsbrook Crematorium on 13 November 2024.',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => Carbon::parse('2024-10-26 10:00'),
                'body' => <<<'MD'
It is with great sadness that we record the death of our friend and club member
**Malcolm Harding**.

## The funeral

The funeral was held at **11:00am on Wednesday 13 November 2024** at Rainsbrook
Crematorium, Rugby CV22 5QQ, with a gathering afterwards at Christ Church, Brownsover,
Rugby CV21 1QG. A live-cast was made available for those who could not attend in person.

## Donations

The family asked that, in lieu of flowers, donations be made to either of the causes
Malcolm cared about:

- [Road Peace](https://www.roadpeace.org/) — the national charity for road crash victims
- [Chess in Schools and Communities](https://www.chessinschools.co.uk/who-we-are) — bringing chess to primary schools

## Remembering Malcolm

Those who wanted to raise a glass in his memory were pointed towards his favourite pub,
The Merchants in Rugby (CV21 3AW) — which is exactly where he would have wanted people
to end up.

Our thoughts remain with Sue and the family.
MD,
            ],
        ];
    }
}
