<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * The club's standing pages, carried over from the old Blogspot site so the new
 * site launches with the same information rather than empty placeholders.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }

    private function pages(): array
    {
        return [
            [
                'slug' => 'fixtures',
                'title' => 'Fixtures',
                'nav_order' => 10,
                'show_in_nav' => true,
                'is_published' => true,
                'body' => <<<'MD'
Our season runs from early October through to the following May, with the Summer Cup
filling the weeks after the league programme finishes. Club nights are every Tuesday
at the Massey Ferguson Social Club from 7:30pm, whether or not a match is scheduled.

## League fixtures

Full fixture lists and up-to-date results for both leagues we play in are published
by the leagues themselves:

- [Coventry & District Chess League](http://covchessleague.blogspot.com/) — fixtures, tables and news
- [Coventry League results on the ECF LMS](https://ecflms.org.uk/lms/node/406/home) — match cards as they are submitted
- [Leamington & District Chess League](http://www.leamingtonchessleague.org.uk/) — our Division 4 side

## The season at a glance

| Period | What is on |
|---|---|
| Early October | Season opens; league matches begin |
| December | First half of the league season closes; club nights continue for social chess |
| January | League matches resume |
| March | Club rapidplay tournament, then the Summer Cup begins |
| April – May | Summer Cup rounds, with a free week over Easter |
| May | Club AGM (members only) |
| June | League AGM |

Exact dates are announced on the [club blog](/blog) as each part of the season is
confirmed, so that is the place to check before travelling to a match.

## Home matches

Home fixtures are played at the Massey Ferguson Social Club, Broad Lane, Coventry,
CV5 7NL. There is parking on site and a bar. Visiting teams are always welcome — please arrive
for a 7:30pm start.
MD,
            ],
            [
                'slug' => 'teams',
                'title' => 'Teams',
                'nav_order' => 20,
                'show_in_nav' => true,
                'is_published' => true,
                'body' => <<<'MD'
Coventry Chess Club runs several teams across two leagues, which means there is
competitive chess available at just about every standard. If you would like to play
league chess, speak to a team captain on a club night, or
[send us a message](/contact).

## Coventry & District Chess League

We enter **six teams** across the divisions of the
[Coventry & District Chess League](http://covchessleague.blogspot.com/). Our A team
plays in the First Division, with further sides in the Second and Third Divisions,
so there is a place for players of every standard.

Alongside the league sides, a number of members simply play friendly games on a club
night without committing to a team.

Matches are played on Tuesday evenings, home and away, with a 7:30pm start.

## Leamington & District Chess League

We also field a side in Division 4 of the
[Leamington & District Chess League](http://www.leamingtonchessleague.org.uk/),
which gives newer and improving players a route into graded chess against opposition
from across south Warwickshire.

## Playing for a team

You do not need a grade or an ECF rating to start. Come along on a Tuesday, play a few
friendly games, and a captain will find you a board at the right level. To play graded
league chess you will need
[ECF membership](https://www.englishchess.org.uk/), which the club can help you arrange.

## Results

Match results and league tables are published on the league websites, and the
notable games from our matches often appear on the [club blog](/blog) with annotations.
MD,
            ],
            [
                'slug' => '1-1-coaching',
                'title' => '1-1 Coaching',
                'nav_order' => 30,
                'show_in_nav' => true,
                'is_published' => true,
                'body' => <<<'MD'
One-to-one coaching is available to both adults and juniors through the club, given by
our qualified trainers, **Rhys Edwards** and **Ed Goodwin**. Rhys has played board one
for our A team in the First Division of the Coventry & District League.

Coaching is arranged privately with the trainer and there is a fee for sessions.

## What coaching covers

Sessions are built around the player rather than a fixed syllabus, but typically cover:

- **Openings** — building a small, reliable repertoire you understand, instead of memorising long lines
- **Tactics** — recognising pins, skewers, discovered attacks and forks quickly and reliably
- **Positional play** — pawn structure, piece activity, weak squares and long-term plans
- **Endgames** — the technical positions that decide league games, starting with king and pawn
- **Game analysis** — going through your own games to find the recurring mistakes worth fixing

## Who it suits

Coaching suits anyone who has started playing regularly and wants to improve faster than
club games alone allow: a junior moving into their first graded tournaments, an adult
returning to chess after years away, or a league player stuck at the same grade for a
few seasons.

## Arranging a session

Speak to either trainer on a club night, or [contact the club](/contact) and we will put
you in touch. Please mention the player's age and roughly how long they have been
playing, as that helps in matching the sessions to the player.
MD,
            ],
            [
                'slug' => 'juniors',
                'title' => 'Juniors',
                'nav_order' => 40,
                'show_in_nav' => true,
                'is_published' => true,
                'body' => <<<'MD'
Our Junior section is every **Tuesday starting at 4:30pm**, costs **£5 per session**, and
is held at **St Oswald's Church Hall, Tile Hill** — a different venue from our main club
night. Spots for this session fill up very quickly and must be pre-booked, so please
[check with us](/contact) before attending.

Sessions coach young players from the basic moves through to their first competitive
games.

> **Please note:** the junior session is at St Oswald's Church Hall, Tile Hill, not at
> the Massey Ferguson Social Club where the main club night is held.

## What a junior session looks like

Sessions mix short teaching, puzzles and played games. Beginners start with how the
pieces move, basic checkmates and simple tactics; more experienced juniors work on
openings, endgame technique and playing with a clock in preparation for graded events.

## Safeguarding

The club takes safeguarding seriously. We have a safeguarding policy and a code of
conduct that all juniors, parents and coaches are asked to read, and coaches working
with our junior section are appropriately checked. Please ask on a club night, or
through the [contact page](/contact), for copies of:

- the junior chess calendar
- the code of conduct
- the safeguarding policy
- the waiting list form

Parents are welcome to stay for sessions.

## Moving up to club and league chess

Juniors who are ready are welcome to come along to the main club night at the Massey
Ferguson Social Club and, in time, to play in league matches — several of our league
players came up through the junior section. [One-to-one coaching](/1-1-coaching) is also
available for juniors who want to push on faster.

## A word to parents

Chess is unusually good at rewarding patience and concentration, and it is one of the
few competitive activities where a nine-year-old and a seventy-year-old can have a
genuinely close game. No equipment is needed — we provide the boards, sets and clocks.
MD,
            ],
            [
                'slug' => 'fun-stuff',
                'title' => 'Fun Stuff',
                'nav_order' => 50,
                'show_in_nav' => false,
                'is_published' => true,
                'body' => <<<'MD'
Chess to enjoy away from the league programme: puzzles, curiosities and positions worth
a second look.

## Try this one

White is a rook down but has one move that settles the game. Board set from Black's
side, as it was played.

```fen
6k1/5ppp/8/8/8/8/5PPP/4R1K1 w - - 0 1
Caption: White to play. What is the fastest win?
Solution: 1. Re8+ forces the king into the corner and mates shortly after; the point is that Black's rook has no useful defensive move.
```

## Puzzles every day

- [Lichess daily puzzle](https://lichess.org/training) — free, unlimited, and rated so the difficulty adapts to you
- [Lichess practice](https://lichess.org/practice) — guided lessons on checkmates, endgames and tactical motifs
- [Lichess coordinate trainer](https://lichess.org/training/coordinate) — surprisingly useful for reading notation quickly

## Worth reading

The [How to improve at chess](/blog) article on our blog collects the practical advice
our stronger players give most often — study your own losses, learn a small repertoire
properly, and do tactics puzzles regularly rather than in bursts.

## Play us online

Several club members play on [lichess](https://lichess.org/) between club nights. Ask
around on a Tuesday for handles, or set up a casual game with someone you have just
played over the board — it is a good way to keep a rivalry going through the summer.
MD,
            ],
            [
                'slug' => 'useful-links',
                'title' => 'Useful Links',
                'nav_order' => 60,
                'show_in_nav' => false,
                'is_published' => true,
                'body' => <<<'MD'
The sites and resources our members use most.

## Our leagues

| Link | What it is for |
|---|---|
| [Coventry & District Chess League](http://covchessleague.blogspot.com/) | Fixtures, tables and league news |
| [Coventry League results (ECF LMS)](https://ecflms.org.uk/lms/node/406/home) | Match cards and results as submitted |
| [Leamington & District Chess League](http://www.leamingtonchessleague.org.uk/) | Fixtures and tables for our Division 4 side |

## Chess governing bodies

- [English Chess Federation](https://www.englishchess.org.uk/) — membership, ratings and national events
- [ECF rating database](https://www.englishchess.org.uk/ecf-rating-database/) — look up your own or an opponent's rating
- [FIDE](https://www.fide.com/) — the international federation

## Playing and training online

- [lichess.org](https://lichess.org/) — free, no adverts, excellent analysis and puzzles
- [Lichess study](https://lichess.org/study) — build and share annotated games, which is what our blog's game viewer uses under the bonnet
- [Chess.com](https://www.chess.com/) — the largest playing site

## Chess in the community

- [Chess in Schools and Communities](https://www.chessinschools.co.uk/) — brings chess to primary schools
- [Massey Ferguson Social Club](https://www.masseyfergusonsocialclub.co.uk/) — our venue, which also hosts private functions

## Find us

We are also on Facebook — search for *Coventry Chess Club*. Club news appears here first,
on the [blog](/blog).
MD,
            ],
        ];
    }
}
