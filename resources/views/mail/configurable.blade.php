<x-mail::message>
@if ($isTest ?? false)
<x-mail::panel>
Administrator preview/test only — no visitor or member received this message.
</x-mail::panel>
@endif

{!! $bodyHtml !!}

@if (filled($signature))
Kind Regards,

**{{ $signature }}**

@if (filled($signatureRole))
{{ $signatureRole }}
@endif
@endif

@if (filled($footer ?? null))
<x-mail::subcopy>
{{ $footer }}
</x-mail::subcopy>
@endif
</x-mail::message>
