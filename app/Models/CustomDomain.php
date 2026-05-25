<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomDomain extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'domain', 'status', 'ssl_status',
        'dns_txt_key', 'dns_txt_value', 'dns_verified',
    ];

    protected $casts = [
        'dns_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the domain is fully active and ready to route.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Generate dynamic TXT record credentials for ownership verification.
     */
    public static function generateTxtVerification(string $domain): array
    {
        return [
            'key'   => '_catasky-challenge.' . parse_url($domain, PHP_URL_HOST) ?: $domain,
            'value' => 'catasky-verification-code-' . bin2hex(random_bytes(16)),
        ];
    }

    /**
     * Simulated DNS Verification for testing/sandbox mode.
     */
    public function verifyDnsMock(): bool
    {
        // Mock verification - always returns true for seamless demo flow
        $this->update([
            'dns_verified' => true,
            'status'       => 'approved',
            'ssl_status'   => 'active',
        ]);
        return true;
    }
}
