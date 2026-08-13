{{--
    The club's standard welcome letter, sent automatically to anyone who uses
    the contact form.

    The wording is the club's own and is kept as close to the original as
    possible. Only the details that live in configuration — venue, times, fees,
    officers, links — are interpolated, so updating them in one place keeps this
    letter accurate rather than leaving a stale copy in a template.
--}}
<x-mail::message>
Hi {{ $enquiry->name }}

Thank you for your interest in {{ config('club.name') }}.

We have League and Social matches every {{ $meeting['day'] }} from {{ $meeting['time'] }} at
{{ $venue['name'] }}, {{ $venue['address'] }}@if ($venue['postcode']), {{ $venue['postcode'] }}@endif.
@if (! empty($venue['entrance']))
{{ $venue['entrance'] }}.
@endif

We have {{ $teams['coventry_count'] }} teams in the Coventry &amp; District chess league, plus a
number of players who play friendlies.{{ $teams['plays_4ncl'] ? ' We also have a team in the 4NCL.' : '' }}

Our Junior section is every {{ $meeting['juniors'] }}{{ ! empty($juniorsVenue['fee']) ? ' and only '.$juniorsVenue['fee'] : '' }} and is held at
{{ $juniorsVenue['name'] }}, {{ $juniorsVenue['address'] }} (spots for this session fill up very
quickly and must be pre-booked, so please check with us before attending).

If you wish to visit us and have a few games of chess, you are very welcome to come to
the club on a {{ $meeting['day'] }} evening.

Private tuition is also available for a fee if required, from our {{ $coaching['trainers'] }}.

For any further information you require, do feel free to telephone either of us:

@foreach ($officers as $officer)
- {{ $officer['role'] }}, {{ $officer['name'] }} — {{ $officer['phone'] }}
@endforeach

@if (! empty($links['facebook']))
We also have a Facebook group here: [{{ $links['facebook'] }}]({{ $links['facebook'] }}) — which
again you are welcome to join.
@endif

If you do decide to come down to the club, it may be best if you telephone one of us
beforehand and then we can make sure someone will keep an eye out for you on arrival and
introduce you to other club members.

Kind Regards,

{{-- A blank line between the two makes them separate paragraphs, which is the
     only form that survives both the HTML and plain-text renderings. A newline
     alone collapses to a space inside an HTML paragraph. --}}
**{{ $signature }}**

{{ $signatureRole }}

<x-mail::subcopy>
This is an automatic acknowledgement of the message you sent through our website on
{{ $enquiry->created_at->format('j F Y \a\t H:i') }}. There is no need to reply — a club
officer will read your message and be in touch personally. If you did not contact us,
please ignore this email.
</x-mail::subcopy>
</x-mail::message>
