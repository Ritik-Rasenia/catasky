<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DoubleTickService
{
    protected $apiKey;
    protected $senderNumber;
    protected $baseApiUrl;

    public function __construct()
    {
        $this->apiKey       = config('services.doubletick.api_key');
        $this->senderNumber = config('services.doubletick.sender_number');
        $this->baseApiUrl   = config('services.doubletick.base_url', 'https://public.doubletick.io');
    }

    /**
     * Send catalog link message via WhatsApp using DoubleTick.io APIs.
     *
     * @param string $phone
     * @param string $messageText
     * @return array
     */
    public function sendWhatsAppMessage(string $phone, string $messageText): array
    {
        // Standardize phone format (Ensure no spaces, add +91 country prefix if missing)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 10) {
            $phone = '91' . $phone; // Default to India country code if length is 10
        }
        
        if (substr($phone, 0, 1) !== '+') {
            $formattedPhone = '+' . $phone;
        } else {
            $formattedPhone = $phone;
        }

        Log::info("DoubleTick Dispatch: Sending WhatsApp to {$formattedPhone}");

        // Simulation mode fallback if no API key is specified in .env
        if (empty($this->apiKey) || $this->apiKey === 'demo_key') {
            $mockMessageId = 'msg_mock_' . rand(100000, 999999);
            Log::warning("DoubleTick credentials not set in .env. Operating in Simulation Mode.", [
                'phone' => $formattedPhone,
                'message_id' => $mockMessageId
            ]);
            
            // Automatically fire a simulated webhook callback to mimic delivery after 3 seconds in a real app,
            // or return the successful payload immediately so users can test immediately.
            return [
                'success' => true,
                'message_id' => $mockMessageId,
                'status' => 'sent',
                'simulated' => true
            ];
        }

        try {
            // Outbound payload following official DoubleTick specs
            $textApiUrl = $this->baseApiUrl . '/whatsapp/message/text';
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($textApiUrl, [
                'messages' => [
                    [
                        'to' => $formattedPhone,
                        'type' => 'text',
                        'text' => [
                            'body' => $messageText
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Extract message id returned by DoubleTick API
                $messageId = $data['messages'][0]['id'] ?? ('msg_' . rand(100000, 999999));
                
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'status' => 'sent',
                    'simulated' => false
                ];
            } else {
                Log::error("DoubleTick API failure response", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'API responded with error code: ' . $response->status()
                ];
            }

        } catch (\Exception $e) {
            Log::error("DoubleTick Exception during API call", [
                'message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send PDF document via WhatsApp using DoubleTick.io API.
     *
     * @param string $phone
     * @param string $pdfUrl
     * @param string $caption
     * @param string $filename
     * @return array
     */
    public function sendWhatsAppDocument(string $phone, string $pdfUrl, string $caption = '', string $filename = ''): array
    {
        // Standardize phone format (Ensure no spaces, add +91 country prefix if missing)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 10) {
            $phone = '91' . $phone; // Default to India country code if length is 10
        }
        
        if (substr($phone, 0, 1) !== '+') {
            $formattedPhone = '+' . $phone;
        } else {
            $formattedPhone = $phone;
        }

        Log::info("DoubleTick Dispatch: Sending WhatsApp Document to {$formattedPhone} | PDF: {$pdfUrl}");

        // Simulation mode fallback if no API key is specified in .env
        if (empty($this->apiKey) || $this->apiKey === 'demo_key') {
            $mockMessageId = 'msg_mock_' . rand(100000, 999999);
            Log::warning("DoubleTick credentials not set in .env. Operating in Simulation Mode for PDF Document.", [
                'phone' => $formattedPhone,
                'pdf_url' => $pdfUrl,
                'message_id' => $mockMessageId
            ]);
            
            return [
                'success' => true,
                'message_id' => $mockMessageId,
                'status' => 'sent',
                'simulated' => true
            ];
        }

        try {
            $documentApiUrl = $this->baseApiUrl . '/whatsapp/message/document';
            
            // Strip leading '+' for DoubleTick standard international number format
            $toPhone = preg_replace('/[^0-9]/', '', $formattedPhone);

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($documentApiUrl, [
                'to' => $toPhone,
                'from' => $this->senderNumber ?: '',
                'content' => [
                    'mediaUrl' => $pdfUrl,
                    'caption' => $caption,
                    'filename' => $filename ?: 'catalogue.pdf'
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? ('msg_' . rand(100000, 999999));
                
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'status' => 'sent',
                    'simulated' => false
                ];
            } else {
                Log::error("DoubleTick Document API failure response", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'API responded with error code: ' . $response->status()
                ];
            }

        } catch (\Exception $e) {
            Log::error("DoubleTick Document Exception during API call", [
                'message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send Image via WhatsApp using DoubleTick.io API.
     *
     * @param string $phone
     * @param string $imageUrl
     * @param string $caption
     * @return array
     */
    public function sendWhatsAppImage(string $phone, string $imageUrl, string $caption = ''): array
    {
        // Standardize phone format (Ensure no spaces, add +91 country prefix if missing)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 10) {
            $phone = '91' . $phone; // Default to India country code if length is 10
        }
        
        if (substr($phone, 0, 1) !== '+') {
            $formattedPhone = '+' . $phone;
        } else {
            $formattedPhone = $phone;
        }

        Log::info("DoubleTick Dispatch: Sending WhatsApp Image to {$formattedPhone} | Image: {$imageUrl}");

        // Simulation mode fallback if no API key is specified in .env
        if (empty($this->apiKey) || $this->apiKey === 'demo_key') {
            $mockMessageId = 'msg_mock_' . rand(100000, 999999);
            Log::warning("DoubleTick credentials not set in .env. Operating in Simulation Mode for Image.", [
                'phone' => $formattedPhone,
                'image_url' => $imageUrl,
                'message_id' => $mockMessageId
            ]);
            
            return [
                'success' => true,
                'message_id' => $mockMessageId,
                'status' => 'sent',
                'simulated' => true
            ];
        }

        try {
            $imageApiUrl = $this->baseApiUrl . '/whatsapp/message/image';
            
            // Strip leading '+' for DoubleTick standard international number format
            $toPhone = preg_replace('/[^0-9]/', '', $formattedPhone);

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($imageApiUrl, [
                'to' => $toPhone,
                'from' => $this->senderNumber ?: '',
                'content' => [
                    'mediaUrl' => $imageUrl,
                    'caption' => $caption
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? ('msg_' . rand(100000, 999999));
                
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'status' => 'sent',
                    'simulated' => false
                ];
            } else {
                Log::error("DoubleTick Image API failure response", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'API responded with error code: ' . $response->status()
                ];
            }

        } catch (\Exception $e) {
            Log::error("DoubleTick Image Exception during API call", [
                'message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

