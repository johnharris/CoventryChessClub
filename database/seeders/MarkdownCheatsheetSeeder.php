<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class MarkdownCheatsheetSeeder extends Seeder
{
    public function run(): void
    {
        Page::query()->firstOrCreate(
            ['slug' => 'markdown-cheatsheet'],
            [
                'title' => 'Markdown Cheatsheet',
                'nav_order' => 900,
                'show_in_nav' => false,
                'is_published' => false,
                'body' => <<<'MD'
This private reference page shows the formatting supported by club posts and standing pages. Copy an example into the editor, replace its sample wording, and use **Preview** before publishing.

> This page is deliberately unpublished and hidden from the public navigation. Keep it that way unless the club intentionally wants to publish the guide.

## Headings

Use one `#` for the main title, two for a section, and three for a subsection. A post or page already has its own title, so begin the body with `##` in most cases.

```markdown
## Match report

### Board one

#### A smaller heading
```

## Bold, italics and crossed-out text

```markdown
**Important result**

*Emphasised wording*

***Bold and italic together***

~~An old date that has been replaced~~
```

**Important result**

*Emphasised wording*

***Bold and italic together***

~~An old date that has been replaced~~

## Paragraphs and line breaks

Leave a blank line between paragraphs.

```markdown
This is the first paragraph.

This is the second paragraph.
```

## Links

```markdown
[Visit the Coventry League website](http://covchessleague.blogspot.com/)
```

[Visit the Coventry League website](http://covchessleague.blogspot.com/)

Use meaningful link wording rather than “click here”.

## Lists

```markdown
- First item
- Second item
  - A nested item
- Third item

1. First step
2. Second step
3. Third step
```

- First item
- Second item
  - A nested item
- Third item

1. First step
2. Second step
3. Third step

## Quotations

```markdown
> Chess is the struggle against error.
>
> — Johannes Zukertort
```

> Chess is the struggle against error.
>
> — Johannes Zukertort

## Tables

Keep the dividing row of dashes beneath the headings. Colons control alignment.

```markdown
| Player | Score | Result |
|:---|:---:|---:|
| Alex | 3½ | 1st |
| Sam | 3 | 2nd |
| Jo | 2½ | 3rd |
```

| Player | Score | Result |
|:---|:---:|---:|
| Alex | 3½ | 1st |
| Sam | 3 | 2nd |
| Jo | 2½ | 3rd |

## Images

Upload the photograph through **Members → Images**, then use the Markdown supplied by the image library.

```markdown
![Descriptive alternative text](/storage/media/example-photo.jpg)
```

Always replace the alternative text with a short description of the image. This helps visitors using screen readers and explains the image if it cannot load.

## Chess positions

A chess diagram uses a fenced `fen` block. The first line is the FEN. The remaining lines are optional settings.

The source looks like this when typed into the editor (the four leading spaces below are only there to display the example):

    ```fen
    r1bqk2r/pppp1ppp/2n2n2/2b1p3/2B1P3/2NP1N2/PPP2PPP/R1BQK2R w KQkq - 4 6
    Caption: White to move in the Italian Game
    Orientation: white
    Solution: Consider 7.Be3 followed by Qd2 and long castling.
    ```

The same block renders as an interactive board:

```fen
r1bqk2r/pppp1ppp/2n2n2/2b1p3/2B1P3/2NP1N2/PPP2PPP/R1BQK2R w KQkq - 4 6
Caption: White to move in the Italian Game
Orientation: white
Solution: Consider 7.Be3 followed by Qd2 and long castling.
```

| Chess option | Purpose |
|---|---|
| `Caption:` | Text printed beneath the board |
| `Orientation: white` | Shows White at the bottom |
| `Orientation: black` | Flips the board to Black’s viewpoint |
| `Solution:` | Adds an answer hidden behind a reveal button |

For a game rather than one position, choose the **Game** post type and paste PGN into the dedicated PGN field. Do not put PGN inside an ordinary Markdown code block.

## Inline code and code blocks

Use backticks for a short item such as `1.e4 e5`. Use three backticks for a block that should remain exactly as typed.

````markdown
```
This text keeps its spacing.
```
````

## Dividing line

Three hyphens create a horizontal divider:

```markdown
---
```

---

## Characters with special meaning

Place a backslash before a Markdown character if you want to display it literally.

```markdown
\*This displays asterisks instead of italics.\*
```

\*This displays asterisks instead of italics.\*

## Safety and good practice

Raw HTML is escaped rather than executed, so use the supported Markdown shown above. Keep paragraphs short, use headings in order, describe images, check links, and preview a page or post before publishing it.

If a layout is difficult to reproduce, save a draft and ask another administrator to review it rather than adding HTML.
MD,
            ],
        );
    }
}
