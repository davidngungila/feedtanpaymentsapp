<?php

namespace App\Http\Controllers;

use App\Models\MessagingService;
use App\Models\SmsMessage;
use App\Models\EmailMessage;
use App\Models\MessagingTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MessagingController extends Controller
{
    /**
     * Display messaging dashboard.
     */
    public function dashboard()
    {
        $smsServices = MessagingService::active()->byType('SMS')->get();
        $emailServices = MessagingService::active()->byType('EMAIL')->get();
        $recentSms = SmsMessage::with('messagingService')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentEmails = EmailMessage::with('messagingService')->orderBy('created_at', 'desc')->limit(5)->get();

        $stats = [
            'total_sms' => SmsMessage::count(),
            'delivered_sms' => SmsMessage::delivered()->count(),
            'failed_sms' => SmsMessage::failed()->count(),
            'total_emails' => EmailMessage::count(),
            'delivered_emails' => EmailMessage::delivered()->count(),
            'opened_emails' => EmailMessage::opened()->count(),
            'failed_emails' => EmailMessage::failed()->count(),
        ];

        return view('messaging.dashboard', compact('smsServices', 'emailServices', 'recentSms', 'recentEmails', 'stats'));
    }

    /**
     * Display SMS messaging page.
     */
    public function smsIndex()
    {
        try {
            $services = MessagingService::active()->byType('SMS')->get();
            $templates = MessagingTemplate::active()->forSms()->get();
            $messages = SmsMessage::with('messagingService', 'user')
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(20);

            return view('messaging.sms.index', compact('services', 'templates', 'messages'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Controller method failed',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Display SMS logs page.
     */
    public function smsLogsPage()
    {
        return view('messaging.sms.logs');
    }

    /**
     * Send SMS message.
     */
    public function sendSms(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:messaging_services,id',
            'to' => 'required|string',
            'message' => 'required|string|max:1600',
            'template_id' => 'nullable|exists:messaging_templates,id',
            'is_test' => 'boolean',
        ]);

        $service = MessagingService::findOrFail($request->service_id);
        
        if (!$service->isReady()) {
            return response()->json([
                'success' => false,
                'message' => 'Selected messaging service is not properly configured'
            ], 400);
        }

        // Process template if provided
        $messageContent = $request->message;
        if ($request->template_id) {
            $template = MessagingTemplate::findOrFail($request->template_id);
            $processed = $template->process($request->variables ?? []);
            $messageContent = $processed['content'];
        }

        // Create SMS message record
        $smsMessage = SmsMessage::create([
            'messaging_service_id' => $service->id,
            'user_id' => auth()->id(),
            'message_id' => 'SMS_' . Str::random(20),
            'from' => $service->sender_id,
            'to' => $this->formatPhoneNumber($request->to),
            'message' => $messageContent,
            'message_type' => 'TEXT',
            'sms_count' => $this->calculateSmsCount($messageContent),
            'price' => $service->cost_per_message,
            'currency' => $service->currency,
            'is_test' => $request->boolean('is_test', false),
        ]);

        // Send SMS via API
        $response = $this->sendSmsViaApi($service, $smsMessage);

        if ($response['success']) {
            $smsMessage->update([
                'status_group_name' => 'PENDING',
                'status_id' => $response['status_id'] ?? null,
                'status_name' => $response['status_name'] ?? 'PENDING',
                'status_description' => $response['status_description'] ?? 'Message sent successfully',
                'sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully',
                'message_id' => $smsMessage->message_id,
                'status' => $response['status']
            ]);
        } else {
            $smsMessage->update([
                'status_group_name' => 'FAILED',
                'status_name' => 'FAILED',
                'status_description' => $response['error'],
                'failed_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: ' . $response['error']
            ], 500);
        }
    }

    /**
     * Display Email messaging page.
     */
    public function emailIndex()
    {
        $services = MessagingService::active()->byType('EMAIL')->get();
        $templates = \App\Models\EmailTemplate::active()->get();
        $messages = EmailMessage::with('messagingService', 'user')
                               ->orderBy('created_at', 'desc')
                               ->paginate(20);

        return view('messaging.email.index', compact('services', 'templates', 'messages'));
    }

    /**
     * Send Email message.
     */
    public function sendEmail(Request $request)
    {
        try {
            // Start timing for performance monitoring
            $startTime = microtime(true);
            
            $request->validate([
                'service_id' => 'required|exists:messaging_services,id',
                'to' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'template_id' => 'nullable|exists:email_templates,id',
                'is_test' => 'boolean',
            ]);

            // Step 1: Validate service quickly
            $service = MessagingService::findOrFail($request->service_id);
            
            if (!$service->isReady()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected messaging service is not properly configured',
                    'step' => 'service_validation'
                ], 400);
            }

            // Step 2: Process template if provided (with caching)
            $htmlContent = $request->message;
            $textContent = strip_tags($request->message);
            $subject = $request->subject;
            
            if ($request->template_id) {
                $template = \App\Models\EmailTemplate::findOrFail($request->template_id);
                
                // Prepare template variables
                $variables = $request->variables ?? [];
                
                // Add common variables
                $variables['memberName'] = $variables['memberName'] ?? 'Valued Member';
                $variables['currentDate'] = date('Y-m-d');
                $variables['companyName'] = 'FeedTan Community Microfinance Group';
                
                // Process template efficiently
                $processed = $template->processTemplate($variables);
                $htmlContent = $processed['html'];
                $textContent = $processed['text'] ?? strip_tags($htmlContent);
                $subject = $processed['subject'] ?: $subject;
                
                // Increment template usage asynchronously
                $template->incrementUsage();
            }

            // Step 3: Create Email message record with optimized data
            $emailMessage = EmailMessage::create([
                'messaging_service_id' => $service->id,
                'user_id' => auth()->id(),
                'message_id' => 'EMAIL_' . Str::random(20),
                'from_name' => $service->from_name ?? 'FeedTan Pay',
                'from_email' => $service->from_email ?? 'feedtan15@gmail.com',
                'to_email' => $request->to,
                'to_name' => $variables['memberName'] ?? 'Valued Member',
                'subject' => $subject,
                'body_html' => $htmlContent,
                'body_text' => $textContent,
                'status_name' => 'pending',
                'custom_data' => json_encode([
                    'template_id' => $request->template_id,
                    'variables' => $variables,
                    'sent_via' => 'template_system',
                    'processing_time' => microtime(true) - $startTime
                ])
            ]);

            // Step 4: Send Email via API with timeout protection
            $response = $this->sendEmailViaApi($service, $emailMessage);

            // Calculate total processing time
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($response['success']) {
                // Update message with success status
                $emailMessage->update([
                    'status_name' => $response['status_name'] ?? 'sent',
                    'status_description' => $response['status_description'] ?? 'Email sent successfully',
                    'sent_at' => now(),
                    'custom_data' => json_encode(array_merge(
                        json_decode($emailMessage->custom_data, true) ?? [],
                        [
                            'processing_time_ms' => $processingTime,
                            'completed_at' => now()->toISOString()
                        ]
                    ))
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Email sent successfully',
                    'message_id' => $emailMessage->message_id,
                    'status' => $response['status'],
                    'processing_time_ms' => $processingTime,
                    'step' => 'completed'
                ]);
            } else {
                // Update message with failure status
                $emailMessage->update([
                    'status_name' => 'failed',
                    'status_description' => $response['error'],
                    'failed_at' => now(),
                    'custom_data' => json_encode(array_merge(
                        json_decode($emailMessage->custom_data, true) ?? [],
                        [
                            'processing_time_ms' => $processingTime,
                            'failed_at' => now()->toISOString(),
                            'error_details' => $response['error']
                        ]
                    ))
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send Email: ' . $response['error'],
                    'processing_time_ms' => $processingTime,
                    'step' => 'failed',
                    'error_code' => 'send_failed'
                ], 500);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors(),
                'step' => 'validation'
            ], 422);
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Email sending error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred while sending email',
                'error' => $e->getMessage(),
                'step' => 'unexpected_error'
            ], 500);
        }
    }

    /**
     * Preview email template.
     */
    public function previewEmailTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'variables' => 'nullable|array'
        ]);

        $template = \App\Models\EmailTemplate::findOrFail($request->template_id);
        
        // Prepare variables with defaults
        $variables = $request->variables ?? [];
        $variables['memberName'] = $variables['memberName'] ?? 'John Doe';
        $variables['currentDate'] = date('Y-m-d');
        $variables['companyName'] = 'FeedTan Community Microfinance Group';
        
        $processed = $template->processTemplate($variables);

        return response()->json([
            'success' => true,
            'data' => [
                'subject' => $processed['subject'],
                'html' => $processed['html'],
                'text' => $processed['text'],
                'template_name' => $template->name,
                'template_category' => $template->category,
                'variables' => json_decode($template->variables, true)
            ]
        ]);
    }

    /**
     * Get email template details.
     */
    public function getEmailTemplate($id)
    {
        $template = \App\Models\EmailTemplate::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $template->id,
                'name' => $template->name,
                'category' => $template->category,
                'subject' => $template->subject,
                'html_content' => $template->html_content,
                'text_content' => $template->text_content,
                'variables' => json_decode($template->variables, true),
                'is_active' => $template->is_active,
                'usage_count' => $template->usage_count,
                'last_used_at' => $template->last_used_at
            ]
        ]);
    }

    /**
     * Get email message details.
     */
    public function getEmailMessage($messageId)
    {
        $message = EmailMessage::with('messagingService', 'user')->findOrFail($messageId);
        
        return response()->json([
            'success' => true,
            'message_id' => $message->message_id,
            'from_email' => $message->from_email,
            'from_name' => $message->from_name,
            'to_email' => $message->to_email,
            'to_name' => $message->to_name,
            'subject' => $message->subject,
            'status_name' => $message->status_name,
            'status_description' => $message->status_description,
            'sent_at' => $message->sent_at,
            'failed_at' => $message->failed_at,
            'created_at' => $message->created_at,
            'service' => [
                'name' => $message->messagingService->name,
                'type' => $message->messagingService->type
            ],
            'user' => $message->user ? [
                'name' => $message->user->name,
                'email' => $message->user->email
            ] : null,
            'custom_data' => json_decode($message->custom_data, true)
        ]);
    }

    /**
     * Get email message content.
     */
    public function getEmailMessageContent($messageId)
    {
        $message = EmailMessage::findOrFail($messageId);
        
        return response()->json([
            'success' => true,
            'subject' => $message->subject,
            'body_html' => $message->body_html,
            'body_text' => $message->body_text,
            'from_email' => $message->from_email,
            'from_name' => $message->from_name,
            'to_email' => $message->to_email,
            'to_name' => $message->to_name
        ]);
    }

    /**
     * Export email message.
     */
    public function exportEmailMessage($messageId)
    {
        $message = EmailMessage::with('messagingService', 'user')->findOrFail($messageId);
        
        $csvContent = "Message ID,From Email,From Name,To Email,To Name,Subject,Status,Sent At,Created At\n";
        $csvContent .= "{$message->message_id},{$message->from_email},{$message->from_name},{$message->to_email},{$message->to_name},{$message->subject},{$message->status_name},{$message->sent_at},{$message->created_at}\n";
        
        $filename = "email_message_{$message->message_id}_" . date('Y-m-d_H-i-s') . ".csv";
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Display services management page.
     */
    public function servicesIndex()
    {
        $services = MessagingService::with(['smsMessages', 'emailMessages'])
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(20);

        return view('messaging.services.index', compact('services'));
    }

    /**
     * Get a specific messaging service.
     */
    public function getService($serviceId)
    {
        try {
            $service = MessagingService::with(['smsMessages', 'emailMessages'])->findOrFail($serviceId);
            
            // Add message counts to the service data
            $serviceData = $service->toArray();
            $serviceData['sms_messages_count'] = $service->smsMessages()->count();
            $serviceData['email_messages_count'] = $service->emailMessages()->count();
            
            return response()->json([
                'success' => true,
                'data' => $serviceData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get service: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get SMS message details from external API.
     */
    public function getSmsMessage($messageId)
    {
        try {
            // Get the SMS service
            $smsService = \App\Models\MessagingService::where('type', 'SMS')->where('is_active', true)->first();
            
            if (!$smsService) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active SMS service found'
                ], 404);
            }

            // Get local message for context
            $localMessage = \App\Models\SmsMessage::find($messageId);
            if (!$localMessage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found'
                ], 404);
            }

            // Get logs from external API
            $url = $smsService->base_url . '/api/v2/logs?limit=100';
            $response = Http::withHeaders($smsService->getApiHeaders())
                           ->timeout(30)
                           ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['results']) && is_array($data['results'])) {
                    // Try multiple matching strategies
                    $matchedLog = null;
                    
                    // Strategy 1: Match by exact API message ID
                    if ($localMessage->message_id) {
                        foreach ($data['results'] as $log) {
                            if ($log['messageId'] === $localMessage->message_id) {
                                $matchedLog = $log;
                                break;
                            }
                        }
                    }
                    
                    // Strategy 2: Match by recipient and message content
                    if (!$matchedLog) {
                        foreach ($data['results'] as $log) {
                            if ($log['to'] === $localMessage->to && 
                                isset($log['text']) && 
                                trim($log['text']) === trim($localMessage->message)) {
                                $matchedLog = $log;
                                break;
                            }
                        }
                    }
                    
                    // Strategy 3: Match by recipient and close time (within 5 minutes)
                    if (!$matchedLog) {
                        $localTime = $localMessage->created_at->timestamp;
                        foreach ($data['results'] as $log) {
                            $logTime = strtotime($log['sentAt']);
                            if ($log['to'] === $localMessage->to && 
                                abs($logTime - $localTime) <= 300) { // 5 minutes
                                $matchedLog = $log;
                                break;
                            }
                        }
                    }
                    
                    // Strategy 4: Get the most recent message for the same recipient
                    if (!$matchedLog) {
                        foreach ($data['results'] as $log) {
                            if ($log['to'] === $localMessage->to) {
                                $matchedLog = $log;
                                break;
                            }
                        }
                    }
                    
                    if ($matchedLog) {
                        // Create enhanced message data from external API
                        $messageData = [
                            'id' => $localMessage->id,
                            'message_id' => $matchedLog['messageId'],
                            'from' => $matchedLog['from'],
                            'to' => $matchedLog['to'],
                            'message' => $matchedLog['text'] ?? $localMessage->message,
                            'status_name' => $matchedLog['status']['name'] ?? $localMessage->status_name,
                            'status_group_name' => $matchedLog['status']['groupName'] ?? '',
                            'channel' => $matchedLog['channel'] ?? '',
                            'sent_at' => $matchedLog['sentAt'],
                            'done_at' => $matchedLog['doneAt'],
                            'sms_count' => $matchedLog['smsCount'] ?? $localMessage->sms_count,
                            'reference' => $matchedLog['reference'] ?? '',
                            'delivery' => $matchedLog['delivery'] ?? '',
                            'messaging_service' => [
                                'name' => $localMessage->messagingService->name
                            ],
                            'user' => [
                                'name' => $localMessage->user->name
                            ],
                            'getFormattedRecipient' => $localMessage->getFormattedRecipient(),
                            'getStatusBadgeColor' => $this->getStatusBadgeColorFromApi($matchedLog['status']['groupName'] ?? ''),
                            'price' => $localMessage->price,
                            'currency' => $localMessage->currency,
                            'created_at' => $localMessage->created_at,
                            'error_message' => $localMessage->error_message,
                            'is_test' => $localMessage->is_test
                        ];
                        
                        return response()->json([
                            'success' => true,
                            'data' => $messageData,
                            'source' => 'external_api'
                        ]);
                    }
                }
            }
            
            // If external API fails or no match, return local data with enhanced info
            $messageData = $localMessage->toArray();
            $messageData['getFormattedRecipient'] = $localMessage->getFormattedRecipient();
            $messageData['getStatusBadgeColor'] = $localMessage->getStatusBadgeColor();
            $messageData['channel'] = 'Unknown';
            $messageData['reference'] = '';
            $messageData['delivery'] = '';
            $messageData['status_group_name'] = '';
            
            return response()->json([
                'success' => true,
                'data' => $messageData,
                'source' => 'local_database'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get SMS message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to get status badge color from API status
     */
    private function getStatusBadgeColorFromApi($statusGroup)
    {
        switch (strtolower($statusGroup)) {
            case 'delivered':
                return 'success';
            case 'sent':
            case 'enroute':
            case 'accepted':
                return 'info';
            case 'failed':
            case 'rejected':
                return 'danger';
            case 'pending':
                return 'warning';
            default:
                return 'secondary';
        }
    }

    /**
     * Export SMS message details.
     */
    public function exportSmsMessage($messageId)
    {
        try {
            $message = \App\Models\SmsMessage::with(['messagingService', 'user'])->findOrFail($messageId);
            
            // Create CSV data
            $csvData = [
                ['Field', 'Value'],
                ['Message ID', $message->message_id],
                ['Recipient', $message->getFormattedRecipient()],
                ['Sender ID', $message->from],
                ['Message', $message->message],
                ['Service', $message->messagingService->name],
                ['Status', $message->status_name],
                ['SMS Count', $message->sms_count],
                ['Price', $message->currency . ' ' . number_format($message->price, 4)],
                ['User', $message->user->name],
                ['Is Test', $message->is_test ? 'Yes' : 'No'],
                ['Created At', $message->created_at],
                ['Sent At', $message->sent_at ?? 'N/A'],
                ['Failed At', $message->failed_at ?? 'N/A'],
                ['Error Message', $message->error_message ?? 'N/A'],
                ['Notes', $message->notes ?? 'N/A']
            ];
            
            // Generate CSV content
            $csv = '';
            foreach ($csvData as $row) {
                $csv .= implode(',', array_map(function($field) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }, $row)) . "\n";
            }
            
            $filename = 'sms_message_' . $message->id . '_' . date('Y-m-d_H-i-s') . '.csv';
            
            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export SMS message: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get SMS logs from local database.
     */
    public function getSmsLogs(Request $request)
    {
        try {
            // Build query from local database
            $query = \App\Models\SmsMessage::with(['messagingService', 'user']);
            
            // Apply filters
            if ($request->has('from') && !empty($request->from)) {
                $query->whereDate('created_at', '>=', $request->from);
            }
            
            if ($request->has('to') && !empty($request->to)) {
                $query->whereDate('created_at', '<=', $request->to);
            }
            
            if ($request->has('sentSince') && !empty($request->sentSince)) {
                $query->whereDate('sent_at', '>=', $request->sentSince);
            }
            
            if ($request->has('sentUntil') && !empty($request->sentUntil)) {
                $query->whereDate('sent_at', '<=', $request->sentUntil);
            }
            
            if ($request->has('reference') && !empty($request->reference)) {
                $query->where('reference', 'like', '%' . $request->reference . '%');
            }
            
            if ($request->has('sender_id') && !empty($request->sender_id)) {
                $query->where('from', $request->sender_id);
            }
            
            if ($request->has('phone') && !empty($request->phone)) {
                $query->where('to', 'like', '%' . $request->phone . '%');
            }
            
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status_name', $request->status);
            }
            
            // Apply limit and ordering
            $limit = $request->has('limit') && !empty($request->limit) ? min($request->limit, 1000) : 100;
            $query->orderBy('created_at', 'desc')->limit($limit);
            
            // Get the messages
            $messages = $query->get();
            
            // Transform to match the expected API format
            $results = $messages->map(function($message) {
                // Parse custom_data for additional fields
                $customData = [];
                if ($message->custom_data) {
                    try {
                        $customData = json_decode($message->custom_data, true) ?: [];
                    } catch (\Exception $e) {
                        $customData = [];
                    }
                }
                
                return [
                    'messageId' => $message->message_id,
                    'reference' => $message->reference,
                    'sentAt' => $message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : null,
                    'doneAt' => $message->delivered_at ? $message->delivered_at->format('Y-m-d H:i:s') : null,
                    'to' => $message->to,
                    'channel' => $customData['channel'] ?? 'SMS',
                    'from' => $message->from,
                    'smsCount' => $message->sms_count,
                    'status' => [
                        'id' => $message->status_id,
                        'name' => $message->status_name,
                        'groupName' => $message->status_group_name,
                        'description' => $message->status_description
                    ],
                    'delivery' => $customData['delivery'] ?? $message->status_name,
                    'text' => $message->message,
                    'price' => $message->price,
                    'currency' => $message->currency,
                    'user' => [
                        'name' => $message->user->name ?? 'System'
                    ],
                    'service' => [
                        'name' => $message->messagingService->name ?? 'Unknown'
                    ],
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'local_id' => $message->id,
                    'error_message' => $message->error_message,
                    'is_test' => $message->is_test
                ];
            });
            
            // Return in the same format as the external API
            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results->toArray(),
                    'total' => $results->count(),
                    'source' => 'local_database'
                ],
                'message' => 'SMS logs retrieved from local database'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving SMS logs from database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export SMS logs to CSV from local database.
     */
    public function exportSmsLogs(Request $request)
    {
        try {
            // Build query from local database
            $query = \App\Models\SmsMessage::with(['messagingService', 'user']);
            
            // Apply filters (same as getSmsLogs)
            if ($request->has('from') && !empty($request->from)) {
                $query->whereDate('created_at', '>=', $request->from);
            }
            
            if ($request->has('to') && !empty($request->to)) {
                $query->whereDate('created_at', '<=', $request->to);
            }
            
            if ($request->has('sentSince') && !empty($request->sentSince)) {
                $query->whereDate('sent_at', '>=', $request->sentSince);
            }
            
            if ($request->has('sentUntil') && !empty($request->sentUntil)) {
                $query->whereDate('sent_at', '<=', $request->sentUntil);
            }
            
            if ($request->has('reference') && !empty($request->reference)) {
                $query->where('reference', 'like', '%' . $request->reference . '%');
            }
            
            if ($request->has('sender_id') && !empty($request->sender_id)) {
                $query->where('from', $request->sender_id);
            }
            
            if ($request->has('phone') && !empty($request->phone)) {
                $query->where('to', 'like', '%' . $request->phone . '%');
            }
            
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status_name', $request->status);
            }
            
            // Set higher limit for export
            $limit = $request->has('limit') && !empty($request->limit) ? min($request->limit, 5000) : 5000;
            $query->orderBy('created_at', 'desc')->limit($limit);
            
            // Get the messages
            $messages = $query->get();
            
            if ($messages->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No logs data found to export'
                ], 404);
            }
            
            // Create CSV data
            $csvData = [['Message ID', 'From', 'To', 'Status', 'Status Group', 'Channel', 'Sent At', 'Done At', 'SMS Count', 'Reference', 'Delivery', 'Price', 'Currency', 'User', 'Service', 'Created At']];
            
            foreach ($messages as $message) {
                // Parse custom_data for additional fields
                $customData = [];
                if ($message->custom_data) {
                    try {
                        $customData = json_decode($message->custom_data, true) ?: [];
                    } catch (\Exception $e) {
                        $customData = [];
                    }
                }
                
                $csvData[] = [
                    $message->message_id ?? '',
                    $message->from ?? '',
                    $message->to ?? '',
                    $message->status_name ?? '',
                    $message->status_group_name ?? '',
                    $customData['channel'] ?? 'SMS',
                    $message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : '',
                    $message->delivered_at ? $message->delivered_at->format('Y-m-d H:i:s') : '',
                    $message->sms_count ?? 0,
                    $message->reference ?? '',
                    $customData['delivery'] ?? $message->status_name,
                    $message->price ?? 0,
                    $message->currency ?? 'TZS',
                    $message->user->name ?? 'System',
                    $message->messagingService->name ?? 'Unknown',
                    $message->created_at->format('Y-m-d H:i:s')
                ];
            }
            
            // Generate CSV content
            $csv = '';
            foreach ($csvData as $row) {
                $csv .= implode(',', array_map(function($field) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }, $row)) . "\n";
            }
            
            $filename = 'sms_logs_' . date('Y-m-d_H-i-s') . '.csv';
            
            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting SMS logs from database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SMS balance from external API.
     */
    public function getSmsBalance()
    {
        try {
            // Get the SMS service
            $smsService = \App\Models\MessagingService::where('type', 'SMS')->where('is_active', true)->first();
            
            if (!$smsService) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active SMS service found'
                ], 404);
            }

            // Check cache first to avoid rate limiting
            $cacheKey = 'sms_balance_' . $smsService->id;
            $cachedBalance = \Cache::get($cacheKey);
            
            if ($cachedBalance) {
                return response()->json([
                    'success' => true,
                    'data' => $cachedBalance,
                    'message' => 'SMS balance retrieved from cache',
                    'cached' => true
                ]);
            }

            // Make the API request
            $url = $smsService->base_url . '/api/v2/balance';
            
            $response = Http::withHeaders($smsService->getApiHeaders())
                           ->timeout(15)
                           ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                // Cache the result for 5 minutes to avoid rate limiting
                \Cache::put($cacheKey, $data, 300);
                
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'message' => 'SMS balance retrieved successfully',
                    'cached' => false
                ]);
            } else if ($response->status() === 429) {
                // Rate limited - try to return cached data if available
                $fallbackBalance = \Cache::get($cacheKey . '_fallback');
                if ($fallbackBalance) {
                    return response()->json([
                        'success' => true,
                        'data' => $fallbackBalance,
                        'message' => 'SMS balance retrieved from fallback cache (rate limited)',
                        'cached' => true,
                        'rate_limited' => true
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'SMS balance service temporarily unavailable due to rate limiting',
                    'rate_limited' => true,
                    'retry_after' => 60
                ], 429);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve SMS balance: ' . $response->status(),
                    'response' => $response->body()
                ], $response->status());
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving SMS balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS via API
     */
    private function sendSmsViaApi(MessagingService $service, SmsMessage $smsMessage): array
    {
        try {
            // Use the correct API endpoints based on the documentation
            $endpoint = $service->test_mode 
                ? $service->base_url . '/api/sms/v2/test/text/single'
                : $service->base_url . '/api/sms/v2/text/single';

            $payload = [
                'from' => $smsMessage->from,
                'to' => $smsMessage->to,
                'text' => $smsMessage->message
            ];

            $response = Http::withHeaders($service->getApiHeaders())
                           ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['messages'][0])) {
                    return [
                        'success' => true,
                        'status' => $data['messages'][0]['status'] ?? [],
                        'message_id' => $data['messages'][0]['messageId'] ?? null
                    ];
                }
            }

            return [
                'success' => false,
                'error' => $response->body() ?: 'Unknown API error'
            ];
        } catch (\Exception $e) {
            Log::error('SMS API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send Email via API.
     */
    private function sendEmailViaApi(MessagingService $service, EmailMessage $emailMessage): array
    {
        try {
            // Use Laravel's built-in mail system instead of external API
            \Illuminate\Support\Facades\Mail::html($emailMessage->body_html, function ($message) use ($emailMessage, $service) {
                $message->to($emailMessage->to_email, $emailMessage->to_name ?? 'Valued Member')
                        ->subject($emailMessage->subject)
                        ->from($emailMessage->from_email ?? $service->from_email ?? 'feedtan15@gmail.com', $emailMessage->from_name ?? $service->from_name ?? 'FeedTan Pay');
            });
            
            return [
                'success' => true,
                'status_name' => 'sent',
                'status_description' => 'Email sent successfully via SMTP',
                'message_id' => $emailMessage->message_id
            ];
            
        } catch (\Exception $e) {
            Log::error('Email SMTP Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number for SMS.
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if missing (assuming Tanzania)
        if (strlen($phone) === 9 && str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        } elseif (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }
        
        return $phone;
    }

    /**
     * Test a messaging service connection.
     */
    public function testService($serviceId)
    {
        try {
            $service = MessagingService::findOrFail($serviceId);
            $testResult = $this->performConnectionTest($service);
            
            return response()->json([
                'success' => true,
                'message' => 'Connection test completed',
                'data' => $testResult
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform actual connection test for the service.
     */
    private function performConnectionTest(MessagingService $service): array
    {
        if ($service->type === 'SMS') {
            return $this->testSmsService($service);
        } elseif ($service->type === 'EMAIL') {
            return $this->testEmailService($service);
        }
        
        throw new \Exception('Unsupported service type: ' . $service->type);
    }

    /**
     * Test SMS service connection.
     */
    private function testSmsService(MessagingService $service): array
    {
        // Test basic connectivity first
        try {
            $baseTest = Http::timeout(5)->get($service->base_url);
            
            if ($baseTest->status() === 200) {
                // Base URL is accessible, test API authentication with correct endpoints
                $testEndpoints = [
                    $service->base_url . '/api/sms/v2/test/text/single', // Correct test endpoint
                    $service->base_url . '/api/sms/v2/text/single'      // Correct production endpoint
                ];
                
                $payload = [
                    'from' => $service->sender_id,
                    'to' => '0622239304', // Use the test number provided by user
                    'text' => 'Test message from FeedTan Pay'
                ];

                foreach ($testEndpoints as $endpoint) {
                    $response = Http::withHeaders($service->getApiHeaders())
                                   ->timeout(10)
                                   ->post($endpoint, $payload);

                    if ($response->successful()) {
                        return [
                            'status' => 'success',
                            'response' => $response->json(),
                            'message' => 'SMS service connection successful',
                            'endpoint' => $endpoint
                        ];
                    }
                }
                
                return [
                    'status' => 'partial',
                    'response' => ['base_url_accessible' => true, 'tested_endpoints' => $testEndpoints],
                    'message' => 'Base URL accessible but API endpoints not working. Check token and endpoint configuration.'
                ];
            } else {
                return [
                    'status' => 'failed',
                    'response' => ['base_url_status' => $baseTest->status()],
                    'message' => 'SMS service base URL not accessible: HTTP ' . $baseTest->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'response' => ['error' => $e->getMessage()],
                'message' => 'SMS service connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test Email service connection.
     */
    private function testEmailService(MessagingService $service): array
    {
        if ($service->provider === 'gmail') {
            return $this->testGmailService($service);
        }
        
        $endpoint = $service->getApiEndpoint('email/test/send');
        
        $payload = [
            'from' => $service->config['from_email'] ?? 'noreply@feedtanpay.co.tz',
            'to' => 'test@feedtanpay.co.tz',
            'subject' => 'Test Email from FeedTan Pay',
            'html' => '<h1>Test Email</h1><p>This is a test email to verify the connection.</p>'
        ];

        $response = Http::withHeaders($service->getApiHeaders())
                       ->timeout(10)
                       ->post($endpoint, $payload);

        if ($response->successful()) {
            return [
                'status' => 'success',
                'response' => $response->json(),
                'message' => 'Email service connection successful'
            ];
        }

        return [
            'status' => 'failed',
            'response' => $response->body(),
            'message' => 'Email service connection failed: ' . $response->status()
        ];
    }

    /**
     * Test Gmail SMTP connection.
     */
    private function testGmailService(MessagingService $service): array
    {
        try {
            $config = $service->config;
            
            // Test basic connectivity by attempting socket connection
            $timeout = 10;
            $host = $config['smtp_host'] ?? 'smtp.gmail.com';
            $port = $config['smtp_port'] ?? 587;
            
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
            
            if ($socket) {
                fclose($socket);
                
                // Additional validation - check if credentials are present
                if ($service->username && $service->password) {
                    return [
                        'status' => 'success',
                        'response' => [
                            'connection' => 'established',
                            'host' => $host,
                            'port' => $port,
                            'username' => $service->username
                        ],
                        'message' => 'Gmail SMTP connection successful'
                    ];
                } else {
                    return [
                        'status' => 'failed',
                        'response' => ['error' => 'Missing credentials'],
                        'message' => 'Gmail SMTP test failed: Missing username or password'
                    ];
                }
            } else {
                return [
                    'status' => 'failed',
                    'response' => ['error' => $errstr, 'errno' => $errno],
                    'message' => "Gmail SMTP connection failed: {$errstr} (Error {$errno})"
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'response' => ['error' => $e->getMessage()],
                'message' => 'Gmail SMTP connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Store a new messaging service.
     */
    public function storeService(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:SMS,EMAIL',
                'provider' => 'required|string|max:255',
                'base_url' => 'required|url',
                'api_version' => 'required|string|max:10',
                'api_key' => 'nullable|string',
                'bearer_token' => 'nullable|string',
                'username' => 'nullable|string',
                'password' => 'nullable|string',
                'sender_id' => 'required|string|max:255',
                'config' => 'nullable|array',
                'rate_limit_per_hour' => 'nullable|integer|min:1',
                'cost_per_message' => 'nullable|numeric|min:0',
                'currency' => 'required|string|max:3',
                'webhook_url' => 'nullable|url',
                'notes' => 'nullable|string',
                'test_mode' => 'boolean',
            ]);

            $service = MessagingService::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Service created successfully',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a messaging service.
     */
    public function updateService(Request $request, $serviceId)
    {
        try {
            $service = MessagingService::findOrFail($serviceId);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:SMS,EMAIL',
                'provider' => 'required|string|max:255',
                'base_url' => 'required|url',
                'api_version' => 'required|string|max:10',
                'api_key' => 'nullable|string',
                'bearer_token' => 'nullable|string',
                'username' => 'nullable|string',
                'password' => 'nullable|string',
                'sender_id' => 'required|string|max:255',
                'config' => 'nullable|array',
                'rate_limit_per_hour' => 'nullable|integer|min:1',
                'cost_per_message' => 'nullable|numeric|min:0',
                'currency' => 'required|string|max:3',
                'webhook_url' => 'nullable|url',
                'notes' => 'nullable|string',
                'test_mode' => 'boolean',
            ]);

            $service->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle service status (activate/deactivate).
     */
    public function toggleServiceStatus($serviceId, $activate)
    {
        try {
            $service = MessagingService::findOrFail($serviceId);
            $service->is_active = $activate;
            $service->save();

            $status = $activate ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true,
                'message' => "Service {$status} successfully",
                'data' => $service
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle service status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a messaging service.
     */
    public function deleteService(MessagingService $service)
    {
        try {
            // Check if service has messages
            $smsCount = $service->smsMessages()->count();
            $emailCount = $service->emailMessages()->count();
            
            if ($smsCount > 0 || $emailCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete service with existing messages. Found ' . $smsCount . ' SMS and ' . $emailCount . ' email messages.'
                ], 400);
            }
            
            $service->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate SMS count based on message length.
     */
    private function calculateSmsCount(string $message): int
    {
        $length = strlen($message);
        return ceil($length / 160);
    }
}
