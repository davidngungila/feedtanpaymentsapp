<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestMessagingController extends Controller
{
    public function test()
    {
        return response()->json([
            'message' => 'TestMessagingController works!',
            'timestamp' => now()
        ]);
    }
}
