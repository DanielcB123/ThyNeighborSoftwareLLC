<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Support\Str;

class OnboardingAppointment extends CentralModel
{
    use HasPublicId;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'tenant_public_id',
        'business_name',
        'contact_name',
        'contact_email',
        'status',
        'scheduled_at',
        'timezone',
        'duration_minutes',
        'zoom_meeting_id',
        'zoom_join_url',
        'zoom_start_url',
        'zoom_passcode',
        'zoom_status',
        'access_token_hash',
        'cancelled_at',
        'last_zoom_synced_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'access_token_hash',
        'zoom_start_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_zoom_synced_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public static function issueAccessToken(): string
    {
        return Str::random(64);
    }

    public function setAccessToken(string $token): void
    {
        $this->access_token_hash = hash('sha256', $token);
    }

    public function matchesAccessToken(?string $token): bool
    {
        if (! is_string($token) || $token === '') {
            return false;
        }

        $hash = hash('sha256', $token);

        return hash_equals((string) $this->access_token_hash, $hash);
    }
}
