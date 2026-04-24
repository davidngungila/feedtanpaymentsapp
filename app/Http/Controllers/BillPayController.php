<?php

namespace App\Http\Controllers;

use App\Models\BillPayNumber;
use App\Services\ClickPesaAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillPayController extends Controller
{
    protected ClickPesaAPIService $clickPesa;

    public function __construct(ClickPesaAPIService $clickPesa)
    {
        $this->clickPesa = $clickPesa;
    }

    /**
     * Display all bill pay numbers
     */
    public function index(Request $request)
    {
        try {
            $query = BillPayNumber::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $query->search($request->search);
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->byStatus($request->status);
            }

            // Filter by type
            if ($request->has('type') && !empty($request->type)) {
                $query->byType($request->type);
            }

            $bills = $query->orderBy('created_at', 'desc')->paginate(10);

            return view('billpay.index', compact('bills'));
        } catch (\Exception $e) {
            Log::error('BillPay index error', ['error' => $e->getMessage()]);
            return view('billpay.index', [
                'bills' => collect([]),
                'error' => 'Failed to load bill pay numbers. Please try again.'
            ]);
        }
    }

    /**
     * Show the form for creating a new bill pay number
     */
    public function create()
    {
        return view('billpay.create');
    }

    /**
     * Store a newly created bill pay number
     */
    public function store(Request $request)
    {
        $request->validate([
            'bill_description' => 'required|string|max:255',
            'bill_amount' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'bill_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'bill_type' => 'required|in:order,customer',
            'bill_payment_mode' => 'required|string|max:50',
        ]);

        try {
            // Format phone number if provided
            $customerPhone = null;
            if ($request->customer_phone) {
                $customerPhone = $this->clickPesa->validatePhoneNumber($request->customer_phone);
            }

            // Create bill pay number via ClickPesa API
            $billData = [
                'billDescription' => $request->bill_description,
                'billAmount' => $request->bill_amount,
                'billCurrency' => 'TZS',
                'billPaymentMode' => $request->bill_payment_mode,
                'billType' => $request->bill_type,
                'billCustomerName' => $request->customer_name,
                'customerEmail' => $request->customer_email,
                'customerPhone' => $customerPhone,
                'billReference' => $request->bill_reference,
            ];

            // Create bill via API
            $billResponse = $this->createBillViaAPI($billData);

            // Save to database
            $billPayNumber = BillPayNumber::createFromApiResponse($billResponse, [
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('billpay.all')
                ->with('success', 'Bill pay number created successfully: ' . $billResponse['billPayNumber']);

        } catch (\Exception $e) {
            Log::error('BillPay creation error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create bill pay number: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified bill pay number
     */
    public function show($billPayNumber)
    {
        try {
            $bill = BillPayNumber::where('bill_pay_number', $billPayNumber)->firstOrFail();
            
            // Try to get latest status from API
            try {
                $apiData = $this->getBillFromAPI($billPayNumber);
                if ($apiData && isset($apiData['billStatus'])) {
                    // Update local status if different
                    if ($bill->bill_status !== $apiData['billStatus']) {
                        $bill->update(['bill_status' => $apiData['billStatus']]);
                    }
                }
            } catch (\Exception $e) {
                // API call failed, continue with local data
                Log::warning('Failed to fetch bill status from API', [
                    'bill_number' => $billPayNumber,
                    'error' => $e->getMessage()
                ]);
            }

            return view('billpay.show', compact('bill'));
        } catch (\Exception $e) {
            Log::error('BillPay show error', [
                'bill_number' => $billPayNumber,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('billpay.all')
                ->with('error', 'Bill pay number not found.');
        }
    }

    /**
     * Update the specified bill pay number
     */
    public function update(Request $request, $billPayNumber)
    {
        try {
            $bill = BillPayNumber::where('bill_pay_number', $billPayNumber)->firstOrFail();

            $request->validate([
                'bill_status' => 'required|in:ACTIVE,INACTIVE,COMPLETED',
                'notes' => 'nullable|string|max:1000',
            ]);

            $bill->update([
                'bill_status' => $request->bill_status,
                'notes' => $request->notes,
            ]);

            return redirect()->route('billpay.show', $billPayNumber)
                ->with('success', 'Bill pay number updated successfully.');

        } catch (\Exception $e) {
            Log::error('BillPay update error', [
                'bill_number' => $billPayNumber,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update bill pay number: ' . $e->getMessage());
        }
    }

    /**
     * Create bill via ClickPesa API
     */
    private function createBillViaAPI(array $data): array
    {
        // For now, return mock response - implement real API call later
        return [
            'billPayNumber' => 'BILL' . time(),
            'billDescription' => $data['billDescription'],
            'billAmount' => $data['billAmount'],
            'billCurrency' => $data['billCurrency'],
            'billPaymentMode' => $data['billPaymentMode'],
            'billStatus' => 'ACTIVE',
            'billType' => $data['billType'],
            'billCustomerName' => $data['billCustomerName'],
            'customerEmail' => $data['customerEmail'],
            'customerPhone' => $data['customerPhone'],
            'billReference' => $data['billReference'],
        ];
    }

    /**
     * Get bill from ClickPesa API
     */
    private function getBillFromAPI(string $billPayNumber): ?array
    {
        // For now, return null - implement real API call later
        return null;
    }
}
