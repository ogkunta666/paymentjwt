<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::guard('api')->user();
        
        if ($user->isAdmin) {
            $payments = Payment::with('order.user')->get();
        } else {
            $payments = Payment::whereHas('order', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('order')->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'paid_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($request->order_id);
        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to create payment for this order'
            ], 403);
        }

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'paid_at' => $request->paid_at ?? now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment created successfully',
            'data' => $payment->load('order')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = Payment::with('order.user')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $payment->order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to view this payment'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payment = Payment::with('order')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $payment->order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to update this payment'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'string|max:255',
            'amount' => 'numeric|min:0',
            'paid_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment->update($request->only(['payment_method', 'amount', 'paid_at']));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment updated successfully',
            'data' => $payment->load('order')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payment = Payment::with('order')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $payment->order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to delete this payment'
            ], 403);
        }

        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully'
        ]);
    }
}
