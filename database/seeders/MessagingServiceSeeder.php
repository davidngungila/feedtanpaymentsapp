<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MessagingService;
use App\Models\MessagingTemplate;

class MessagingServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create SMS Services
        $this->createSmsServices();
        
        // Create Email Services
        $this->createEmailServices();
        
        // Create Messaging Templates
        $this->createMessagingTemplates();
    }
    
    private function createSmsServices()
    {
        // Primary SMS Service - FeedTan Pay
        MessagingService::create([
            'name' => 'Primary SMS Service',
            'type' => 'SMS',
            'provider' => 'messaging-service.co.tz',
            'base_url' => 'https://messaging-service.co.tz',
            'api_version' => 'v2',
            'api_key' => 'f9a89f439206e27169ead766463ca92c',
            'bearer_token' => 'f9a89f439206e27169ead766463ca92c',
            'sender_id' => 'FEEDTAN',
            'rate_limit_per_hour' => 1000,
            'cost_per_message' => 0.0160,
            'currency' => 'TZS',
            'is_active' => true,
            'test_mode' => false,
            'webhook_url' => 'https://feedtanpay.co.tz/webhook/sms',
            'notes' => 'Primary SMS service with API Token: f9a89f439206e27169ead766463ca92c',
        ]);
    }
    
    private function createEmailServices()
    {
        // Primary Email Service - Gmail
        MessagingService::create([
            'name' => 'Primary Email Service - Gmail',
            'type' => 'EMAIL',
            'provider' => 'gmail',
            'base_url' => 'https://gmail.googleapis.com',
            'api_version' => 'v1',
            'username' => 'feedtan15@gmail.com',
            'password' => 'dmxf jyhl eymc libp',
            'sender_id' => 'FeedTan Pay',
            'config' => [
                'from_email' => 'feedtan15@gmail.com',
                'from_name' => 'FeedTan Pay',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'encryption' => 'tls'
            ],
            'rate_limit_per_hour' => 2000,
            'cost_per_message' => 0.0000,
            'currency' => 'TZS',
            'is_active' => true,
            'test_mode' => false,
            'webhook_url' => 'https://feedtanpay.co.tz/webhook/email',
            'notes' => 'Primary Gmail service with app password: dmxf jyhl eymc libp',
        ]);
    }
    
    private function createMessagingTemplates()
    {
        // SMS Templates
        MessagingTemplate::create([
            'name' => 'Welcome SMS',
            'type' => 'SMS',
            'category' => 'Welcome',
            'subject' => 'Welcome to FeedTan Pay',
            'content' => 'Welcome to FeedTan Pay! Your account has been successfully created. Start enjoying our services today.',
            'variables' => json_encode(['customer_name', 'account_number']),
            'is_active' => true,
            'created_by' => 1,
            'description' => 'Welcome message for new users',
        ]);
        
        MessagingTemplate::create([
            'name' => 'Transaction Confirmation',
            'type' => 'SMS',
            'category' => 'Transaction',
            'subject' => 'Transaction Confirmation',
            'content' => 'Your transaction of TZS {amount} has been {status}. Reference: {reference}. Thank you for using FeedTan Pay.',
            'variables' => json_encode(['amount', 'status', 'reference', 'customer_name']),
            'is_active' => true,
            'created_by' => 1,
            'description' => 'Transaction confirmation message',
        ]);
        
        MessagingTemplate::create([
            'name' => 'Payment Reminder',
            'type' => 'SMS',
            'category' => 'Reminder',
            'subject' => 'Payment Reminder',
            'content' => 'Reminder: Your payment of TZS {amount} is due on {due_date}. Please make your payment to avoid service interruption.',
            'variables' => json_encode(['amount', 'due_date', 'customer_name', 'invoice_number']),
            'is_active' => true,
            'created_by' => 1,
            'description' => 'Payment reminder notification',
        ]);
        
        // Email Templates
        MessagingTemplate::create([
            'name' => 'Welcome Email',
            'type' => 'EMAIL',
            'category' => 'Welcome',
            'subject' => 'Welcome to FeedTan Pay - Your Journey Starts Here!',
            'content' => '<h2>Welcome to FeedTan Pay!</h2><p>Dear {customer_name},</p><p>Thank you for joining FeedTan Pay. Your account has been successfully created with account number {account_number}.</p><p>Start exploring our services today!</p><p>Best regards,<br>FeedTan Pay Team</p>',
            'variables' => json_encode(['customer_name', 'account_number']),
            'is_active' => true,
            'created_by' => 1,
            'description' => 'Welcome email for new users',
        ]);
        
        MessagingTemplate::create([
            'name' => 'Transaction Receipt',
            'type' => 'EMAIL',
            'category' => 'Transaction',
            'subject' => 'Transaction Receipt - {reference}',
            'content' => '<h2>Transaction Receipt</h2><p>Dear {customer_name},</p><p>Your transaction has been processed successfully:</p><ul><li>Amount: TZS {amount}</li><li>Status: {status}</li><li>Reference: {reference}</li><li>Date: {transaction_date}</li></ul><p>Thank you for using FeedTan Pay!</p>',
            'variables' => json_encode(['customer_name', 'amount', 'status', 'reference', 'transaction_date']),
            'is_active' => true,
            'created_by' => 1,
            'description' => 'Transaction receipt email',
        ]);
        
        MessagingTemplate::create([
            'name' => 'Password Reset',
            'type' => 'EMAIL',
            'category' => 'Security',
            'subject' => 'Reset Your FeedTan Pay Password',
            'content' => '<h2>Password Reset Request</h2><p>Dear {customer_name},</p><p>We received a request to reset your password. Click the link below to reset your password:</p><p><a href="{reset_link}">Reset Password</a></p><p>This link will expire in {expiry_hours} hours.</p><p>If you did not request this, please ignore this email.</p><p>Best regards,<br>FeedTan Pay Team</p>',
            'variables' => json_encode(['customer_name', 'reset_link', 'expiry_hours']),
            'is_active' => true,
            'created_by' => 1,
            'description' => 'Password reset email',
        ]);
    }
}
