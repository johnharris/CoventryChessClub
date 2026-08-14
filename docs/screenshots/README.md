# Coventry Chess Club — the proposed new website

These are screenshots of a working website built to replace the club's Blogspot pages. Nothing here is a mock-up or a drawing: every image is a photograph of the real site running, so what you see is what the site does.

The site is not yet on the internet. That comes next, once hosting is arranged. In the meantime these pictures are the easiest way for club members to see it and say what they think.

Each page is shown twice, because the site adapts to the device: the **desktop** version is what you would see on a computer, and the **mobile** version is what you would see on a phone. Click any link to view the full image.

If you would rather read through everything in one go, there is a **[single PDF of all the pages](../coventry-chess-club-website-preview.pdf)** that you can download, print or forward.

---

## The public website

This is what anyone visiting the club's web address would see.

| Page | What it is | View |
|---|---|---|
| **Home page** | The front page: club night details, the latest news, recent games and puzzles, and where to find us | [Desktop](desktop/01-home.png) · [Phone](mobile/01-home.png) |
| **Club blog** | Every post in one place, with buttons to show only news, only puzzles or only games, and a search box | [Desktop](desktop/02-blog.png) · [Phone](mobile/02-blog.png) |
| **An annotated game** | Morphy's Opera House game. Readers step through it move by move on the board, with the annotator's comments appearing beside each move | [Desktop](desktop/03-post-annotated-game.png) · [Phone](mobile/03-post-annotated-game.png) |
| **A chess puzzle** | A rook ending shown as a proper diagram, with the answer hidden until the reader asks for it | [Desktop](desktop/04-post-position.png) · [Phone](mobile/04-post-position.png) |
| **A news article** | An ordinary written post, which can still have chess diagrams and photographs dropped into the middle of the text | [Desktop](desktop/05-post-news.png) · [Phone](mobile/05-post-news.png) |
| **Fixtures** | One of the club's standing pages, carried across from the old site | [Desktop](desktop/06-page-fixtures.png) · [Phone](mobile/06-page-fixtures.png) |
| **Juniors** | The junior section: its own time, its own venue at St Oswald's, the £5 charge, and the pre-booking requirement | [Desktop](desktop/07-page-juniors.png) · [Phone](mobile/07-page-juniors.png) |
| **Fun Stuff** | Another standing page, this one with a puzzle diagram inside the text | [Desktop](desktop/08-page-fun-stuff.png) · [Phone](mobile/08-page-fun-stuff.png) |
| **Contact the club** | How newcomers and parents get in touch, with both officers' telephone numbers alongside. What happens after the form is sent is shown further down | [Desktop](desktop/09-contact.png) · [Phone](mobile/09-contact.png) |
| **Members' login** | Where members sign in. A visitor never needs this | [Desktop](desktop/10-login.png) · [Phone](mobile/10-login.png) |

