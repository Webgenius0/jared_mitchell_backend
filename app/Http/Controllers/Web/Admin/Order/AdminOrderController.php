<?php

namespace App\Http\Controllers\Web\Admin\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index()
    {
        return view('web.admin.orders.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = Order::with(['user.profile'])->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('order_info', function ($row) {
                return '<strong>' . e($row->order_number) . '</strong>' .
                    '<br><small class="text-muted">' . $row->created_at->format('M d, Y h:i A') . '</small>';
            })
            ->addColumn('customer', function ($row) {
                $name = $row->user?->profile?->name ?? 'N/A';
                $email = $row->user?->email ?? '';
                return '<div>' . e($name) . '<br><small class="text-muted">' . e($email) . '</small></div>';
            })
            ->addColumn('total_display', function ($row) {
                return '<span class="fw-semibold">$' . number_format($row->total, 2) . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $statusClasses = [
                    'pending'   => 'bg-warning text-dark',
                    'confirmed' => 'bg-info',
                    'processing'=> 'bg-primary',
                    'shipped'   => 'bg-secondary',
                    'delivered' => 'bg-success',
                    'cancelled' => 'bg-danger',
                    'refunded'  => 'bg-dark',
                ];
                $class = $statusClasses[$row->status] ?? 'bg-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('payment_badge', function ($row) {
                $paymentClasses = [
                    'unpaid'             => 'bg-danger',
                    'paid'               => 'bg-success',
                    'refunded'           => 'bg-dark',
                    'partially_refunded' => 'bg-warning text-dark',
                ];
                $class = $paymentClasses[$row->payment_status] ?? 'bg-secondary';
                return '<span class="badge ' . $class . '">' . str_replace('_', ' ', ucfirst($row->payment_status)) . '</span>';
            })
            ->addColumn('items_count', function ($row) {
                return $row->items_count;
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="' . route('admin.orders.show', $row->id) . '" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>';
                return '<div class="d-flex gap-1 justify-content-center">' . $showBtn . '</div>';
            })
            ->filterColumn('order_info', function ($query, $keyword) {
                $query->where('order_number', 'like', "%{$keyword}%");
            })
            ->rawColumns(['order_info', 'customer', 'total_display', 'status_badge', 'payment_badge', 'action'])
            ->make(true);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load([
            'user.profile',
            'items.product',
            'shippingAddress',
            'billingAddress',
        ]);

        $statuses = [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
        ];

        $paymentStatuses = [
            Order::PAYMENT_UNPAID,
            Order::PAYMENT_PAID,
            Order::PAYMENT_REFUNDED,
            Order::PAYMENT_PARTIALLY_REFUNDED,
        ];

        return view('web.admin.orders.show', compact('order', 'statuses', 'paymentStatuses'));
    }

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        try {
            $updatedOrder = $this->orderService->updateStatus(
                $order->id,
                $request->input('status')
            );

            return response()->json([
                'success' => true,
                'message' => 'Order status updated to "' . ucfirst($request->status) . '".',
                'data'    => [
                    'status' => $updatedOrder->status,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status.',
            ], 500);
        }
    }

    /**
     * Update the payment status.
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status'  => 'required|in:unpaid,paid,refunded,partially_refunded',
            'transaction_id'  => 'nullable|string|max:255',
        ]);

        try {
            $updatedOrder = $this->orderService->updatePaymentStatus(
                $order->id,
                $request->input('payment_status'),
                $request->input('transaction_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated to "' . str_replace('_', ' ', ucfirst($request->payment_status)) . '".',
                'data'    => [
                    'payment_status' => $updatedOrder->payment_status,
                    'status'         => $updatedOrder->status,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status.',
            ], 500);
        }
    }

    /**
     * Process a refund for the order.
     */
    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $updatedOrder = $this->orderService->processRefund(
                $order->id,
                $request->input('note')
            );

            return response()->json([
                'success' => true,
                'message' => 'Order refunded successfully.',
                'data'    => [
                    'status'         => $updatedOrder->status,
                    'payment_status' => $updatedOrder->payment_status,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund.',
            ], 500);
        }
    }
}
