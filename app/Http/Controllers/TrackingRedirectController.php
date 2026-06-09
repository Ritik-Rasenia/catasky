<?php

namespace App\Http\Controllers;

use App\Models\EngagementLog;
use App\Models\SubscriberProduct;
use App\Models\SubscriberShareLink;
use Illuminate\Http\Request;

class TrackingRedirectController extends Controller
{
    public function pdfProductClick(Request $request)
    {
        $productSlug = $request->query('product');
        $ref = $request->query('ref');
        $tk = $request->query('tk');

        $product = SubscriberProduct::where('slug', $productSlug)->first();
        $shareLink = SubscriberShareLink::where('token', $ref)->first();

        if ($product && $shareLink) {
            EngagementLog::create([
                'subscriber_share_link_id' => $shareLink->id,
                'user_id'                  => $shareLink->user_id,
                'event_type'               => 'pdf_product_click',
                'subscriber_product_id'    => $product->id,
                'metadata'                 => [
                    'tracking_token' => $tk,
                    'source'         => 'pdf',
                ],
            ]);
        }

        $companySlug = $shareLink?->user?->profile?->company_slug;
        $redirectUrl = route('product.details', ['slug' => $productSlug]);
        if ($companySlug) {
            $redirectUrl .= '?is_subscriber=1&company_slug=' . urlencode($companySlug);
        }

        return redirect()->away($redirectUrl);
    }

    public function catalogueOpen(Request $request)
    {
        $ref = $request->query('ref');
        $tk = $request->query('tk');

        $shareLink = SubscriberShareLink::where('token', $ref)->first();

        if ($shareLink) {
            EngagementLog::create([
                'subscriber_share_link_id' => $shareLink->id,
                'user_id'                  => $shareLink->user_id,
                'event_type'               => 'pdf_catalogue_open',
                'metadata'                 => [
                    'tracking_token' => $tk,
                    'source'         => 'pdf_cover',
                ],
            ]);

            $companySlug = $shareLink->user?->profile?->company_slug;
            if ($companySlug) {
                return redirect()->route('store.catalog', ['company_slug' => $companySlug]);
            }
        }

        return redirect()->to('/');
    }
}
