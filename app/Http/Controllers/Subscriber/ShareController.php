<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\SubscriberShareLink;
use App\Models\SubscriberProduct;
use App\Models\SubscriberActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ShareController extends Controller
{
    public function index()
    {
        $links = SubscriberShareLink::where('user_id', auth()->id())
            ->with('product')
            ->latest()
            ->paginate(15);
        return view('subscriber-panel.share.index', compact('links'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $products = SubscriberProduct::where('user_id', $user->id)
            ->where('status', 'active')
            ->select(['id', 'name', 'sku', 'thumbnail', 'mrp', 'offer_price', 'short_description', 'sort_order'])
            ->orderBy('name')
            ->get();
        $selectedProduct = null;
        if ($request->product_id) {
            $selectedProduct = SubscriberProduct::where('user_id', $user->id)
                ->find($request->product_id);
        }
        return view('subscriber-panel.share.create', compact('products', 'selectedProduct'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'             => 'nullable|string|max:255',
            'type'              => 'required|in:pdf,image,catalog,whatsapp',
            'subscriber_product_id' => 'nullable|exists:subscriber_products,id',
            'selected_product_ids' => 'nullable|array|max:250',
            'selected_product_ids.*' => 'integer|exists:subscriber_products,id',
            'expires_at'        => 'nullable|date|after:now',
        ]);

        $user = auth()->user();

        // Validate product belongs to subscriber
        if ($request->subscriber_product_id) {
            $product = SubscriberProduct::where('user_id', $user->id)
                ->findOrFail($request->subscriber_product_id);
        }

        $selectedProductIds = collect($request->input('selected_product_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedProductIds->isNotEmpty()) {
            $validSelectedIds = SubscriberProduct::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereIn('id', $selectedProductIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $selectedProductIds = $validSelectedIds;
        }

        $settings = [
            'show_mrp'         => $request->boolean('show_mrp', true),
            'show_offer_price' => $request->boolean('show_offer_price', true),
            'show_description' => $request->boolean('show_description', true),
            'show_attributes'  => $request->boolean('show_attributes', true),
            'show_images'      => $request->boolean('show_images', true),
            'show_contact'     => $request->boolean('show_contact', true),
            'allow_download'   => $request->boolean('allow_download', true),
            'selected_product_ids' => $selectedProductIds->all(),
            'render_mode'       => 'virtualized',
            'image_profile'     => 'webp-responsive',
            'export_batch_size' => 12,
        ];

        $link = SubscriberShareLink::create([
            'user_id'           => $user->id,
            'subscriber_product_id' => $request->subscriber_product_id,
            'token'             => Str::random(32),
            'title'             => $request->title ?: ($product->name ?? 'Product Catalog'),
            'type'              => $request->type,
            'settings'          => $settings,
            'expires_at'        => $request->expires_at,
            'is_active'         => true,
            'approval_status'   => 'approved',
        ]);

        SubscriberActivityLog::log('created', 'Created share link: ' . $link->title, $link);

        return redirect()->route('subscriber.share.show', $link->id)
            ->with('success', 'Share link created successfully!');
    }

    public function show(SubscriberShareLink $shareLink)
    {
        if ($shareLink->user_id !== auth()->id()) abort(403);
        $shareLink->load('product.images', 'product.attributeValues.attribute');
        return view('subscriber-panel.share.show', compact('shareLink'));
    }

    public function destroy(SubscriberShareLink $shareLink)
    {
        if ($shareLink->user_id !== auth()->id()) abort(403);
        $shareLink->delete();
        return back()->with('success', 'Share link deleted.');
    }

    // ─── PUBLIC SHARE PAGE ────────────────────────────────────────────────────

    public function publicView(string $token)
    {
        $link = SubscriberShareLink::with([
            'subscriber.subscriberProfile',
            'product.images',
            'product.attributeValues.attribute',
            'product.category',
        ])->where('token', $token)
          ->where('is_active', true)
          ->firstOrFail();

        if ($link->is_expired) {
            abort(410, 'This share link has expired.');
        }

        // Double-approval check
        if ($link->approval_status !== 'approved' || 
            ($link->product && $link->product->approval_status !== 'approved') || 
            ($link->subscriber->subscriberProfile && ! in_array($link->subscriber->subscriberProfile->status, ['active', 'approved'], true))) {
            return response()->view('subscriber-panel.share.pending', ['link' => $link], 403);
        }

        $link->incrementView();
        $settings = $link->settings ?? [];
        $subscriber = $link->subscriber;
        $profile = $subscriber->subscriberProfile;
        $product = $link->product;

        // For catalog shares (no specific product), load subscriber's active products
        $catalogProducts = null;
        if (!$product) {
            $catalogProducts = $this->catalogProductsForLink($link);
        }

        return view('subscriber-panel.share.public', compact(
            'link', 'subscriber', 'profile', 'product', 'catalogProducts', 'settings'
        ));
    }

    // ─── PDF GENERATION ───────────────────────────────────────────────────────

    public function generatePdf(string $token)
    {
        $link = SubscriberShareLink::with([
            'subscriber.subscriberProfile',
            'product.images',
            'product.attributeValues.attribute',
        ])->where('token', $token)
          ->where('is_active', true)
          ->firstOrFail();

        if ($link->is_expired) {
            abort(410, 'This share link has expired.');
        }

        // Double-approval check
        if ($link->approval_status !== 'approved' || 
            ($link->product && $link->product->approval_status !== 'approved') || 
            ($link->subscriber->subscriberProfile && ! in_array($link->subscriber->subscriberProfile->status, ['active', 'approved'], true))) {
            abort(403, 'This catalog page is currently under review by our administration.');
        }

        $subscriber = $link->subscriber;
        $profile = $subscriber->subscriberProfile;
        $settings = $link->settings ?? [];

        $product = $link->product;
        $catalogProducts = null;
        if (!$product) {
            $catalogProducts = $this->catalogProductsForLink($link, true);
        }

        $template = $subscriber->subscriberPdfTemplate ?? null;

        $pdf = Pdf::loadView('subscriber-panel.pdf.catalog', compact(
            'link', 'subscriber', 'profile', 'product', 'catalogProducts', 'settings', 'template'
        ))
            ->setPaper('A4', $template?->orientation ?? 'portrait')
            ->setOptions([
                'dpi' => 96,
                'defaultFont' => 'Helvetica',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
            ]);

        $link->increment('download_count');

        $filename = Str::slug($link->title ?? 'catalog') . '-' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    // ─── IMAGE GALLERY SHARE ─────────────────────────────────────────────────

    public function imageGallery(string $token)
    {
        $link = SubscriberShareLink::with([
            'subscriber.subscriberProfile',
            'product.images',
        ])->where('token', $token)
          ->where('is_active', true)
          ->firstOrFail();

        if ($link->is_expired) {
            abort(410, 'This share link has expired.');
        }

        // Double-approval check
        if ($link->approval_status !== 'approved' || 
            ($link->product && $link->product->approval_status !== 'approved') || 
            ($link->subscriber->subscriberProfile && ! in_array($link->subscriber->subscriberProfile->status, ['active', 'approved'], true))) {
            return response()->view('subscriber-panel.share.pending', ['link' => $link], 403);
        }

        $catalogProducts = $link->product ? null : $this->catalogProductsForLink($link);

        $link->incrementView();
        return view('subscriber-panel.share.image-gallery', compact('link', 'catalogProducts'));
    }

    private function catalogProductsForLink(SubscriberShareLink $link, bool $forPdf = false)
    {
        $settings = $link->settings ?? [];
        $selectedIds = collect($settings['selected_product_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $cacheKey = 'share_catalog_products:' . $link->id . ':' . md5($selectedIds->implode(',') . ':' . (int) $forPdf . ':' . optional($link->updated_at)->timestamp);

        return Cache::remember($cacheKey, now()->addMinutes(12), function () use ($link, $selectedIds, $forPdf) {
            $query = SubscriberProduct::where('user_id', $link->user_id)
                ->where('status', 'active')
                ->with([
                    'category:id,name',
                    'subcategory:id,name',
                    'images:id,subscriber_product_id,image_path,alt_text,is_primary,sort_order',
                    'attributeValues.attribute:id,name,type,unit,show_in_pdf,show_in_share,sort_order',
                ])
                ->select([
                    'id', 'user_id', 'category_id', 'subcategory_id', 'name', 'slug', 'sku',
                    'mrp', 'offer_price', 'currency', 'thumbnail', 'short_description',
                    'full_description', 'tags', 'featured', 'status', 'approval_status',
                    'sort_order', 'created_at', 'updated_at',
                ]);

            if ($selectedIds->isNotEmpty()) {
                $query->whereIn('id', $selectedIds);
            }

            $products = $query->orderBy('sort_order')->orderBy('name')->limit($forPdf ? 180 : 300)->get();

            if ($selectedIds->isEmpty()) {
                return $products;
            }

            return $products->sortBy(fn ($product) => $selectedIds->search((int) $product->id))->values();
        });
    }
}
