<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::guard('api')->user();
        
        if ($user->isAdmin) {
            $orders = Order::with('user', 'payments')->get();
        } else {
            $orders = Order::where('user_id', $user->id)->with('payments')->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required|numeric|min:0',
            'status' => 'string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::guard('api')->user();

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $request->total_amount,
            'status' => $request->status ?? 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order->load('payments')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with('user', 'payments')->find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to view this order'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to update this order'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'total_amount' => 'numeric|min:0',
            'status' => 'string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update($request->only(['total_amount', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully',
            'data' => $order->load('payments')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to delete this order'
            ], 403);
        }

        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully'
        ]);
    }
}
