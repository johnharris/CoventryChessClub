<?php

use App\Mail\EnquiryAcknowledgement;
use App\Mail\MemberConfirmation;
use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\Page;
use App\Models\User;
use App\Models\WhitelistEntry;
use Database\Seeders\MarkdownCheatsheetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function emailSettingsAdmin(): User
{
    return User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);
}

function emailSettingsMember(): User
{
    return User::factory()->create([
        'role' => User::ROLE_MEMBER,
        'is_active' => true,
    ]);
}

/** @return array<string, mixed> */
function emailSettingsPayload(string $key, array $overrides = []): array
{
    $defaults = EmailTemplate::defaults($key);

    return array_merge([
        'action' => 'save',
        'is_enabled' => '1',
        'subject' => $defaults['subject'],
        'body' => $defaults['body'],
        'signature' => $defaults['signature'],
        'signature_role' => $defaults['signature_role'],
    ], $overrides);
}

it('keeps automated email settings for administrators only', function () {
    $this->get('/members/emails')->assertRedirect('/login');

    $this->actingAs(emailSettingsMember())
        ->get('/members/emails')
        ->assertForbidden();

    $this->actingAs(emailSettingsAdmin())
        ->get('/members/emails')
        ->assertOk()
        ->assertSee('Automated emails')
        ->assertSee('Enquiry acknowledgement')
        ->assertSee('Member confirmation');
});

it('shows safe defaults without creating template rows on a fresh installation', function () {
    $this->actingAs(emailSettingsAdmin())
        ->get('/members/emails')
        ->assertOk()
        ->assertSee('Thank you for your interest in Coventry Chess Club')
        ->assertSee('{{name}}');

    $this->assertDatabaseCount('email_templates', 0);
});

it('lets an administrator save and render a customized enquiry acknowledgement', function () {
    $admin = emailSettingsAdmin();

    $this->actingAs($admin)
        ->post(route('members.emails.handle', EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT), emailSettingsPayload(
            EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT,
            [
                'subject' => 'Thanks, {{name}}',
                'body' => "Hi {{name}},\n\n**Your custom acknowledgement is ready.**",
                'signature' => 'The Club Team',
                'signature_role' => 'Coventry Chess Club',
            ],
        ))
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('email_templates', [
        'key' => EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT,
        'is_enabled' => true,
        'subject' => 'Thanks, {{name}}',
        'signature' => 'The Club Team',
    ]);

    $enquiry = Enquiry::create([
        'name' => 'Jamie Visitor',
        'email' => 'jamie@example.com',
        'enquiry_type' => 'join',
        'message' => 'I would like to come along next Tuesday.',
    ]);
    $mail = new EnquiryAcknowledgement($enquiry);

    expect($mail->envelope()->subject)->toBe('Thanks, Jamie Visitor');
    expect($mail->render())
        ->toContain('Hi Jamie Visitor')
        ->toContain('Your custom acknowledgement is ready.')
        ->toContain('The Club Team');
});

it('rejects unsupported placeholders', function () {
    $this->actingAs(emailSettingsAdmin())
        ->from(route('members.emails.edit', ['template' => EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT]))
        ->post(route('members.emails.handle', EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT), emailSettingsPayload(
            EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT,
            ['body' => 'Hello {{name}}. Your password is {{password}}.'],
        ))
        ->assertSessionHasErrors('body');

    $this->assertDatabaseCount('email_templates', 0);
});

it('previews unsaved Markdown safely without changing the database', function () {
    $this->actingAs(emailSettingsAdmin())
        ->post(route('members.emails.handle', EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT), emailSettingsPayload(
            EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT,
            [
                'action' => 'preview',
                'body' => "## Preview for {{name}}\n\n<script>alert('no')</script>",
            ],
        ))
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Administrator preview/test only')
        ->assertSee('Preview for Alex Visitor')
        ->assertSee('&lt;script&gt;', false)
        ->assertDontSee("<script>alert('no')</script>", false);

    $this->assertDatabaseCount('email_templates', 0);
});

it('sends an unsaved test only to the signed-in administrator', function () {
    Mail::fake();
    $admin = emailSettingsAdmin();

    $this->actingAs($admin)
        ->post(route('members.emails.handle', EmailTemplate::MEMBER_CONFIRMATION), emailSettingsPayload(
            EmailTemplate::MEMBER_CONFIRMATION,
            [
                'action' => 'test',
                'subject' => 'Welcome test for {{name}}',
            ],
        ))
        ->assertRedirect()
        ->assertSessionHas('status');

    Mail::assertSent(MemberConfirmation::class, function (MemberConfirmation $mail) use ($admin): bool {
        return $mail->hasTo($admin->email)
            && $mail->isTest
            && $mail->envelope()->subject === '[Test] Welcome test for Alex Member';
    });
    Mail::assertSentCount(1);
    $this->assertDatabaseCount('email_templates', 0);
});

