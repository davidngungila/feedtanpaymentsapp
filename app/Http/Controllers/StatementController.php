<?php

namespace App\Http\Controllers;

use App\Services\ClickPesaAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatementController extends Controller
{
    protected ClickPesaAPIService $clickPesa;

    public function __construct(ClickPesaAPIService $clickPesa)
    {
        $this->clickPesa = $clickPesa;
    }

    /**
     * Display account statement page
     */
    public function index(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $currency = $request->get('currency', 'TZS');

            // Get account balance
            $balance = $this->clickPesa->getAccountBalance();

            // Get account statement
            $params = [
                'currency' => $currency
            ];

            if ($startDate) {
                $params['startDate'] = $startDate;
            }

            if ($endDate) {
                $params['endDate'] = $endDate;
            }

            $statement = $this->clickPesa->getAccountStatement($params);

            return view('statement.index', compact('balance', 'statement', 'startDate', 'endDate', 'currency'));

        } catch (\Exception $e) {
            Log::error('Statement fetch error', ['error' => $e->getMessage()]);
            
            return view('statement.index', [
                'balance' => [],
                'statement' => [],
                'error' => 'Failed to fetch account statement. Please try again later.',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'currency' => $currency
            ]);
        }
    }

    /**
     * Handle payment sync request
     */
    public function syncPayments(Request $request)
    {
        try {
            // Run the sync command
            $command = new \App\Console\Commands\SyncPaymentsCommand($this->clickPesa);
            $exitCode = $command->handle();

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payments synced successfully from ClickPesa API'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to sync payments from API'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Payment sync error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while syncing payments: ' . $e->getMessage()
            ], 500);
        }
    }
}
