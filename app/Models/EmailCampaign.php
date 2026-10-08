<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $guarded = [];

    protected $casts = ['sent_at' => 'datetime'];

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /** Replace {{name}} / {{email}} (case-insensitive, spaces allowed). */
    public static function personalize(string $text, string $name, string $email): string
    {
        return preg_replace_callback(
            '/\{\{\s*(name|email)\s*\}\}/i',
            fn ($m) => strtolower($m[1]) === 'name' ? $name : $email,
            $text
        );
    }

    /**
     * Returns [subject, safeHtmlBody] for one recipient.
     * The body is HTML-escaped FIRST, then placeholders are filled with escaped
     * values, so neither the template nor the CSV data can inject HTML.
     */
    public function renderFor(string $name, string $email): array
    {
        $subject = self::personalize($this->subject, $name, $email);
        $subject = trim(preg_replace('/[\r\n]+/', ' ', $subject)); // no header injection

        $html = nl2br(self::personalize(e($this->body), e($name), e($email)));

        return [$subject, $html];
    }

    public function syncCounts(): void
    {
        $this->update([
            'sent_count'   => $this->recipients()->where('status', 'sent')->count(),
            'failed_count' => $this->recipients()->where('status', 'failed')->count(),
        ]);
    }
}
