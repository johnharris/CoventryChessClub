<x-mail::message>
# New enquiry from the website

**{{ $enquiry->name }}** sent a message through the contact form.

- **Type:** {{ $enquiry->typeLabel() }}
- **Email:** {{ $enquiry->email }}
- **Telephone:** {{ $enquiry->phone ?: 'Not given' }}
@if ($enquiry->strengthLabel())
- **Playing strength:** {{ $enquiry->strengthLabel() }} ({{ $enquiry->strengthHint() }})
@endif
@if ($enquiry->subject)
- **Subject:** {{ $enquiry->subject }}
@endif
- **Received:** {{ $enquiry->created_at->format('j F Y \a\t H:i') }}

---

{{ $enquiry->message }}

---

<x-mail::button :url="route('members.enquiries.show', $enquiry)">
Open in the site inbox
</x-mail::button>

Replying to this email goes straight back to {{ $enquiry->name }}.

{{ config('club.name') }}
</x-mail::message>
