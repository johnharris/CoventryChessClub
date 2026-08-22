<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class EmailTemplate extends Model
{
    public const ENQUIRY_ACKNOWLEDGEMENT = 'enquiry_acknowledgement';

    public const MEMBER_CONFIRMATION = 'member_confirmation';

    protected $fillable = [
        'key',
        'is_enabled',
        'subject',
        'body',
        'signature',
        'signature_role',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return [
            self::ENQUIRY_ACKNOWLEDGEMENT,
            self::MEMBER_CONFIRMATION,
        ];
    }

    /** @return array<string, mixed> */
    public static function definition(string $key): array
    {
        $definition = config("email_templates.$key");

        if (! is_array($definition)) {
            throw new InvalidArgumentException("Unknown email template [$key].");
        }

        return $definition;
    }

    public static function current(string $key): self
    {
        return self::query()->where('key', $key)->first()
            ?? new self(self::defaults($key));
    }

    /** @return array<string, mixed> */
    public static function defaults(string $key): array
    {
        $definition = self::definition($key);

        return [
            'key' => $key,
            'is_enabled' => (bool) ($definition['enabled'] ?? true),
            'subject' => (string) ($definition['subject'] ?? ''),
            'body' => (string) ($definition['body'] ?? ''),
            'signature' => (string) ($definition['signature'] ?? ''),
            'signature_role' => (string) ($definition['signature_role'] ?? ''),
        ];
    }

    /** @return list<string> */
    public function allowedPlaceholders(): array
    {
        return array_values(self::definition($this->key)['placeholders'] ?? []);
    }

    /** @param array<string, string> $values */
    public function renderSubject(array $values): string
    {
        return $this->replacePlaceholders((string) $this->subject, $values);
    }

    /** @param array<string, string> $values */
    public function renderBody(array $values): string
    {
        return $this->replacePlaceholders((string) $this->body, $values);
    }

    /** @param array<string, string> $values */
    protected function replacePlaceholders(string $content, array $values): string
    {
        $replacements = [];

        foreach ($this->allowedPlaceholders() as $placeholder) {
            $replacements['{{'.$placeholder.'}}'] = $values[$placeholder] ?? '';
        }

        return strtr($content, $replacements);
    }
}
