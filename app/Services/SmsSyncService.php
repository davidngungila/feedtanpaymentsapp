<?php

namespace App\Services;

use App\Models\MessagingService;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmsSyncService
{
    /**
     * Sync SMS messages from external API to local database
     */
    public function syncSmsMessages($limit = 500, $fromDate = null, $toDate = null, $fetchAll = false)
    {
        try {
            $smsService = MessagingService::where('type', 'SMS')->where('is_active', true)->first();
            
            if (!$smsService) {
                Log::error('No active SMS service found for sync');
                return [
                    'success' => false,
                    'message' => 'No active SMS service found',
                    'synced' => 0,
                    'updated' => 0
                ];
            }

            // If fetchAll is true, get all messages from the beginning
            if ($fetchAll) {
                return $this->syncAllMessages($smsService, $fromDate, $toDate);
            }

            // Build query parameters
            $params = ['limit' => $limit];
            
            if ($fromDate) {
                $params['sentSince'] = $fromDate;
            }
            
            if ($toDate) {
                $params['sentUntil'] = $toDate;
            }

            // Get logs from external API
            $url = $smsService->base_url . '/api/v2/logs?' . http_build_query($params);
            $response = Http::withHeaders($smsService->getApiHeaders())
                           ->timeout(60)
                           ->get($url);

            if (!$response->successful()) {
                Log::error('Failed to fetch SMS logs from API', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to fetch SMS logs from API: ' . $response->status(),
                    'synced' => 0,
                    'updated' => 0
                ];
            }

            $data = $response->json();
            $logs = $data['results'] ?? [];
            
            $syncedCount = 0;
            $updatedCount = 0;
            $errors = [];

            foreach ($logs as $log) {
                try {
                    $result = $this->syncSingleMessage($log, $smsService);
                    
                    if ($result['action'] === 'created') {
                        $syncedCount++;
                    } elseif ($result['action'] === 'updated') {
                        $updatedCount++;
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = "Error syncing message {$log['messageId']}: " . $e->getMessage();
                    Log::error('Error syncing individual SMS message', [
                        'messageId' => $log['messageId'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('SMS sync completed', [
                'total_logs' => count($logs),
                'synced' => $syncedCount,
                'updated' => $updatedCount,
                'errors' => count($errors)
            ]);

            return [
                'success' => true,
                'message' => "Sync completed: {$syncedCount} new, {$updatedCount} updated",
                'synced' => $syncedCount,
                'updated' => $updatedCount,
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            Log::error('SMS sync service error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Sync service error: ' . $e->getMessage(),
                'synced' => 0,
                'updated' => 0
            ];
        }
    }

    /**
     * Sync a single SMS message
     */
    private function syncSingleMessage($log, $smsService)
    {
        $messageId = $log['messageId'] ?? null;
        $recipient = $log['to'] ?? null;
        $messageText = $log['text'] ?? '';
        $senderId = $log['from'] ?? $smsService->sender_id;
        
        if (!$messageId || !$recipient) {
            throw new \Exception('Missing required fields: messageId or to');
        }

        // Check if message already exists
        $existingMessage = SmsMessage::where('message_id', $messageId)->first();
        
        if ($existingMessage) {
            // Update existing message with latest status
            $this->updateExistingMessage($existingMessage, $log);
            
            return [
                'action' => 'updated',
                'message_id' => $messageId
            ];
        } else {
            // Create new message
            $this->createNewMessage($log, $smsService);
            
            return [
                'action' => 'created',
                'message_id' => $messageId
            ];
        }
    }

    /**
     * Create new SMS message from API log
     */
    private function createNewMessage($log, $smsService)
    {
        // Get or create a default user (admin user)
        $user = User::where('email', 'admin@example.com')->first();
        if (!$user) {
            $user = User::first(); // Fallback to first user
        }

        // Determine status from API response
        $statusName = $log['status']['name'] ?? 'UNKNOWN';
        $statusGroupName = $log['status']['groupName'] ?? 'UNKNOWN';
        $statusId = $log['status']['id'] ?? null;
        $statusGroupId = $log['status']['groupId'] ?? null;

        // Map API status to local status
        $localStatus = $this->mapApiStatusToLocal($statusName, $statusGroupName);

        $message = SmsMessage::create([
            'messaging_service_id' => $smsService->id,
            'user_id' => $user->id,
            'message_id' => $log['messageId'],
            'from' => $log['from'] ?? $smsService->sender_id,
            'to' => $log['to'],
            'message' => $log['text'] ?? '',
            'message_type' => 'text',
            'sms_count' => $log['smsCount'] ?? 1,
            'price' => $this->calculatePrice($log),
            'currency' => 'TZS',
            'status_group_id' => $statusGroupId,
            'status_group_name' => $statusGroupName,
            'status_id' => $statusId,
            'status_name' => $localStatus,
            'status_description' => $log['status']['description'] ?? '',
            'sent_at' => $this->parseDateTime($log['sentAt']),
            'delivered_at' => $this->parseDateTime($log['doneAt']),
            'reference' => $log['reference'] ?? '',
            'custom_data' => json_encode([
                'channel' => $log['channel'] ?? '',
                'delivery' => $log['delivery'] ?? '',
                'api_source' => 'external_sync'
            ]),
            'is_test' => false,
            'retry_count' => 0,
            'notes' => 'Auto-synced from external API'
        ]);

        Log::info('New SMS message created from API', [
            'message_id' => $message->id,
            'api_message_id' => $log['messageId'],
            'recipient' => $log['to']
        ]);

        return $message;
    }

    /**
     * Update existing message with latest API data
     */
    private function updateExistingMessage($message, $log)
    {
        // Update status and timestamps
        $statusName = $log['status']['name'] ?? 'UNKNOWN';
        $statusGroupName = $log['status']['groupName'] ?? 'UNKNOWN';
        $statusId = $log['status']['id'] ?? null;
        $statusGroupId = $log['status']['groupId'] ?? null;

        $localStatus = $this->mapApiStatusToLocal($statusName, $statusGroupName);

        $message->update([
            'status_group_id' => $statusGroupId,
            'status_group_name' => $statusGroupName,
            'status_id' => $statusId,
            'status_name' => $localStatus,
            'status_description' => $log['status']['description'] ?? '',
            'sent_at' => $this->parseDateTime($log['sentAt']),
            'delivered_at' => $this->parseDateTime($log['doneAt']),
            'custom_data' => json_encode([
                'channel' => $log['channel'] ?? '',
                'delivery' => $log['delivery'] ?? '',
                'api_source' => 'external_sync',
                'last_sync' => now()->toISOString()
            ]),
            'updated_at' => now()
        ]);

        Log::info('SMS message updated from API', [
            'message_id' => $message->id,
            'api_message_id' => $log['messageId'],
            'new_status' => $localStatus
        ]);
    }

    /**
     * Map API status to local status
     */
    private function mapApiStatusToLocal($statusName, $statusGroupName)
    {
        $statusMap = [
            'DELIVERED' => 'delivered',
            'SENT' => 'sent',
            'ENROUTE' => 'sent',
            'ACCEPTED' => 'sent',
            'PENDING' => 'pending',
            'FAILED' => 'failed',
            'REJECTED' => 'failed',
            'UNKNOWN' => 'pending'
        ];

        return $statusMap[$statusName] ?? 'pending';
    }

    /**
     * Calculate price based on SMS count and service
     */
    private function calculatePrice($log)
    {
        $smsCount = $log['smsCount'] ?? 1;
        // Default price calculation - can be customized based on service
        return $smsCount * 0.0160; // Default TZS 0.0160 per SMS
    }

    /**
     * Parse datetime from API response
     */
    private function parseDateTime($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        try {
            return Carbon::parse($dateTime);
        } catch (\Exception $e) {
            Log::warning('Failed to parse datetime', [
                'datetime' => $dateTime,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Sync all messages from the beginning with pagination
     */
    private function syncAllMessages($smsService, $fromDate = null, $toDate = null)
    {
        $totalSynced = 0;
        $totalUpdated = 0;
        $totalErrors = 0;
        $offset = 0;
        $limit = 500; // Maximum allowed by API
        $hasMore = true;
        $iteration = 0;
        $maxIterations = 1000; // Prevent infinite loops

        Log::info('Starting full historical SMS sync', [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'limit_per_request' => $limit
        ]);

        while ($hasMore && $iteration < $maxIterations) {
            $iteration++;
            
            // Build query parameters for pagination
            $params = [
                'limit' => $limit,
                'offset' => $offset
            ];
            
            if ($fromDate) {
                $params['sentSince'] = $fromDate;
            }
            
            if ($toDate) {
                $params['sentUntil'] = $toDate;
            }

            $url = $smsService->base_url . '/api/v2/logs?' . http_build_query($params);
            
            Log::info("Fetching SMS logs - iteration {$iteration}", [
                'offset' => $offset,
                'limit' => $limit,
                'url' => $url
            ]);

            $response = Http::withHeaders($smsService->getApiHeaders())
                           ->timeout(60)
                           ->get($url);

            if (!$response->successful()) {
                Log::error('Failed to fetch SMS logs in full sync', [
                    'iteration' => $iteration,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to fetch SMS logs: ' . $response->status(),
                    'synced' => $totalSynced,
                    'updated' => $totalUpdated,
                    'iterations' => $iteration,
                    'errors' => $totalErrors
                ];
            }

            $data = $response->json();
            $logs = $data['results'] ?? [];
            
            if (empty($logs)) {
                $hasMore = false;
                Log::info('No more logs found, ending sync', [
                    'iteration' => $iteration,
                    'total_synced' => $totalSynced,
                    'total_updated' => $totalUpdated
                ]);
                break;
            }

            // Process current batch
            $batchSynced = 0;
            $batchUpdated = 0;
            $batchErrors = [];

            foreach ($logs as $log) {
                try {
                    $result = $this->syncSingleMessage($log, $smsService);
                    
                    if ($result['action'] === 'created') {
                        $batchSynced++;
                        $totalSynced++;
                    } elseif ($result['action'] === 'updated') {
                        $batchUpdated++;
                        $totalUpdated++;
                    }
                    
                } catch (\Exception $e) {
                    $batchErrors[] = "Error syncing message {$log['messageId']}: " . $e->getMessage();
                    $totalErrors++;
                    Log::error('Error syncing individual SMS message in full sync', [
                        'iteration' => $iteration,
                        'messageId' => $log['messageId'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Batch sync completed - iteration {$iteration}", [
                'batch_synced' => $batchSynced,
                'batch_updated' => $batchUpdated,
                'batch_errors' => count($batchErrors),
                'total_synced' => $totalSynced,
                'total_updated' => $totalUpdated
            ]);

            // Check if we have more data
            if (count($logs) < $limit) {
                $hasMore = false;
            } else {
                $offset += $limit;
            }

            // Add delay to prevent rate limiting
            if ($hasMore) {
                usleep(500000); // 0.5 second delay
            }
        }

        if ($iteration >= $maxIterations) {
            Log::warning('Full sync stopped due to maximum iterations limit', [
                'iterations' => $iteration,
                'total_synced' => $totalSynced,
                'total_updated' => $totalUpdated
            ]);
        }

        Log::info('Full historical SMS sync completed', [
            'iterations' => $iteration,
            'total_synced' => $totalSynced,
            'total_updated' => $totalUpdated,
            'total_errors' => $totalErrors
        ]);

        return [
            'success' => true,
            'message' => "Full sync completed: {$totalSynced} new, {$totalUpdated} updated over {$iteration} iterations",
            'synced' => $totalSynced,
            'updated' => $totalUpdated,
            'iterations' => $iteration,
            'errors' => $totalErrors
        ];
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats()
    {
        $totalLocal = SmsMessage::count();
        $last24Hours = SmsMessage::where('created_at', '>=', now()->subHours(24))->count();
        $lastSync = SmsMessage::where('custom_data', 'like', '%api_source%')->max('updated_at');

        return [
            'total_messages' => $totalLocal,
            'last_24_hours' => $last24Hours,
            'last_sync' => $lastSync,
            'sync_enabled' => MessagingService::where('type', 'SMS')->where('is_active', true)->exists()
        ];
    }
}
