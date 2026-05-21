<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CatalogueShare;
use App\Models\ShareTrackingLog;
use App\Services\DoubleTickService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DoubleTickController extends Controller
{
    protected $doubleTick;

    public function __construct(DoubleTickService $doubleTick)
    {
        $this->doubleTick = $doubleTick;
    }

    /**
     * Helper to upload a local file to a free public sharing host (tmpfiles.org)
     * so that external APIs (like DoubleTick or WhatsApp) can download it during local testing.
     */
    protected function uploadToPublicHost($path)
    {
        try {
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) {
                Log::error("uploadToPublicHost: File does not exist at {$fullPath}");
                return null;
            }
            
            Log::info("uploadToPublicHost: Uploading {$fullPath} to tmpfiles.org...");
            
            $response = \Illuminate\Support\Facades\Http::attach(
                'file', file_get_contents($fullPath), basename($fullPath)
            )->post('https://tmpfiles.org/api/v1/upload');
            
            if ($response->successful()) {
                $data = $response->json();
                $tempUrl = $data['data']['url'] ?? null;
                if ($tempUrl) {
                    // Convert standard page URL to direct download link:
                    // https://tmpfiles.org/12345/filename.pdf -> https://tmpfiles.org/dl/12345/filename.pdf
                    $directUrl = str_replace('https://tmpfiles.org/', 'https://tmpfiles.org/dl/', $tempUrl);
                    Log::info("uploadToPublicHost: Upload succeeded! Public URL: {$directUrl}");
                    return $directUrl;
                }
            } else {
                Log::error("uploadToPublicHost: Service returned failure: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("uploadToPublicHost: Exception during upload: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Upload a client-side generated PDF temporarily to the public disk and return its public URL.
     */
    /**
     * Upload a client-side generated PDF temporarily to the public disk and return its public URL.
     */
    public function uploadTempPdf(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // max 10MB
            'filename' => 'nullable|string'
        ]);

        if ($request->hasFile('pdf')) {
            $file = $request->file('pdf');
            $directory = 'catalogues';

            // Clean up old files (older than 24 hours) to save space
            try {
                $files = \Illuminate\Support\Facades\Storage::disk('public')->files($directory);
                foreach ($files as $f) {
                    $time = \Illuminate\Support\Facades\Storage::disk('public')->lastModified($f);
                    if (time() - $time > 86400) { // 24 hours
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($f);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to clean up old PDFs: " . $e->getMessage());
            }

            // Save PDF with unique name
            $filename = $request->input('filename') ?: 'catalogue_' . time() . '.pdf';
            $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
            if (!Str::endsWith(strtolower($filename), '.pdf')) {
                $filename .= '.pdf';
            }
            $filename = time() . '_' . $filename;

            $path = $file->storeAs($directory, $filename, 'public');

            // Double check that file was successfully written
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                Log::error("uploadTempPdf: File was not successfully written to {$path}");
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save PDF on storage disk.'
                ], 500);
            }

            // Generate clean production-ready URL through Laravel custom route with inline headers
            $productionUrl = route('pdf.download', ['filename' => $filename]);

            // Try uploading to public host for local testing so WhatsApp APIs can access it
            $tempPublicUrl = null;
            $localDiskUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            if (Str::contains($localDiskUrl, ['localhost', '127.0.0.1'])) {
                $tempPublicUrl = $this->uploadToPublicHost($path);
            }

            // If tempPublicUrl was generated, we use it for live sending/sharing, otherwise use productionUrl
            $publicUrl = $tempPublicUrl ?: $productionUrl;

            return response()->json([
                'success' => true,
                'url' => $productionUrl, // Give front-end the clean production-ready URL
                'public_url' => $publicUrl, // Give the actual accessible public file URL for local/external APIs
                'path' => $path
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file received.'
        ], 400);
    }

    /**
     * View/Download PDF inline in browser with proper headers.
     */
    public function downloadPdf($filename)
    {
        $path = 'catalogues/' . $filename;
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            abort(404, 'Catalogue PDF not found.');
        }

        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    /**
     * Upload a client-side generated PNG temporarily to the public disk and return its public URL.
     */
    public function uploadTempImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|image|mimes:png,jpg,jpeg|max:10240', // max 10MB
            'filename' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $directory = 'catalogues_images';

            // Clean up old files (older than 24 hours) to save space
            try {
                $files = \Illuminate\Support\Facades\Storage::disk('public')->files($directory);
                foreach ($files as $f) {
                    $time = \Illuminate\Support\Facades\Storage::disk('public')->lastModified($f);
                    if (time() - $time > 86400) { // 24 hours
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($f);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to clean up old images: " . $e->getMessage());
            }

            // Save Image with unique name
            $filename = $request->input('filename') ?: 'card_' . time() . '.png';
            $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
            if (!Str::endsWith(strtolower($filename), ['.png', '.jpg', '.jpeg'])) {
                $filename .= '.png';
            }
            $filename = time() . '_' . $filename;

            $path = $file->storeAs($directory, $filename, 'public');

            // Double check that file was successfully written
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                Log::error("uploadTempImage: File was not successfully written to {$path}");
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save image on storage disk.'
                ], 500);
            }

            // Generate clean production-ready URL through Laravel custom route with inline headers
            $productionUrl = route('image.download', ['filename' => $filename]);

            // Try uploading to public host for local testing so WhatsApp APIs can access it
            $tempPublicUrl = null;
            $localDiskUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            if (Str::contains($localDiskUrl, ['localhost', '127.0.0.1'])) {
                $tempPublicUrl = $this->uploadToPublicHost($path);
            }

            // If tempPublicUrl was generated, we use it for live sending/sharing, otherwise use productionUrl
            $publicUrl = $tempPublicUrl ?: $productionUrl;

            return response()->json([
                'success' => true,
                'url' => $productionUrl, // Give front-end the clean production-ready URL
                'public_url' => $publicUrl, // Give the actual accessible public file URL for local/external APIs
                'path' => $path
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file received.'
        ], 400);
    }

    /**
     * View/Download Image inline in browser with proper headers.
     */
    public function downloadImage($filename)
    {
        $path = 'catalogues_images/' . $filename;
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            abort(404, 'Catalogue image not found.');
        }

        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
        
        $mime = 'image/png';
        if (Str::endsWith(strtolower($filename), '.jpg') || Str::endsWith(strtolower($filename), '.jpeg')) {
            $mime = 'image/jpeg';
        }

        return response()->file($filePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    /**
     * Outbound WhatsApp sharing endpoint.
     */
    public function share(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'product_ids' => 'required|array',
            'catalog_title' => 'nullable|string',
            'pdf_url' => 'nullable|string',
            'image_urls' => 'nullable|array',
            'send_type' => 'nullable|string|in:text,pdf,images',
        ]);

        $productIds = $request->input('product_ids');
        $phone = $request->input('phone');
        $catalogTitle = $request->input('catalog_title') ?: 'Catasky Smart Catalogue';
        $pdfUrl = $request->input('pdf_url');
        $sendType = $request->input('send_type') ?: 'text';

        // Generate unique catalogue code
        $code = strtoupper(Str::random(7));

        // Create the Share record
        $share = CatalogueShare::create([
            'user_id' => Auth::id(),
            'catalogue_code' => $code,
            'product_ids' => implode(',', $productIds),
            'pdf_url' => $pdfUrl,
            'customer_phone' => $phone,
            'delivery_status' => 'pending',
            'seen_status' => 'unread',
            'clicked_status' => 'no',
            'opened_status' => 'no',
            'total_view_time' => 0,
            'visit_count' => 0
        ]);

        // Construct unique catalogue B2B link
        $catalogLink = route('doubletick.view', $code);

        // Compile a premium personalized WhatsApp corporate message
        $companyName = \App\Models\Setting::first()->site_title ?? 'Catasky';
        
        if ($sendType === 'pdf' && !empty($pdfUrl)) {
            // Document message flow
            $caption = "📄 *{$catalogTitle} CATALOGUE FROM {$companyName}*\n\n";
            $caption .= "Hello! We have attached the custom specifications catalogue tailored for your review.\n\n";
            $caption .= "🔗 *You can also view proposal online:* {$catalogLink}\n\n";
            $caption .= "Thank you for choosing {$companyName}!";

            $filename = strtolower(str_replace(' ', '_', $catalogTitle)) . '.pdf';

            // Send via DoubleTick Document Service
            $result = $this->doubleTick->sendWhatsAppDocument($phone, $pdfUrl, $caption, $filename);
        } elseif ($sendType === 'images' && !empty($request->input('image_urls'))) {
            // Images flow
            $imageUrls = $request->input('image_urls');
            $caption = "📄 *{$catalogTitle} CATALOGUE FROM {$companyName}*\n\n";
            $caption .= "Hello! We have shared the custom product images tailored for your review.\n\n";
            $caption .= "🔗 *You can also view proposal online:* {$catalogLink}\n\n";
            $caption .= "Thank you for choosing {$companyName}!";

            $successCount = 0;
            $lastResult = null;

            foreach ($imageUrls as $index => $imageUrl) {
                // Send caption ONLY with the first image
                $imageCaption = ($index === 0) ? $caption : '';
                $result = $this->doubleTick->sendWhatsAppImage($phone, $imageUrl, $imageCaption);
                
                if ($result['success']) {
                    $successCount++;
                    $lastResult = $result;
                } else {
                    Log::error("Failed to send image {$imageUrl} to {$phone}: " . ($result['error'] ?? 'Unknown Error'));
                }
            }

            if ($successCount > 0) {
                $result = [
                    'success' => true,
                    'message_id' => $lastResult['message_id'] ?? ('img_' . rand(100000, 999999)),
                    'simulated' => $lastResult['simulated'] ?? false
                ];
            } else {
                $result = [
                    'success' => false,
                    'error' => 'Failed to send any images.'
                ];
            }
        } else {
            // Text message flow
            $msg = "📄 *{$catalogTitle} CATALOGUE FROM {$companyName}*\n\n";
            $msg .= "Hello! We have compiled a custom specifications catalogue tailored for your review.\n\n";
            $msg .= "🔗 *Press link below to view proposal online:*\n";
            $msg .= "{$catalogLink}\n\n";
            $msg .= "This link is secured for your device and allows active proposal specification tracking. Feel free to contact us for pricing queries.";

            // Send via DoubleTick Service
            $result = $this->doubleTick->sendWhatsAppMessage($phone, $msg);
        }

        if ($result['success']) {
            $share->message_id = $result['message_id'];
            $share->delivery_status = 'sent';
            $share->save();

            // Log initial sent event
            ShareTrackingLog::create([
                'share_id' => $share->id,
                'event_type' => 'sent',
                'metadata' => ['gateway_response' => $result]
            ]);

            // If simulated, automatically simulate delivery/seen logs to make testing a joy
            if (isset($result['simulated']) && $result['simulated']) {
                $this->simulateDeliveryAndRead($share);
            }

            return response()->json([
                'success' => true,
                'message' => $sendType === 'pdf' ? 'PDF Catalogue shared directly via WhatsApp successful!' : ($sendType === 'images' ? 'Catalogue images shared via WhatsApp successfully!' : 'Catalogue link shared via WhatsApp successfully!'),
                'code' => $code,
                'message_id' => $result['message_id'],
                'url' => $catalogLink
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch via WhatsApp: ' . ($result['error'] ?? 'Unknown Error')
            ], 400);
        }
    }

    /**
     * Client opens the unique secured link.
     */
    public function viewCatalogue($code)
    {
        $share = CatalogueShare::where('catalogue_code', $code)->firstOrFail();

        // Increment stats
        $share->visit_count += 1;
        $share->clicked_status = 'yes';
        $share->opened_status = 'yes';
        $share->save();

        // Log open event
        ShareTrackingLog::create([
            'share_id' => $share->id,
            'event_type' => 'click',
            'metadata' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'visit_number' => $share->visit_count
            ]
        ]);

        // Resolve product models
        $ids = explode(',', $share->product_ids);
        $products = Product::whereIn('id', $ids)->where('status', 1)->get();

        return view('c_catalogue', compact('share', 'products'));
    }

    /**
     * Heartbeat tracking active view time.
     */
    public function heartbeat(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $share = CatalogueShare::where('catalogue_code', $request->input('code'))->first();

        if ($share) {
            $share->total_view_time += 5; // Increment by 5 seconds
            $share->save();

            // Log heartbeat log
            ShareTrackingLog::create([
                'share_id' => $share->id,
                'event_type' => 'heartbeat',
                'metadata' => ['active_seconds' => 5]
            ]);

            return response()->json(['success' => true, 'total_time' => $share->total_view_time]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Webhook updates from DoubleTick.io API.
     */
    public function webhook(Request $request)
    {
        Log::info("DoubleTick Webhook received:", $request->all());

        // Event payload standard format
        $event = $request->input('event');
        $messageId = $request->input('message.id');

        if ($messageId) {
            $share = CatalogueShare::where('message_id', $messageId)->first();
            if ($share) {
                if ($event === 'message.delivered') {
                    $share->delivery_status = 'delivered';
                    $share->save();
                    
                    ShareTrackingLog::create([
                        'share_id' => $share->id,
                        'event_type' => 'delivery',
                        'metadata' => $request->all()
                    ]);
                } elseif ($event === 'message.read') {
                    $share->delivery_status = 'delivered';
                    $share->seen_status = 'read';
                    $share->save();

                    ShareTrackingLog::create([
                        'share_id' => $share->id,
                        'event_type' => 'seen',
                        'metadata' => $request->all()
                    ]);
                }
                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Record not found'], 404);
    }

    /**
     * Admin tracking panel dashboard.
     */
    public function analyticsDashboard()
    {
        $shares = CatalogueShare::with('trackingLogs')->latest()->paginate(15);
        
        // Summary stats
        $totalShares = CatalogueShare::count();
        $deliveredCount = CatalogueShare::where('delivery_status', 'delivered')->count();
        $seenCount = CatalogueShare::where('seen_status', 'read')->count();
        $clickedCount = CatalogueShare::where('clicked_status', 'yes')->count();
        $openedCount = CatalogueShare::where('opened_status', 'yes')->count();
        
        $deliveryRate = $totalShares > 0 ? round(($deliveredCount / $totalShares) * 100) : 0;
        $seenRate = $deliveredCount > 0 ? round(($seenCount / $deliveredCount) * 100) : 0;
        $clickRate = $totalShares > 0 ? round(($clickedCount / $totalShares) * 100) : 0;

        return view('admin.tracking_analytics', compact(
            'shares', 'totalShares', 'deliveredCount', 'seenCount', 
            'clickedCount', 'openedCount', 'deliveryRate', 'seenRate', 'clickRate'
        ));
    }

    /**
     * Helper to simulate delivery and seen events for a seamless out-of-the-box demo.
     */
    protected function simulateDeliveryAndRead(CatalogueShare $share)
    {
        // Simulate message delivered in 2 seconds
        dispatch(function() use ($share) {
            $share->refresh();
            $share->delivery_status = 'delivered';
            $share->save();

            ShareTrackingLog::create([
                'share_id' => $share->id,
                'event_type' => 'delivery',
                'metadata' => ['simulated' => true]
            ]);
        })->afterResponse();

        // Simulate message read in 4 seconds
        dispatch(function() use ($share) {
            $share->refresh();
            $share->seen_status = 'read';
            $share->save();

            ShareTrackingLog::create([
                'share_id' => $share->id,
                'event_type' => 'seen',
                'metadata' => ['simulated' => true]
            ]);
        })->afterResponse();
    }
}
