<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\CustomDomain;
use App\Models\SubscriberActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class DomainController extends Controller
{
    /**
     * Enforce strict enterprise subscriber access.
     */
    protected function ensureEnterprise()
    {
        $user = auth()->user();
        $sub = $user ? $user->activeSubscription() : null;
        $plan = $sub ? $sub->plan : null;
        $isEnterprise = $plan && ($plan->slug === 'enterprise' || $plan->custom_branding);

        if (!$isEnterprise || !$user->hasActiveSubscription()) {
            abort(403, 'Unauthorized. Only Enterprise subscribers can access the custom domain management page.');
        }

        return [$user, $plan];
    }

    public function index()
    {
        list($user, $plan) = $this->ensureEnterprise();

        $domains = CustomDomain::where('user_id', $user->id)->with('logs')->latest()->get();

        return view('subscriber-panel.domain.index', [
            'isEnterprise' => true,
            'domains' => $domains,
            'plan' => $plan,
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {
        list($user, $plan) = $this->ensureEnterprise();

        // One domain per Enterprise store
        $existingCount = CustomDomain::where('user_id', $user->id)->count();
        if ($existingCount >= 1) {
            return back()->with('error', 'Maximum limit reached. Your Enterprise plan supports 1 active custom domain.');
        }

        // 1. Sanitize the domain: strip http://, https://, and trailing slash
        $domainName = strtolower(trim($request->domain));
        $domainName = preg_replace('#^https?://#', '', $domainName);
        $domainName = rtrim($domainName, '/');

        // Merge back to request so validation uses sanitized domain name
        $request->merge(['domain' => $domainName]);

        // 2. Invalid characters validation
        if (preg_match('/[^a-z0-9.-]/', $domainName)) {
            return back()->withErrors(['domain' => 'Domain name contains invalid characters. Only letters, numbers, dots (.) and hyphens (-) are allowed.'])->withInput();
        }

        // 3. Domain format validation
        if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,11}$/', $domainName)) {
            return back()->withErrors(['domain' => 'Please enter a valid domain name format (e.g. store.company.com).'])->withInput();
        }

        // 4. Force delete soft deleted domains to prevent database unique key collision
        CustomDomain::onlyTrashed()->where('domain', $domainName)->forceDelete();

        // 5. Reserved domain validation
        $reservedDomains = ['catasky.com', 'admin.catasky.com', 'api.catasky.com', 'mail.catasky.com', 'app.catasky.com', 'www.catasky.com', 'localhost', 'example.com', 'example.org', 'example.net'];
        $isReserved = collect($reservedDomains)->contains(function ($reserved) use ($domainName) {
            return $domainName === $reserved || str_ends_with($domainName, '.' . $reserved);
        });
        if ($isReserved || str_ends_with($domainName, 'catasky.com')) {
            return back()->withErrors(['domain' => 'This domain is reserved for system use and cannot be mapped.'])->withInput();
        }

        // 6. Blacklisted domain validation
        $blacklist = ['phishing', 'malware', 'scam', 'hack', 'porn', 'gamble', 'spam', 'xyx', 'free-money'];
        foreach ($blacklist as $badWord) {
            if (str_contains($domainName, $badWord)) {
                return back()->withErrors(['domain' => 'This domain name contains prohibited keywords and is blacklisted.'])->withInput();
            }
        }

        // 7. Domain existence validation (Resolves IP/DNS records except for sandbox/local env or keywords)
        $isSandbox = config('app.env') === 'local' || collect(['demo', 'local', 'test', 'ritik'])->contains(function ($kw) use ($domainName) {
            return str_contains($domainName, $kw);
        });

        if (!$isSandbox) {
            if (!checkdnsrr($domainName, 'ANY') && !checkdnsrr($domainName, 'A') && !checkdnsrr($domainName, 'CNAME') && !checkdnsrr($domainName, 'MX') && !checkdnsrr($domainName, 'NS')) {
                return back()->withErrors(['domain' => 'The domain name does not exist or has no active DNS zone records.'])->withInput();
            }
        }

        // 8. Duplicate domain validation against BOTH custom_domains AND subscriber_profiles tables
        $validator = Validator::make($request->all(), [
            'domain' => [
                'required',
                'string',
                'unique:custom_domains,domain',
                'unique:subscriber_profiles,custom_domain',
            ]
        ], [
            'domain.unique' => 'This custom domain has already been mapped to another store.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Generate verification codes
        $verification = CustomDomain::generateTxtVerification($domainName);

        $domain = CustomDomain::create([
            'user_id'            => $user->id,
            'domain'             => $domainName,
            'status'             => 'Pending DNS Setup',
            'ssl_status'         => 'SSL Pending',
            'dns_txt_key'        => $verification['key'], // @
            'dns_txt_value'      => $verification['value'],
            'dns_verified'       => false,
            'dns_txt_verified'   => false,
            'dns_a_verified'     => false,
            'dns_cname_verified' => false,
            'admin_approved'     => false,
        ]);

        $domain->log('created', 'info', 'Custom domain requested and initialized as Pending DNS Setup.');

        SubscriberActivityLog::log('created', 'Requested custom domain mapping: ' . $domainName, $domain);

        return redirect()->route('subscriber.domain.index')
            ->with('success', 'Custom domain mapping request submitted successfully! Please configure DNS settings.');
    }

    public function verify(Request $request)
    {
        $this->ensureEnterprise();

        $request->validate(['domain_id' => 'required|exists:custom_domains,id']);
        $domain = CustomDomain::findOrFail($request->domain_id);

        if ($domain->user_id !== auth()->id()) {
            abort(403);
        }

        // Trigger DNS verification flow (sets verified flags and updates status)
        $success = $domain->verifyDns();

        // Run activation check (activates only if all conditions met including admin approval)
        $domain->checkAndActivate();

        SubscriberActivityLog::log('updated', 'Triggered DNS verification check for custom domain: ' . $domain->domain, $domain);

        if ($success) {
            $msg = '🎉 DNS Verification Successful! ';
            if ($domain->status === 'Active') {
                $msg .= 'Domain is now active and routing traffic.';
            } else {
                $msg .= 'Domain DNS verified successfully. Pending Admin approval to go live.';
            }
            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        }

        // Fetch the latest verification failure message
        $latestFailLog = $domain->logs()->where('action', 'dns_check')->where('status', 'failed')->latest()->first();
        $errorMsg = $latestFailLog ? $latestFailLog->message : 'DNS verification failed. Please ensure TXT, A, and CNAME records are correctly configured.';

        return response()->json([
            'success' => false,
            'message' => $errorMsg
        ]);
    }

    public function destroy($id)
    {
        $this->ensureEnterprise();

        $domain = CustomDomain::findOrFail($id);

        if ($domain->user_id !== auth()->id()) {
            abort(403);
        }

        $domainName = $domain->domain;

        // Clear custom domain on subscriber profile if this is the active domain
        $profile = auth()->user()->subscriberProfile;
        if ($profile && $profile->custom_domain === $domainName) {
            $profile->update([
                'custom_domain' => null,
                'domain_verified' => false
            ]);
        }

        $domain->log('deleted', 'info', 'Custom domain request deleted by subscriber.');
        $domain->delete();

        SubscriberActivityLog::log('deleted', 'Removed custom domain mapping: ' . $domainName, $domain);

        return redirect()->route('subscriber.domain.index')
            ->with('success', 'Custom domain mapping removed successfully.');
    }
}

