<?php

namespace App\Http\Controllers;

use App\Services\ClickPesaAPIService;
use Illuminate\Support\Facades\Log;

class ApiDiagnosticController extends Controller
{
    protected $clickPesa;

    public function __construct(ClickPesaAPIService $clickPesa)
    {
        $this->clickPesa = $clickPesa;
    }

    /**
     * Diagnose API configuration and connectivity
     */
    public function diagnose()
    {
        $config = $this->clickPesa->getConfig();
        
        $diagnostic = [
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
            'api_config' => [
                'api_base_url' => $config['api_base_url'] ?? 'NOT_SET',
                'api_key' => $config['api_key'] ? 'SET' : 'NOT_SET',
                'client_id' => $config['client_id'] ? 'SET' : 'NOT_SET',
                'currency' => $config['currency'] ?? 'TZS',
                'test_mode' => $config['test_mode'] ?? false,
            ],
            'connectivity_tests' => [],
            'recommendations' => []
        ];

        // Test API connectivity
        $endpoints = [
            '/payments/preview-ussd-push-request',
            '/v2/payments/preview-ussd-push-request', 
            '/third-parties/payments/preview-ussd-push-request'
        ];

        foreach ($endpoints as $endpoint) {
            $url = $config['api_base_url'] . $endpoint;
            $testResult = $this->testEndpoint($url);
            $diagnostic['connectivity_tests'][] = [
                'endpoint' => $endpoint,
                'url' => $url,
                'status' => $testResult['status'],
                'response_time' => $testResult['response_time'],
                'error' => $testResult['error'] ?? null
            ];
        }

        // Add recommendations based on diagnostic results
        $diagnostic['recommendations'] = $this->generateRecommendations($diagnostic);

        return response()->json($diagnostic);
    }

    private function testEndpoint($url)
    {
        $startTime = microtime(true);
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);

            if ($error) {
                return [
                    'status' => 'FAILED',
                    'response_time' => $responseTime,
                    'error' => $error
                ];
            }

            return [
                'status' => $httpCode >= 200 && $httpCode < 300 ? 'SUCCESS' : 'HTTP_ERROR',
                'response_time' => $responseTime,
                'http_code' => $httpCode,
                'response_preview' => substr($response, 0, 200)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'EXCEPTION',
                'response_time' => round((microtime(true) - $startTime) * 1000, 2),
                'error' => $e->getMessage()
            ];
        }
    }

    private function generateRecommendations($diagnostic)
    {
        $recommendations = [];
        
        // Check API configuration
        if (!$diagnostic['api_config']['api_key']) {
            $recommendations[] = 'CLICKPESA_API_KEY is not set in .env file';
        }
        
        if (!$diagnostic['api_config']['client_id']) {
            $recommendations[] = 'CLICKPESA_CLIENT_ID is not set in .env file';
        }

        // Check connectivity
        $failedEndpoints = array_filter($diagnostic['connectivity_tests'], function($test) {
            return $test['status'] !== 'SUCCESS';
        });

        if (count($failedEndpoints) === count($diagnostic['connectivity_tests'])) {
            $recommendations[] = 'All API endpoints are failing. Check internet connectivity and firewall settings.';
            $recommendations[] = 'Verify the ClickPesa API base URL is correct: ' . $diagnostic['api_config']['api_base_url'];
        }

        // Check test mode
        if ($diagnostic['api_config']['test_mode']) {
            $recommendations[] = 'Test mode is enabled. Set CLICKPESA_TEST_MODE=false in .env for production.';
        }

        // Environment-specific recommendations
        if ($diagnostic['environment'] === 'production') {
            $recommendations[] = 'Ensure all API credentials are properly configured for production.';
            $recommendations[] = 'Check server logs for detailed API error messages.';
        }

        return $recommendations;
    }
}
