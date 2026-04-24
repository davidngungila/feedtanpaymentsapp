<?php

namespace App\Console\Commands;

use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestPaymentCommand extends Command
{
    protected $signature = 'test:payment {amount=2000} {phone=0622239304} {name="Test User"}';
    protected $description = 'Test payment initiation with ClickPesa API';

    protected ClickPesaAPIService $api;
    protected MessagingServiceAPI $messaging;

    public function __construct(ClickPesaAPIService $api, MessagingServiceAPI $messaging)
    {
        parent::__construct();
        $this->api = $api;
        $this->messaging = $messaging;
    }

    public function handle()
    {
        $amount = $this->argument('amount');
        $phone = $this->argument('phone');
        $name = $this->argument('name');

        $this->info("=== Testing Payment Initiation ===");
        $this->info("Amount: {$amount} TZS");
        $this->info("Phone: {$phone}");
        $this->info("Name: {$name}");
        $this->info("");

        try {
            // Format phone number
            $formattedPhone = $this->api->validatePhoneNumber($phone);
            if (!$formattedPhone) {
                $this->error("Invalid phone number format. Use: 255712345678");
                return 1;
            }
            
            $this->info("Formatted phone: {$formattedPhone}");

            // Generate order reference
            $orderReference = $this->api->generateOrderReference();
            $this->info("Order Reference: {$orderReference}");

            // Preview the payment
            $this->info("1. Previewing payment...");
            $preview = $this->api->previewUSSDPush($amount, $orderReference, $formattedPhone, true);
            
            if (empty($preview['activeMethods'])) {
                $this->error("No active payment methods available for this phone number");
                return 1;
            }

            $this->info("✅ Preview successful!");
            $this->info("Available methods:");
            foreach ($preview['activeMethods'] as $method) {
                $this->info("  - {$method['name']}: {$method['status']} (Fee: {$method['fee']})");
            }

            if (isset($preview['sender'])) {
                $this->info("Sender details: {$preview['sender']['accountName']} ({$preview['sender']['accountProvider']})");
            }

            // Save transaction to database
            $this->info("2. Saving transaction to database...");
            $transaction = Transaction::create([
                'order_reference' => $orderReference,
                'status' => 'PROCESSING',
                'amount' => $amount,
                'currency' => 'TZS',
                'phone' => $formattedPhone,
                'payer_name' => $name,
                'description' => 'Test payment via command',
                'type' => 'payment',
                'user_id' => 1, // Assuming user ID 1 exists
            ]);

            $this->info("✅ Transaction saved with ID: {$transaction->id}");

            // Initiate the payment
            $this->info("3. Initiating USSD Push...");
            $customerDetails = [
                'customerName' => $name,
                'description' => 'Test payment via command'
            ];
            
            $payment = $this->api->initiateUSSDPush($amount, $orderReference, $formattedPhone, null, $customerDetails);
            
            $this->info("✅ Payment initiated successfully!");
            $this->info("Transaction ID: {$payment['id']}");
            $this->info("Status: {$payment['status']}");
            $this->info("Channel: " . ($payment['channel'] ?? 'N/A'));

            // Update transaction with API response
            if (isset($payment['id'])) {
                $transaction->update([
                    'transaction_id' => $payment['id'],
                    'status' => $payment['status'],
                    'payment_method' => $payment['channel'] ?? null,
                ]);
            }

            // Send SMS notification
            $this->info("4. Sending SMS notification...");
            try {
                $this->sendPaymentInitiationNotification($formattedPhone, $orderReference, $amount, $name);
                $this->info("✅ SMS notification sent");
            } catch (\Exception $e) {
                $this->warn("⚠️  SMS notification failed: " . $e->getMessage());
            }

            $this->info("");
            $this->info("=== Payment Initiated Successfully! ===");
            $this->info("Order Reference: {$orderReference}");
            $this->info("Transaction ID: {$payment['id']}");
            $this->info("Status: {$payment['status']}");
            $this->info("Amount: {$amount} TZS");
            $this->info("Phone: {$formattedPhone}");
            $this->info("");
            $this->info("Check payment status with:");
            $this->info("php artisan test:payment-status {$orderReference}");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Payment initiation failed: " . $e->getMessage());
            Log::error('Test payment failed', [
                'amount' => $amount,
                'phone' => $phone,
                'name' => $name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function sendPaymentInitiationNotification(string $phoneNumber, string $orderReference, float $amount, string $payerName)
    {
        $message = "FEEDTAN: Payment Initiated\n" .
                  "Order Ref: {$orderReference}\n" .
                  "Amount: " . number_format($amount, 2) . " TZS\n" .
                  "Payer: {$payerName}\n" .
                  "Phone: {$phoneNumber}\n" .
                  "Status: USSD Push Sent\n" .
                  "Please complete payment on your phone.";

        $this->messaging->sendSMS($phoneNumber, $message);
    }
}
