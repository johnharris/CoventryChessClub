<?php

use App\Mail\EnquiryAcknowledgement;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

function submitEnquiry(array $overrides = []): TestResponse
{
    return test()->post('/contact', array_merge([
        'name' => 'Jane Newcomer',
        'email' => 'jane@example.com',
        'enquiry_type' => 'join',
        'message' => 'I have not played since school but would like to start again.',
    ], $overrides));
}

it('sends the standard welcome letter to whoever used the contact form', function () {
    Mail::fake();

    submitEnquiry()->assertRedirect();

    Mail::assertSent(EnquiryAcknowledgement::class, function ($mail) {
        return $mail->hasTo('jane@example.com');
    });
});

it('sends a private notification to each configured administrator', function () {
    config(['club.enquiry_emails' => [
        'simonw21@yahoo.com',
        'david_filer@hotmail.com',
    ]]);
    Mail::fake();

    submitEnquiry()->assertRedirect();

    Mail::assertSent(EnquiryReceived::class, 2);
    Mail::assertSent(EnquiryReceived::class, function ($mail) {
        return $mail->hasTo('simonw21@yahoo.com') && count($mail->to) === 1;
    });
    Mail::assertSent(EnquiryReceived::class, function ($mail) {
        return $mail->hasTo('david_filer@hotmail.com') && count($mail->to) === 1;
    });
});

it('directs replies to the acknowledgement to the first configured administrator', function () {
    config(['club.enquiry_emails' => [
        'simonw21@yahoo.com',
        'david_filer@hotmail.com',
    ]]);

    $enquiry = Enquiry::create([
        'name' => 'Jane Newcomer',
        'email' => 'jane@example.com',
        'enquiry_type' => 'join',
        'message' => 'Hello there, I would like to visit the club.',
    ]);

    $replyTo = (new EnquiryAcknowledgement($enquiry))->envelope()->replyTo;

    expect($replyTo)->toHaveCount(1)
        ->and($replyTo[0]->address)->toBe('simonw21@yahoo.com');
});

it('still acknowledges the enquirer when no club notification address is set', function () {
    config(['club.enquiry_emails' => []]);
    Mail::fake();

    submitEnquiry()->assertRedirect();

    Mail::assertSent(EnquiryAcknowledgement::class);
    Mail::assertNotSent(EnquiryReceived::class);
});

it('does not acknowledge the enquirer when the automatic reply is switched off', function () {
    config(['club.auto_reply.enabled' => false]);
    Mail::fake();

    submitEnquiry()->assertRedirect();

    Mail::assertNotSent(EnquiryAcknowledgement::class);
});

it('stores the enquiry even when sending the email fails', function () {
    // A broken mail server must never cost the club the message.
    config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'invalid.invalid', 'mail.mailers.smtp.port' => 1]);

    submitEnquiry()->assertRedirect()->assertSessionHasNoErrors();

    expect(Enquiry::where('email', 'jane@example.com')->exists())->toBeTrue();
});

it('includes the club details a newcomer needs in the welcome letter', function () {
    $enquiry = Enquiry::create([
        'name' => 'Jane Newcomer',
        'email' => 'jane@example.com',
        'enquiry_type' => 'join',
        'message' => 'Hello there, I would like to visit the club.',
    ]);

    $html = (new EnquiryAcknowledgement($enquiry))->render();

    expect($html)
        ->toContain('Hi Jane Newcomer')
        ->toContain('Massey Ferguson Social Club')
        ->toContain('Banner Lane')
        ->toContain('Broad Lane')          // the entrance, which differs from the postal address
        ->toContain('CV5 7NL')
        ->toContain('7:30pm')
        ->toContain('4:30pm')
        ->toContain('£5 per session')
        ->toContain('Dave Filer')
        ->toContain('024 7641 1719')
        ->toContain('Simon Weaver')
        ->toContain('01455 221297')
        ->toContain('facebook.com/groups/coventrychessclub')
        ->toContain('Kind Regards');
});

it('leaves the 4NCL out of the welcome letter unless the club switches it on', function () {
    $enquiry = Enquiry::create([
        'name' => 'Jane Newcomer',
        'email' => 'jane@example.com',
        'enquiry_type' => 'join',
        'message' => 'Hello there, I would like to visit the club.',
    ]);

    expect((new EnquiryAcknowledgement($enquiry))->render())->not->toContain('4NCL');

    config(['club.teams.plays_4ncl' => true]);

    expect((new EnquiryAcknowledgement($enquiry))->render())->toContain('4NCL');
});

it('signs the welcome letter with the name and role on separate lines', function () {
    $enquiry = Enquiry::create([
        'name' => 'Jane Newcomer',
        'email' => 'jane@example.com',
        'enquiry_type' => 'join',
        'message' => 'Hello there, I would like to visit the club.',
    ]);

    $html = (new EnquiryAcknowledgement($enquiry))->render();

    // A newline inside a paragraph collapses to a space in every mail client,
    // which would read as a person called "Simon Weaver Club Secretary".
    expect($html)->not->toMatch('/Simon Weaver\s+Club Secretary/')
        ->and($html)->toContain('Simon Weaver')
        ->and($html)->toContain('Club Secretary');
});

it('keeps the league teams and the junior section in separate paragraphs', function () {
    $enquiry = Enquiry::create([
        'name' => 'Jane Newcomer',
        'email' => 'jane@example.com',
        'enquiry_type' => 'join',
        'message' => 'Hello there, I would like to visit the club.',
    ]);

    $html = (new EnquiryAcknowledgement($enquiry))->render();

    expect($html)->not->toMatch('/play friendlies\.\s*Our Junior section/');
});

it('does not sign the club emails as Laravel', function () {
    $enquiry = Enquiry::create([
        'name' => 'Jane Newcomer',
        'email' => 'jane@example.com',
        'enquiry_type' => 'join',
        'message' => 'Hello there, I would like to visit the club.',
    ]);

    expect((new EnquiryAcknowledgement($enquiry))->render())
        ->not->toContain('Laravel')
        ->toContain('Coventry Chess Club');
});
