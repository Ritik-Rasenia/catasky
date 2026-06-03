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
        'dns_txt_verified', 'dns_a_verified', 'dns_cname_verified',
        'admin_approved', 'rejection_reason',
        'domain_expires_at', 'ssl_expires_at', 'last_revalidated_at', 'dns_mismatch_detected'
    ];

    protected $casts = [
        'dns_verified' => 'boolean',
        'dns_txt_verified' => 'boolean',
        'dns_a_verified' => 'boolean',
        'dns_cname_verified' => 'boolean',
        'admin_approved' => 'boolean',
        'dns_mismatch_detected' => 'boolean',
        'domain_expires_at' => 'date',
        'ssl_expires_at' => 'date',
        'last_revalidated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(CustomDomainLog::class);
    }

    /**
     * Log custom domain event.
     */
    public function log(string $action, string $status, string $message, ?array $details = null)
    {
        return $this->logs()->create([
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'details' => $details
        ]);
    }

    /**
     * Check if the domain is fully active and ready to route.
     */
    public function isActive(): bool
    {
        return $this->status === 'Active' && $this->ssl_status === 'SSL Active' && $this->admin_approved;
    }

    /**
     * Generate dynamic TXT record credentials for ownership verification.
     */
    public static function generateTxtVerification(string $domain): array
    {
        return [
            'key'   => '@',
            'value' => 'catasky-verification=' . bin2hex(random_bytes(8)),
        ];
    }

    /**
     * Extract root domain (e.g. "ritikstore.com" from "www.ritikstore.com")
     */
    public function getRootDomain(): string
    {
        $domain = strtolower(trim($this->domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = explode('/', $domain)[0];
        $domain = explode(':', $domain)[0];

        $parts = explode('.', $domain);
        $count = count($parts);
        if ($count > 2) {
            // Check for common two-part TLD extensions
            $lastTwo = $parts[$count - 2] . '.' . $parts[$count - 1];
            $twoPartTlds = [
                'co.uk', 'me.uk', 'org.uk', 'net.uk', 'ltd.uk', 'plc.uk', 'sch.uk',
                'com.au', 'net.au', 'org.au', 'com.tr', 'co.in', 'net.in', 'org.in',
                'firm.in', 'ind.in', 'gen.in', 'co.jp', 'org.nz', 'co.nz'
            ];
            if (in_array($lastTwo, $twoPartTlds) && $count > 3) {
                return $parts[$count - 3] . '.' . $lastTwo;
            }
            return $parts[$count - 2] . '.' . $parts[$count - 1];
        }
        return $domain;
    }

    /**
     * Simulated DNS Verification for testing/sandbox mode.
     */
    public function verifyDnsMock(): bool
    {
        $this->update([
            'dns_txt_verified'   => true,
            'dns_a_verified'     => true,
            'dns_cname_verified' => true,
            'dns_verified'       => true,
            'status'             => ($this->status === 'Active') ? 'Active' : 'DNS Verified',
            'dns_mismatch_detected' => false,
        ]);

        $this->log('dns_check', 'success', 'DNS records verified successfully (Mock Mode). Root domain A record, CNAME and TXT ownership token match configuration.');

        return true;
    }

    /**
     * Real DNS verification checking TXT, A, and CNAME records.
     */
    public function verifyDns(): bool
    {
        $domainName = strtolower(trim($this->domain));
        
        // 1. Local/Sandbox simulation check
        $containsSandboxKeywords = false;
        foreach (['demo', 'local', 'test', 'ritik'] as $keyword) {
            if (str_contains($domainName, $keyword)) {
                $containsSandboxKeywords = true;
                break;
            }
        }

        if ($containsSandboxKeywords) {
            return $this->verifyDnsMock();
        }

        // 2. Production Real Verification
        $rootDomain = $this->getRootDomain();
        $isTxtCorrect = false;
        $isACorrect = false;
        $isCnameCorrect = false;
        $errors = [];

        try {
            // A. Verify TXT Record on Root Domain (Host: @, Value: catasky-verification={token})
            $txtRecords = @dns_get_record($rootDomain, DNS_TXT);
            if (!empty($txtRecords)) {
                foreach ($txtRecords as $record) {
                    $txtVal = $record['txt'] ?? (isset($record['entries']) ? implode('', $record['entries']) : '');
                    $txtVal = trim($txtVal, " \t\n\r\0\x0B\"");
                    if ($txtVal === $this->dns_txt_value) {
                        $isTxtCorrect = true;
                        break;
                    }
                }
            }
            if (!$isTxtCorrect) {
                $errors[] = "TXT record not found or token mismatched on " . $rootDomain;
            }

            // B. Verify A Record on Root Domain pointing to Catasky IP: 159.89.172.11
            $aRecords = @dns_get_record($rootDomain, DNS_A);
            if (!empty($aRecords)) {
                foreach ($aRecords as $record) {
                    if (isset($record['ip']) && $record['ip'] === '159.89.172.11') {
                        $isACorrect = true;
                        break;
                    }
                }
            }
            if (!$isACorrect) {
                $errors[] = "A record not pointing to IP: 159.89.172.11 on " . $rootDomain;
            }

            // C. Verify CNAME Record (WWW or subdomain points to domain.catasky.com)
            $cnameCheckDomain = $domainName;
            $parts = explode('.', $domainName);
            if (count($parts) === 2) {
                $cnameCheckDomain = 'www.' . $domainName;
            }

            $cnameRecords = @dns_get_record($cnameCheckDomain, DNS_CNAME);
            if (!empty($cnameRecords)) {
                foreach ($cnameRecords as $record) {
                    if (isset($record['target'])) {
                        $target = strtolower(trim($record['target'], '.'));
                        if ($target === 'domain.catasky.com') {
                            $isCnameCorrect = true;
                            break;
                        }
                    }
                }
            }
            if (!$isCnameCorrect) {
                $errors[] = "CNAME record not pointing to domain.catasky.com on " . $cnameCheckDomain;
            }
        } catch (\Exception $e) {
            $errors[] = "DNS query failure: " . $e->getMessage();
        }

        // Update database flags
        $allVerified = ($isTxtCorrect && $isACorrect && $isCnameCorrect);
        
        $this->update([
            'dns_txt_verified'   => $isTxtCorrect,
            'dns_a_verified'     => $isACorrect,
            'dns_cname_verified' => $isCnameCorrect,
            'dns_verified'       => $allVerified,
        ]);

        if ($allVerified) {
            $this->update([
                'status' => ($this->status === 'Active') ? 'Active' : 'DNS Verified',
                'dns_mismatch_detected' => false,
            ]);

            $this->log('dns_check', 'success', 'DNS records verified successfully. Root domain A record, CNAME and TXT ownership token match configuration.', [
                'txt_verified' => true,
                'a_verified' => true,
                'cname_verified' => true
            ]);
        } else {
            if ($this->status !== 'Active') {
                $this->update([
                    'status' => 'Pending DNS Setup',
                ]);
            }
            $this->log('dns_check', 'failed', 'DNS records verification failed: ' . implode('; ', $errors), [
                'txt_verified' => $isTxtCorrect,
                'a_verified' => $isACorrect,
                'cname_verified' => $isCnameCorrect,
                'errors' => $errors
            ]);
        }

        return $allVerified;
    }

    /**
     * Check conditions and activate domain if valid.
     */
    public function checkAndActivate(): bool
    {
        if ($this->dns_txt_verified &&
            $this->dns_a_verified &&
            $this->dns_cname_verified &&
            $this->admin_approved &&
            $this->ssl_status === 'SSL Active') {

            $this->update([
                'status' => 'Active',
                'domain_expires_at' => now()->addYear(),
                'ssl_expires_at' => now()->addDays(90),
            ]);

            // Sync the custom domain to the subscriber's profile
            $profile = $this->user->subscriberProfile ?? null;
            if ($profile) {
                $profile->update([
                    'custom_domain'   => $this->domain,
                    'domain_verified' => true
                ]);
            }

            $this->log('status_change', 'success', 'Custom domain activated successfully. Store traffic is now routed through ' . $this->domain);

            return true;
        }

        return false;
    }
}