The boards are not pictures. They are the same board software that [lichess.org](https://lichess.org) uses, so the pieces, the colours and the feel are exactly what anyone who plays online already knows.

### Puzzles keep their answers to themselves

A reader gets the position and the question, and nothing else, until they choose to look:

| Before clicking | After clicking |
|---|---|
| [Answer hidden](desktop/21a-answer-hidden.png) | [Answer revealed](desktop/21b-answer-revealed.png) |

---

## What happens when somebody contacts the club

This is worth following through in order, because it is the part of the site that does work for the club rather than simply displaying information. The four pictures below are one real enquiry, from the moment a newcomer finishes typing to the letter that arrives in her inbox.

| Step | What happens | View |
|---|---|---|
| **1. The form, filled in** | A newcomer has given her name, email and telephone number, said her enquiry is about joining, and put herself down as an intermediate player | [Desktop](desktop/22-contact-filled-in.png) · [Phone](mobile/22-contact-filled-in.png) |
| **2. What she sees when she presses send** | An immediate confirmation that the message reached the club, so she is not left wondering whether it went anywhere | [Desktop](desktop/23-contact-confirmation.png) · [Phone](mobile/23-contact-confirmation.png) |
| **3. The reply she receives** | The club's standard welcome letter, sent within seconds, signed by the club secretary | [Desktop](desktop/24-email-acknowledgement.png) · [Phone](mobile/24-email-acknowledgement.png) |
| **4. What the club receives** | The enquiry itself, with her details and playing strength, and a button that opens it in the site's own inbox | [Desktop](desktop/25-email-club-notification.png) · [Phone](mobile/25-email-club-notification.png) |

**The reply is the wording the club already uses**, so nobody has to write it again: when and where we meet, the entrance on Broad Lane, the six teams in the Coventry and District league, the junior section's separate time, venue and £5 charge, that private tuition is available from our qualified trainers, both officers' telephone numbers, and the Facebook group. It ends by saying plainly that she is welcome simply to turn up on a Tuesday, and suggests telephoning first so somebody can look out for her.

It also states that it is an automatic acknowledgement and that a club officer will read the message and be in touch personally. That matters: an automatic letter that pretends to be personal reads badly, whereas one that is honest about what it is reads as efficient.

**The enquiry reaches the club two ways at once** — by email to the officers, and stored in an inbox on the site itself. Either one alone would eventually lose a message to a spam filter or a changed email address.

The playing strength question is optional and can be left as *"Prefer not to say"*, which the form makes clear. When it is answered, whoever greets a newcomer on a Tuesday already knows roughly who to pair them with.

One practical point for going live: these letters cannot actually be sent until the club has hosting with a mail server configured. Until then the site stores every enquiry and shows the sender a confirmation, but the emails are written to a log file rather than delivered. Nothing is lost, and setting this up is part of putting the site online.

---

## The members' area

Once signed in, any member can write for the site. This is the part that replaces having to ask one person to post everything.

| Page | What it is | View |
|---|---|---|
| **Dashboard** | The starting point: what you have written, what is still a draft, and shortcuts to write something new | [Desktop](desktop/11-members-dashboard.png) · [Phone](mobile/11-members-dashboard.png) |
| **My posts** | Everything you have written, published or not, ready to edit | [Desktop](desktop/12-members-my-posts.png) · [Phone](mobile/12-members-my-posts.png) |
| **Writing up a game** | The blank form. You paste a game in the usual PGN format, or drag a `.pgn` file straight onto the box | [Desktop](desktop/13-editor-annotated-game.png) · [Phone](mobile/13-editor-annotated-game.png) |
| **The same form, with a game pasted in** | **Worth a look.** The moment a game is pasted, the site fills in both players, the result, the date and the competition by itself, and shows a working board preview | [Desktop](desktop/13b-editor-game-with-pgn.png) |
| **Setting up a puzzle** | For a single position. Either paste a FEN, or simply drag the pieces onto the board until it looks right. The answer goes in its own box and stays hidden from readers until they ask | [Desktop](desktop/14-editor-position.png) · [Phone](mobile/14-editor-position.png) |
| **Writing a news post** | An ordinary article. Photographs can be chosen from a file, dragged onto the text, or pasted straight in | [Desktop](desktop/15-editor-news.png) · [Phone](mobile/15-editor-news.png) |
| **Photographs** | Every picture uploaded to the site, in one place, with how much space they are using | [Desktop](desktop/16-image-library.png) · [Phone](mobile/16-image-library.png) |
| **My profile** | Your name as it appears on posts, your ECF details, and your password | [Desktop](desktop/20-member-profile.png) · [Phone](mobile/20-member-profile.png) |

Nobody can accidentally interfere with anybody else's work: a member can edit and delete their own posts, and nobody else's.

Photographs are resized automatically, so a large picture straight from a phone will not make the site slow to load, and the location information that phones record is stripped out before anything is published.

---

## For club officers

Officers get everything above, plus the running of the site.

| Page | What it is | View |
|---|---|---|
| **Members** | Invite a new member by entering their email and choosing whether they are an ordinary member or an officer. Also where an account is suspended if someone leaves | [Desktop](desktop/17-admin-members.png) · [Phone](mobile/17-admin-members.png) |
| **Enquiries** | Every message from the contact form, kept on the site so nothing is lost to a spam filter, with the enquirer's playing strength shown alongside | [Desktop](desktop/18-admin-enquiries.png) · [Phone](mobile/18-admin-enquiries.png) |
| **Pages** | Edit the standing pages — Fixtures, Teams, Coaching, Juniors and so on — and decide which appear in the menu at the top | [Desktop](desktop/19-admin-pages.png) · [Phone](mobile/19-admin-pages.png) |

Because the standing pages are editable in the browser, details such as the coaches' names, session times and charges can be corrected by any officer at any time, without needing the site rebuilt.

---

## What is planned next

A **Swiss tournament system**: entering the players, working out the pairings for each round, recording the results, and publishing a live table. The site has been built with this in mind so it can be added without disturbing anything else.

---

## Comments welcome

If anything looks wrong, reads awkwardly, or is missing, please say so — it is far easier to change now than after the site is live. In particular:

- Are the club night details correct? Tuesdays from 7:30pm at the Massey Ferguson Social Club, Banner Lane, CV5 7NL, with the entrance on Broad Lane.
- Are the junior details correct? Tuesdays at 4:30pm, £5 a session, at St Oswald's Church Hall, Tile Hill, pre-booking essential.
- Are the coaching details right? The site names Rhys Edwards and Ed Goodwin as the club's qualified trainers.
- Does the automatic reply read as you would want it to? It is shown in full above, and every part of it — times, fees, venues, telephone numbers, even who signs it — can be changed by an officer without the site being rebuilt.
- Is there anything from the old site that has been missed?
- Does anything look confusing or hard to follow?
- Would you write for it? There are six teams' worth of games that nobody currently writes up.
