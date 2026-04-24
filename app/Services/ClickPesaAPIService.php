<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickPesaAPIService
{
    protected array $config;
    protected ?string $token = null;
    protected ?int $tokenExpiry = null;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: [
            'api_base_url' => env('CLICKPESA_API_BASE_URL', 'https://api.clickpesa.com/third-parties'),
            'api_key' => env('CLICKPESA_API_KEY'),
            'client_id' => env('CLICKPESA_CLIENT_ID'),
            'currency' => env('CLICKPESA_DEFAULT_CURRENCY', 'TZS'),
            'test_mode' => env('CLICKPESA_TEST_MODE', false), // Default to production mode
        ];
    }

    /**
     * Generate JWT Authorization Token
     */
    public function generateToken(): string
    {
        // Test mode - return mock token
        if ($this->config['test_mode']) {
            $this->token = 'test_token_' . time();
            $this->tokenExpiry = time() + 3600;
            return 'Bearer ' . $this->token;
        }

        $url = $this->config['api_base_url'] . '/generate-token';

        $response = Http::withHeaders([
            'api-key' => $this->config['api_key'],
            'client-id' => $this->config['client_id']
        ])->timeout(30)->post($url);

        if ($response->successful() && $response->json('success')) {
            $rawToken = $response->json('token');
            if (strpos($rawToken, 'Bearer ') === 0) {
                $this->token = substr($rawToken, 7);
            } else {
                $this->token = $rawToken;
            }
            $this->tokenExpiry = time() + 3600;
            return $rawToken;
        }

        throw new Exception('Failed to generate token: ' . ($response->json('message') ?? 'Unknown error'));
    }

    /**
     * Get valid token (generate new one if expired)
     */
    protected function getValidToken(): string
    {
        if (!$this->token || $this->tokenExpiry <= time()) {
            $this->generateToken();
        }
        return $this->token;
    }

    /**
     * Preview USSD-PUSH request
     */
    public function previewUSSDPush(float $amount, string $orderReference, string $phoneNumber, bool $fetchSenderDetails = false, ?string $checksum = null): array
    {
        // Test mode - return mock response
        if ($this->config['test_mode']) {
            $response = [
                'activeMethods' => [
                    [
                        'name' => 'TIGO PESA',
                        'status' => 'AVAILABLE',
                        'fee' => 500,
                        'message' => 'Payment method available'
                    ],
                    [
                        'name' => 'M-PESA',
                        'status' => 'AVAILABLE', 
                        'fee' => 500,
                        'message' => 'Payment method available'
                    ]
                ]
            ];

            if ($fetchSenderDetails) {
                $response['sender'] = [
                    'accountName' => 'Test User',
                    'accountNumber' => $phoneNumber,
                    'accountProvider' => 'TIGO-PESA'
                ];
            }

            return $response;
        }

        // Try different endpoints
        $endpoints = [
            '/payments/preview-ussd-push-request',
            '/v2/payments/preview-ussd-push-request',
            '/third-parties/payments/preview-ussd-push-request'
        ];

        $data = [
            'amount' => $amount,
            'currency' => $this->config['currency'],
            'orderReference' => $orderReference,
            'phoneNumber' => $phoneNumber,
            'fetchSenderDetails' => $fetchSenderDetails
        ];

        if ($checksum) {
            $data['checksum'] = $checksum;
        }

        foreach ($endpoints as $endpoint) {
            try {
                $url = $this->config['api_base_url'] . $endpoint;
                return $this->makeRequest('POST', $url, $data);
            } catch (\Exception $e) {
                // Try next endpoint
                continue;
            }
        }

        throw new Exception('All endpoints failed for USSD preview');
    }

    /**
     * Initiate USSD-PUSH request
     */
    public function initiateUSSDPush(float $amount, string $orderReference, string $phoneNumber, ?string $checksum = null, array $customerDetails = []): array
    {
        // Test mode - return mock response
        if ($this->config['test_mode']) {
            return [
                'id' => 'TEST_TXN_' . time(),
                'status' => 'PROCESSING',
                'channel' => 'TIGO-PESA',
                'orderReference' => $orderReference,
                'collectedAmount' => (string) $amount,
                'collectedCurrency' => $this->config['currency'],
                'createdAt' => now()->toISOString(),
                'clientId' => 'TEST_CLIENT'
            ];
        }

        $url = $this->config['api_base_url'] . '/payments/initiate-ussd-push-request';

        $data = [
            'amount' => $amount,
            'currency' => $this->config['currency'],
            'orderReference' => $orderReference,
            'phoneNumber' => $phoneNumber
        ];

        // Add customer details if provided
        if (!empty($customerDetails)) {
            if (isset($customerDetails['customerName'])) {
                $data['customerName'] = $customerDetails['customerName'];
            }
            if (isset($customerDetails['description'])) {
                $data['description'] = $customerDetails['description'];
            }
            if (isset($customerDetails['email'])) {
                $data['customerEmail'] = $customerDetails['email'];
            }
        }

        if ($checksum) {
            $data['checksum'] = $checksum;
        }

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Query Payment Status by Order Reference
     */
    public function queryPaymentStatus(string $orderReference): array
    {
        // Test mode - return mock response
        if ($this->config['test_mode']) {
            return [
                'id' => 'TEST_TXN_' . time(),
                'status' => 'SUCCESS',
                'paymentReference' => $orderReference,
                'paymentPhoneNumber' => '25522239304',
                'orderReference' => $orderReference,
                'collectedAmount' => 2000,
                'collectedCurrency' => 'TZS',
                'message' => 'Payment completed successfully',
                'updatedAt' => now()->toISOString(),
                'createdAt' => now()->toISOString(),
                'customer' => [
                    'customerName' => 'Test User',
                    'customerPhoneNumber' => '25522239304',
                    'customerEmail' => 'test@example.com'
                ],
                'clientId' => 'TEST_CLIENT'
            ];
        }

        $url = 'https://api.clickpesa.com/third-parties/payments/' . urlencode($orderReference);
        return $this->makeRequest('GET', $url);
    }

    /**
     * Query All Payments with filtering and pagination
     */
    public function queryAllPayments(array $params = []): array
    {
        // Test mode - return mock response
        if ($this->config['test_mode']) {
            return [
                'data' => [
                    [
                        'id' => 'TEST_TXN_' . time(),
                        'status' => 'SUCCESS',
                        'exchanged' => false,
                        'paymentReference' => 'FEEDTAN_TEST_' . time(),
                        'paymentPhoneNumber' => '255712345678',
                        'orderReference' => 'FEEDTAN_TEST_' . time(),
                        'collectedAmount' => 2000,
                        'collectedCurrency' => 'TZS',
                        'message' => 'Test payment from sync',
                        'updatedAt' => now()->toISOString(),
                        'createdAt' => now()->toISOString(),
                        'customer' => [
                            'customerName' => 'Test User',
                            'customerPhoneNumber' => '255712345678',
                            'customerEmail' => 'test@example.com'
                        ],
                        'channel' => 'TIGO-PESA',
                        'clientId' => 'TEST_CLIENT'
                    ],
                    [
                        'id' => 'TEST_TXN_' . (time() - 100),
                        'status' => 'PROCESSING',
                        'exchanged' => false,
                        'paymentReference' => 'FEEDTAN_TEST_' . (time() - 100),
                        'paymentPhoneNumber' => '25562239304',
                        'orderReference' => 'FEEDTAN_TEST_' . (time() - 100),
                        'collectedAmount' => 1500,
                        'collectedCurrency' => 'TZS',
                        'message' => 'Test payment from sync',
                        'updatedAt' => now()->toISOString(),
                        'createdAt' => now()->toISOString(),
                        'customer' => [
                            'customerName' => 'Another User',
                            'customerPhoneNumber' => '25562239304',
                            'customerEmail' => 'another@example.com'
                        ],
                        'channel' => 'M-PESA',
                        'clientId' => 'TEST_CLIENT'
                    ]
                ],
                'totalCount' => 2
            ];
        }

        // Use the correct ClickPesa third-parties endpoint
        $url = $this->config['api_base_url'] . '/payments/all';
        
        // Set default parameters
        $defaultParams = [
            'orderBy' => 'DESC',
            'limit' => 20
        ];
        
        $params = array_merge($defaultParams, $params);
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $response = $this->makeRequest('GET', $url);
        
        // Return the response
        return $response;
    }

    /**
     * Get Account Balance
     */
    public function getAccountBalance(): array
    {
        // Test mode - return mock response
        if ($this->config['test_mode']) {
            return [
                [
                    'currency' => 'TZS',
                    'balance' => 50000
                ]
            ];
        }

        $url = $this->config['api_base_url'] . '/account/balance';
        return $this->makeRequest('GET', $url);
    }

    /**
     * Get Account Statement
     */
    public function getAccountStatement(array $params = []): array
    {
        // Test mode - return mock response
        if ($this->config['test_mode']) {
            return [
                'accountDetails' => [
                    'currency' => 'TZS',
                    'openingBalance' => 100000,
                    'closingBalance' => 50000,
                    'totalCredits' => 25000,
                    'totalDebits' => 75000
                ],
                'transactions' => [
                    [
                        'balance' => 50000,
                        'date' => now()->format('Y-m-d'),
                        'description' => 'Test payment',
                        'entry' => 'Debit',
                        'amount' => 2000,
                        'currency' => 'TZS',
                        'orderReference' => 'FEEDTAN_TEST_' . time(),
                        'id' => 'TEST_TXN_' . time(),
                        'type' => 'Payment'
                    ]
                ]
            ];
        }

        $url = $this->config['api_base_url'] . '/account/statement';
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->makeRequest('GET', $url);
    }

    /**
     * Generate unique order reference (max 20 characters)
     */
    public function generateOrderReference(string $prefix = 'FEEDTAN'): string
    {
        $uniqueId = strtoupper(uniqid());
        $timestamp = time();
        $reference = $prefix . substr($uniqueId, -8) . substr($timestamp, -6);
        
        if (strlen($reference) > 20) {
            $reference = substr($reference, 0, 20);
        }
        
        return $reference;
    }

    /**
     * Format amount for API
     */
    public function formatAmount(float $amount): float
    {
        return (float) number_format($amount, 0, '.', '');
    }

    /**
     * Validate phone number for Tanzania
     */
    public function validatePhoneNumber(string $phoneNumber): ?string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Handle different formats
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
        
        // Handle 0 prefix format (like 0622239304)
        if (strlen($cleaned) === 10 && substr($cleaned, 0, 1) === '0') {
            return '255' . substr($cleaned, 1);
        }
        
        return null;
    }

    /**
     * Make HTTP request with error handling
     */
    private function makeRequest(string $method, string $url, ?array $data = null, array $headers = []): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders($headers))
                ->timeout(30)
                ->$method($url, $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                $errorMessage = $response->json('message') ?? $response->body() ?? 'Unknown API error';
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            Log::error('API request failed', [
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get headers for HTTP request
     */
    private function getHeaders(array $headers = []): array
    {
        $token = $this->getValidToken();

        $defaultHeaders = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ];

        return array_merge($defaultHeaders, $headers);
    }
}
