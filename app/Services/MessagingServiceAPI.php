<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MessagingServiceAPI
{
    private string $baseUrl;
    private string $token;
    private string $senderId;

    public function __construct()
    {
        $this->baseUrl = 'https://messaging-service.co.tz';
        $this->token = config('messaging.token', 'f9a89f439206e27169ead766463ca92c');
        $this->senderId = config('messaging.sender_id', 'FEEDTAN');
    }

    /**
     * Send SMS via Messaging Service API V2
     */
    public function sendSMS(string $to, string $message, array $options = []): array
    {
        try {
            $url = $this->baseUrl . '/api/sms/v2/text/single';
            
            $data = [
                'from' => $options['from'] ?? $this->senderId,
                'to' => $this->formatPhoneNumber($to),
                'text' => $message
            ];

            // Add optional parameters
            if (isset($options['date']) && isset($options['time'])) {
                $data['date'] = $options['date'];
                $data['time'] = $options['time'];
            }

            Log::info('Sending SMS via Messaging Service', [
                'to' => $data['to'],
                'message' => substr($message, 0, 100) . '...',
                'url' => $url
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->timeout(30)->post($url, $data);

            if ($response->successful()) {
                Log::info('SMS sent successfully', ['response' => $response->json()]);
                return $response->json();
            } else {
                $errorMessage = $this->getErrorMessage($response);
                Log::error('SMS sending failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'error' => $errorMessage
                ]);
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            Log::error('SMS API error: ' . $e->getMessage(), [
                'to' => $to,
                'message' => substr($message, 0, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Ensure it starts with 255 for Tanzania
        if (strlen($cleaned) === 9 && in_array(substr($cleaned, 0, 1), ['6', '7'])) {
            return '255' . $cleaned;
        }
        
        if (strlen($cleaned) === 12 && substr($cleaned, 0, 3) === '255') {
            return $cleaned;
        }
        
        // Handle 06 format (like 0622239304)
        if (strlen($cleaned) === 10 && substr($cleaned, 0, 2) === '06') {
            return '255' . substr($cleaned, 2);
        }
        
        // Handle 07 format
        if (strlen($cleaned) === 10 && substr($cleaned, 0, 2) === '07') {
            return '255' . substr($cleaned, 2);
        }
        
        // Default: assume Tanzania number and add 255
        return '255' . ltrim($cleaned, '0');
    }

    /**
     * Get user-friendly error message from API response
     */
    private function getErrorMessage($response): string
    {
        $status = $response->status();
        $body = $response->body();

        // Try to parse JSON response
        try {
            $json = $response->json();
            if (isset($json['error'])) {
                return "Messaging API Error: " . $json['error'];
            }
        } catch (Exception $e) {
            // JSON parsing failed, use body
        }

        // Handle specific HTTP status codes
        switch ($status) {
            case 400:
                return "Invalid Request: Please check your message format and parameters.";
            case 401:
                return "Authentication Failed: Invalid API token. Please check your messaging service credentials.";
            case 403:
                return "Access Denied: Insufficient permissions to send SMS.";
            case 429:
                return "Rate Limit Exceeded: Too many requests. Please try again later.";
            case 500:
                return "Server Error: Messaging service is temporarily unavailable. Please try again later.";
            default:
                return "SMS Error: Unable to send message (HTTP {$status}). Please try again.";
        }
    }
}
