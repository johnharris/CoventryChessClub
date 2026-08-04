<?php

namespace App\Http\Controllers;

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /* =====================================================================
     | Public contact form
     * ================================================================== */

    public function create(): View
    {
        return view('contact');
    }

    public function store(Request $request): RedirectResponse
    {
        // Three submissions per ten minutes per IP is plenty for a club site.
        $key = 'enquiry:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'message' => 'You have sent several messages already. Please try again shortly.',
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:180'],
            'enquiry_type' => ['required', Rule::in(array_keys(Enquiry::TYPES))],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
            // Honeypot: a hidden field real people never fill in.
            'website' => ['prohibited'],
        ], [
            'website.prohibited' => 'Your message could not be sent.',
        ]);

        RateLimiter::hit($key, 600);

        $enquiry = Enquiry::create([
            ...$data,
            'ip_address' => $request->ip(),
        ]);

        // Email the club as well, if outbound mail has been configured.
        if ($to = config('club.enquiry_email')) {
            try {
                Mail::to($to)->send(new EnquiryReceived($enquiry));
            } catch (\Throwable $e) {
                // The enquiry is safely stored either way, so never fail the request.
                Log::warning('Enquiry notification could not be sent: '.$e->getMessage());
            }
        }

        return redirect()->route('contact')
            ->with('status', 'Thank you — your message has reached the club and we will be in touch soon.');
    }

    /* =====================================================================
     | Admin inbox
     * ================================================================== */

    public function index(Request $request): View
    {
        $archived = $request->boolean('archived');

        return view('members.admin.enquiries', [
            'enquiries' => Enquiry::query()
                ->where('is_archived', $archived)
                ->orderByDesc('created_at')
                ->paginate(20)
                ->withQueryString(),
            'archived' => $archived,
            'unreadCount' => Enquiry::unread()->count(),
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        if (! $enquiry->is_read) {
            $enquiry->update(['is_read' => true]);
        }

        return view('members.admin.enquiry', ['enquiry' => $enquiry]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $enquiry->update([
            'is_read' => $request->boolean('is_read', true),
            'is_archived' => $request->boolean('is_archived'),
        ]);

        return back()->with('status', 'Enquiry updated.');
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();

        return redirect()->route('members.enquiries.index')
            ->with('status', 'Enquiry deleted.');
    }
}