it('restores the approved default wording', function () {
    EmailTemplate::query()->create([
        'key' => EmailTemplate::MEMBER_CONFIRMATION,
        'is_enabled' => false,
        'subject' => 'Temporary subject',
        'body' => 'Temporary body',
        'signature' => 'Temporary name',
        'signature_role' => 'Temporary role',
    ]);

    $this->actingAs(emailSettingsAdmin())
        ->post(route('members.emails.handle', EmailTemplate::MEMBER_CONFIRMATION), [
            'action' => 'reset',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('email_templates', EmailTemplate::defaults(EmailTemplate::MEMBER_CONFIRMATION));
});

it('uses the saved acknowledgement for a public enquiry', function () {
    EmailTemplate::query()->create([
        ...EmailTemplate::defaults(EmailTemplate::ENQUIRY_ACKNOWLEDGEMENT),
        'subject' => 'Hello {{name}} from the club',
        'body' => 'This is the saved message for {{name}}.',
    ]);
    Mail::fake();

    $this->post('/contact', [
        'name' => 'Taylor Visitor',
        'email' => 'taylor@example.com',
        'enquiry_type' => 'join',
        'message' => 'I would like to visit the club next week.',
    ])->assertRedirect();

    Mail::assertSent(EnquiryAcknowledgement::class, function (EnquiryAcknowledgement $mail): bool {
        return $mail->hasTo('taylor@example.com')
            && $mail->envelope()->subject === 'Hello Taylor Visitor from the club'
            && str_contains($mail->render(), 'This is the saved message for Taylor Visitor.');
    });
});

it('sends the saved member confirmation after successful registration', function () {
    EmailTemplate::query()->create([
        ...EmailTemplate::defaults(EmailTemplate::MEMBER_CONFIRMATION),
        'subject' => 'Welcome to the members area, {{name}}',
        'body' => 'Your account for {{email}} is ready. Sign in at {{login_url}}.',
    ]);
    WhitelistEntry::create([
        'email' => 'new.member@example.com',
        'name' => 'New Member',
        'role' => User::ROLE_MEMBER,
        'invite_token' => WhitelistEntry::freshToken(),
    ]);
    Mail::fake();

    $this->post('/register', [
        'name' => 'New Member',
        'email' => 'new.member@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
    ])->assertRedirect(route('members.dashboard'));

    Mail::assertSent(MemberConfirmation::class);

    $mail = Mail::sent(MemberConfirmation::class)->first();

    expect($mail->hasTo('new.member@example.com'))->toBeTrue()
        ->and($mail->envelope()->subject)->toBe('Welcome to the members area, New Member')
        ->and($mail->render())->toContain('Your account for')
        ->toContain('new.member@example.com')
        ->toContain('is ready.');
});

it('does not send the member confirmation when administrators disable it', function () {
    EmailTemplate::query()->create([
        ...EmailTemplate::defaults(EmailTemplate::MEMBER_CONFIRMATION),
        'is_enabled' => false,
    ]);
    WhitelistEntry::create([
        'email' => 'quiet.member@example.com',
        'role' => User::ROLE_MEMBER,
        'invite_token' => WhitelistEntry::freshToken(),
    ]);
    Mail::fake();

    $this->post('/register', [
        'name' => 'Quiet Member',
        'email' => 'quiet.member@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
    ])->assertRedirect(route('members.dashboard'));

    Mail::assertNotSent(MemberConfirmation::class);
    $this->assertAuthenticated();
});

it('keeps the member account when the confirmation mail server fails', function () {
    config([
        'mail.default' => 'smtp',
        'mail.mailers.smtp.host' => 'invalid.invalid',
        'mail.mailers.smtp.port' => 1,
    ]);
    WhitelistEntry::create([
        'email' => 'resilient.member@example.com',
        'role' => User::ROLE_MEMBER,
        'invite_token' => WhitelistEntry::freshToken(),
    ]);

    $this->post('/register', [
        'name' => 'Resilient Member',
        'email' => 'resilient.member@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
    ])->assertRedirect(route('members.dashboard'))
        ->assertSessionHasNoErrors();

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'resilient.member@example.com']);
});

it('creates a private reusable Markdown cheatsheet without overwriting later edits', function () {
    $this->seed(MarkdownCheatsheetSeeder::class);

    $page = Page::where('slug', 'markdown-cheatsheet')->firstOrFail();

    expect($page->is_published)->toBeFalse()
        ->and($page->show_in_nav)->toBeFalse()
        ->and($page->body)->toContain('## Headings')
        ->and($page->body)->toContain('| Player | Score | Result |')
        ->and($page->body)->toContain('```fen')
        ->and($page->body)->toContain('## Images');

    $this->get('/markdown-cheatsheet')->assertNotFound();
    $this->get('/')->assertOk()->assertDontSee('Markdown Cheatsheet');
    $this->actingAs(emailSettingsMember())
        ->get('/markdown-cheatsheet')
        ->assertNotFound();

    $admin = emailSettingsAdmin();
    $this->actingAs($admin)
        ->get(route('members.pages.edit', $page))
        ->assertOk()
        ->assertSee('Markdown Cheatsheet')
        ->assertSee('## Headings')
        ->assertSee('Preview unpublished page');
    $this->actingAs($admin)
        ->get('/markdown-cheatsheet')
        ->assertOk()
        ->assertSee('Administrator preview')
        ->assertSee('Bold, italics and crossed-out text')
        ->assertSee('data-fen=', false);

    $page->update(['body' => 'Administrator revision']);
    $this->seed(MarkdownCheatsheetSeeder::class);

    expect($page->fresh()->body)->toBe('Administrator revision');
});
