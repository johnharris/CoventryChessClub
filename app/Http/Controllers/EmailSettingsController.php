<?php

namespace App\Http\Controllers;

use App\Mail\EnquiryAcknowledgement;
use App\Mail\MemberConfirmation;
use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class EmailSettingsController extends Controller
{
    public function edit(): View
    {
        $templates = collect(EmailTemplate::keys())->mapWithKeys(function (string $key): array {
            return [$key => [
                'template' => EmailTemplate::current($key),
                'definition' => EmailTemplate::definition($key),
            ]];
        });

        return view('members.admin.emails.edit', compact('templates'));
    }

    public function handle(Request $request, string $template): RedirectResponse|Response
    {
        $key = $this->validatedKey($template);
        $action = $request->validate([
            'action' => ['required', Rule::in(['save', 'preview', 'test', 'reset'])],
        ])['action'];

        if ($action === 'reset') {
            EmailTemplate::query()->updateOrCreate(
                ['key' => $key],
                EmailTemplate::defaults($key),
            );

            return $this->redirectToEditor($key)
                ->with('status', EmailTemplate::definition($key)['label'].' restored to its default wording.');
        }

        $data = $this->validatedTemplate($request, $key);

        if ($action === 'preview') {
            return response($this->mailable($key, $data, true)->render())
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        if ($action === 'test') {
            try {
                Mail::to($request->user()->email, $request->user()->publicName())
                    ->send($this->mailable($key, $data, true));
            } catch (Throwable $exception) {
                report($exception);

                return back()->withErrors([
                    'test_email' => 'The test email could not be sent. The settings were not changed.',
                ])->withInput();
            }

            return back()->with('status', 'Test email sent to '.$request->user()->email.'. The settings were not changed.');
        }

        EmailTemplate::query()->updateOrCreate(['key' => $key], $data);

        return $this->redirectToEditor($key)
            ->with('status', EmailTemplate::definition($key)['label'].' saved.');
    }

    /** @return array<string, mixed> */
    protected function validatedTemplate(Request $request, string $key): array
    {
        $limits = config('email_templates.limits');
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:'.$limits['subject']],
            'body' => ['required', 'string', 'max:'.$limits['body']],
            'signature' => ['nullable', 'string', 'max:'.$limits['signature']],
            'signature_role' => ['nullable', 'string', 'max:'.$limits['signature_role']],
        ]);

        $allowed = EmailTemplate::definition($key)['placeholders'];
        $used = $this->placeholdersIn($data['subject']."\n".$data['body']);
        $unsupported = array_values(array_diff($used, $allowed));

        if ($unsupported !== []) {
            throw ValidationException::withMessages([
                'body' => 'Unsupported placeholder'.(count($unsupported) === 1 ? '' : 's').': '
                    .collect($unsupported)->map(fn (string $name): string => '{{'.$name.'}}')->join(', '),
            ]);
        }

        return [
            'is_enabled' => $request->boolean('is_enabled'),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'signature' => $data['signature'] ?? '',
            'signature_role' => $data['signature_role'] ?? '',
        ];
    }

    /** @return list<string> */
    protected function placeholdersIn(string $content): array
    {
        preg_match_all('/\{\{\s*([a-z_]+)\s*\}\}/', $content, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    protected function validatedKey(string $key): string
    {
        abort_unless(in_array($key, EmailTemplate::keys(), true), 404);

        return $key;
    }

    /** @param array<string, mixed> $data */
    protected function mailable(string $key, array $data, bool $isTest): Mailable
    {
        if ($key === EmailTemplate::MEMBER_CONFIRMATION) {
            $member = new User([
                'name' => 'Alex Member',
                'email' => 'alex.member@example.com',
            ]);

            return new MemberConfirmation($member, $data, $isTest);
        }

        $enquiry = new Enquiry([
            'name' => 'Alex Visitor',
            'email' => 'alex.visitor@example.com',
            'enquiry_type' => 'join',
            'message' => 'I would like to visit the club.',
        ]);

        return new EnquiryAcknowledgement($enquiry, $data, $isTest);
    }

    protected function redirectToEditor(string $key): RedirectResponse
    {
        return redirect()->route('members.emails.edit', ['template' => $key]);
    }
}
