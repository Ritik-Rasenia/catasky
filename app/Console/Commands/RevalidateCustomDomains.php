<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomDomain;

class RevalidateCustomDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-domain:revalidate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform daily DNS verification and SSL/domain expiration checks for all active custom domains';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting custom domain revalidation process...');

        // Get all active custom domains
        $domains = CustomDomain::where('status', 'Active')->get();

        foreach ($domains as $domain) {
            $this->info("Revalidating domain: {$domain->domain}...");

            // 1. Re-run DNS verification
            $dnsSuccess = $domain->verifyDns();

            if ($dnsSuccess) {
                // Update revalidation timestamp
                $domain->update([
                    'last_revalidated_at' => now(),
                    'dns_mismatch_detected' => false
                ]);

                $domain->log('daily_revalidation', 'success', 'Daily DNS revalidation check passed. Configuration remains valid.');

                // 2. Expiry checks
                // Domain registrar expiration check (warning if <= 30 days)
                if ($domain->domain_expires_at) {
                    $daysToDomainExpiry = now()->diffInDays($domain->domain_expires_at, false);
                    if ($daysToDomainExpiry <= 30) {
                        $domain->log('daily_revalidation', 'info', "Domain registrar expiration warning: domain expires in {$daysToDomainExpiry} days on " . $domain->domain_expires_at->format('Y-m-d'));
                    }
                }

                // SSL certificate expiration check (warning if <= 14 days)
                if ($domain->ssl_expires_at) {
                    $daysToSslExpiry = now()->diffInDays($domain->ssl_expires_at, false);
                    if ($daysToSslExpiry <= 14) {
                        $domain->log('daily_revalidation', 'info', "SSL certificate expiration warning: SSL expires in {$daysToSslExpiry} days on " . $domain->ssl_expires_at->format('Y-m-d'));
                    }
                }
            } else {
                // Auto Disable on DNS Failure
                $domain->update([
                    'status' => 'Pending DNS Setup',
                    'ssl_status' => 'SSL Pending',
                    'dns_mismatch_detected' => true,
                    'last_revalidated_at' => now()
                ]);

                $domain->log('auto_disabled', 'failed', 'DNS mismatch detected during daily revalidation. Custom domain routing auto-disabled.');

                // Clear the mapping on the subscriber profile
                $profile = $domain->user->subscriberProfile ?? null;
                if ($profile && $profile->user_id === $domain->user_id) {
                    $profile->update([
                        'custom_domain' => null,
                        'domain_verified' => false
                    ]);
                }

                $this->warn("Custom domain {$domain->domain} has been deactivated due to DNS mismatch.");
            }
        }

        $this->info('Custom domain revalidation process completed.');
    }
}
