<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string',
            'status' => 'required|in:pending,completed,failed',
        ]);

        $transactionId = $request->input('transaction_id');
        $status = $request->input('status');

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if ($payment) {
            $payment->status = $status;
            $payment->save();
        }

        return response()->json(['message' => 'Payment callback processed successfully'], 200);
    }

    public function createPayment(Request $request, $orderId)
    {


        $order = auth()->user()->orders()->findOrFail($orderId);

        $payment = new Payment([
            'order_id' => $order->id,
            'transaction_id' => 'midtrans_transaction_id',
            'payment_method' => 'midtrans',
            'status' => 'pending',
            'amount' => $order->total_price,
        ]);

        $payment->save();

        return response()->json(['message' => 'Payment created successfully'], 201);
    }
}
