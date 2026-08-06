<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\StripeService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected OrderService  $orderService,
        protected StripeService $stripeService
    ) {}

    /**
     * POST /api/v1/orders/place
     *
     * Place an order from the current cart contents.
     */
    public function place(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Shipping address (required)
            'shipping.name' => 'required|string|max:255',
            'shipping.phone' => 'nullable|string|max:20',
            'shipping.email' => 'nullable|email|max:255',
            'shipping.address_line1' => 'required|string|max:255',
            'shipping.address_line2' => 'nullable|string|max:255',
            'shipping.city' => 'required|string|max:255',
            'shipping.state' => 'nullable|string|max:255',
            'shipping.zip' => 'nullable|string|max:20',
            'shipping.country' => 'nullable|string|max:2',

            // Billing address (optional — if billing group is provided, all required fields become required)
            'billing' => 'nullable|array',
            'billing.name' => 'required_with:billing|string|max:255',
            'billing.phone' => 'nullable|string|max:20',
            'billing.email' => 'nullable|email|max:255',
            'billing.address_line1' => 'required_with:billing|string|max:255',
            'billing.address_line2' => 'nullable|string|max:255',
            'billing.city' => 'required_with:billing|string|max:255',
            'billing.state' => 'nullable|string|max:255',
            'billing.zip' => 'nullable|string|max:20',
            'billing.country' => 'nullable|string|max:2',

            // Payment & notes
            'payment_method' => 'nullable|string|in:cod,card|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $userId = auth('api')->id();
            $paymentMethod = $request->input('payment_method', 'cod');

            $billing = $request->input('billing')
                ? $request->input('billing')
                : $request->input('shipping');

            $order = $this->orderService->placeFromCart(
                $userId,
                $request->input('shipping'),
                $billing,
                [
                    'notes' => $request->input('notes'),
                    'payment_method' => $paymentMethod,
                ]
            );

            // If card payment, create a Stripe Checkout session
            if ($paymentMethod === 'card') {
                return $this->handleStripeCheckout($order);
            }

            // COD — order is placed immediately
            return $this->success(
                'Order placed successfully. Payment on delivery.',
                $this->formatOrder($order, true),
                201
            );
        } catch (RuntimeException $e) {
            return $this->error(null, $e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to place order. Please try again.');
        }
    }

    /**
     * POST /api/v1/orders/buy-now
     *
     * Buy a single product directly.
     */
    public function buyNow(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:100',

            // Shipping address
            'shipping.name' => 'required|string|max:255',
            'shipping.phone' => 'nullable|string|max:20',
            'shipping.email' => 'nullable|email|max:255',
            'shipping.address_line1' => 'required|string|max:255',
            'shipping.address_line2' => 'nullable|string|max:255',
            'shipping.city' => 'required|string|max:255',
            'shipping.state' => 'nullable|string|max:255',
            'shipping.zip' => 'nullable|string|max:20',
            'shipping.country' => 'nullable|string|max:255',

            // Billing address (optional — if billing group is provided, all required fields become required)
            'billing' => 'nullable|array',
            'billing.name' => 'required_with:billing|string|max:255',
            'billing.phone' => 'nullable|string|max:20',
            'billing.email' => 'nullable|email|max:255',
            'billing.address_line1' => 'required_with:billing|string|max:255',
            'billing.address_line2' => 'nullable|string|max:255',
            'billing.city' => 'required_with:billing|string|max:255',
            'billing.state' => 'nullable|string|max:255',
            'billing.zip' => 'nullable|string|max:20',
            'billing.country' => 'nullable|string|max:255',

            'payment_method' => 'nullable|string|in:cod,card|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $userId = auth('api')->id();
            $paymentMethod = $request->input('payment_method', 'cod');

            $billing = $request->input('billing')
                ? $request->input('billing')
                : $request->input('shipping');

            $order = $this->orderService->placeDirect(
                $userId,
                (int) $request->input('product_id'),
                (int) $request->input('quantity'),
                $request->input('shipping'),
                $billing,
                [
                    'notes'          => $request->input('notes'),
                    'payment_method' => $paymentMethod,
                ]
            );

            // If card payment, create a Stripe Checkout session
            if ($paymentMethod === 'card') {
                return $this->handleStripeCheckout($order);
            }

            // COD — order is placed immediately
            return $this->success(
                'Order placed successfully. Payment on delivery.',
                $this->formatOrder($order, true),
                201
            );
        } catch (RuntimeException $e) {
            return $this->error(null, $e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('Product not found.');
        } catch (Exception $e) {
            return $this->error(null, 'Failed to place order. Please try again.');
        }
    }

    /**
     * Create a Stripe Checkout session for a card-payment order.
     *
     * @param \App\Models\Order $order
     * @return JsonResponse
     */
    private function handleStripeCheckout(\App\Models\Order $order): JsonResponse
    {
        try {
            $lineItems = $order->items->map(function ($item) {
                return [
                    'name'     => $item->product_name,
                    'quantity' => $item->quantity,
                    'price'    => $item->sale_price ?? $item->product_price,
                ];
            })->toArray();

            $checkoutSession = $this->stripeService->createCheckoutSession([
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'amount'         => (float) $order->total,
                'customer_email' => auth('api')->user()?->email,
                'line_items'     => $lineItems,
                'metadata'       => [
                    'order_id'     => (string) $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);

            // Mark the order as card-payment pending
            $this->orderService->markAsStripePending($order->id);

            return $this->success(
                'Redirecting to payment...',
                [
                    'order'         => $this->formatOrder($order, true),
                    'checkout_url'  => $checkoutSession->url,
                    'session_id'    => $checkoutSession->id,
                ],
                201
            );
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return $this->error(null, 'Payment service error: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to initiate payment. Please try again.');
        }
    }

    /**
     * GET /api/v1/orders
     *
     * List orders for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = auth('api')->id();

            $filters = [
                'status' => $request->input('status'),
                'payment_status' => $request->input('payment_status'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
                'per_page' => $request->input('per_page', 15),
            ];

            $orders = $this->orderService->list($userId, $filters);

            $orders->getCollection()->transform(function ($order) {
                return $this->formatOrder($order);
            });

            return $this->success('Orders retrieved successfully.', [
                'data' => $orders->items(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to retrieve orders. Please try again.');
        }
    }

    /**
     * GET /api/v1/orders/{order}
     *
     * Show a single order.
     */
    public function show(int $order): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $orderModel = $this->orderService->show($userId, $order);

            return $this->success(
                'Order retrieved successfully.',
                $this->formatOrder($orderModel, true)
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFound('Order not found.');
        } catch (Exception $e) {
            return $this->error(null, 'Failed to retrieve order. Please try again.');
        }
    }

    /**
     * POST /api/v1/orders/{order}/cancel
     *
     * Cancel an order.
     */
    public function cancel(Request $request, int $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $userId = auth('api')->id();
            $orderModel = $this->orderService->cancel(
                $userId,
                $order,
                $request->input('reason')
            );

            return $this->success(
                'Order cancelled successfully.',
                $this->formatOrder($orderModel, true)
            );
        } catch (RuntimeException $e) {
            return $this->error(null, $e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('Order not found.');
        } catch (Exception $e) {
            return $this->error(null, 'Failed to cancel order. Please try again.');
        }
    }

    /**
     * Format an order for API response.
     */
    private function formatOrder($order, bool $includeTimestamps = false): array
    {
        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'subtotal' => (float) $order->subtotal,
            'tax' => (float) $order->tax,
            'shipping_cost' => (float) $order->shipping_cost,
            'discount' => (float) $order->discount,
            'total' => (float) $order->total,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product_name,
                    'price' => (float) $item->product_price,
                    'sale_price' => $item->sale_price ? (float) $item->sale_price : null,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                    'thumbnail' => $item->product_thumbnail ? url('/' . $item->product_thumbnail) : null,
                ];
            }),
            'shipping_address' => $order->shippingAddress ? [
                'name' => $order->shippingAddress->name,
                'phone' => $order->shippingAddress->phone,
                'email' => $order->shippingAddress->email,
                'address_line1' => $order->shippingAddress->address_line1,
                'address_line2' => $order->shippingAddress->address_line2,
                'city' => $order->shippingAddress->city,
                'state' => $order->shippingAddress->state,
                'zip' => $order->shippingAddress->zip,
                'country' => $order->shippingAddress->country,
                'full_address' => $order->shippingAddress->full_address,
            ] : null,
            'created_at' => $order->created_at->toISOString(),
        ];

        if ($includeTimestamps) {
            $data['billing_address'] = $order->billingAddress ? [
                'name' => $order->billingAddress->name,
                'phone' => $order->billingAddress->phone,
                'email' => $order->billingAddress->email,
                'address_line1' => $order->billingAddress->address_line1,
                'address_line2' => $order->billingAddress->address_line2,
                'city' => $order->billingAddress->city,
                'state' => $order->billingAddress->state,
                'zip' => $order->billingAddress->zip,
                'country' => $order->billingAddress->country,
                'full_address' => $order->billingAddress->full_address,
            ] : null;
            $data['notes'] = $order->notes;
            $data['confirmed_at'] = $order->confirmed_at?->toISOString();
            $data['shipped_at'] = $order->shipped_at?->toISOString();
            $data['delivered_at'] = $order->delivered_at?->toISOString();
            $data['cancelled_at'] = $order->cancelled_at?->toISOString();
            $data['updated_at'] = $order->updated_at->toISOString();
        }

        return $data;
    }
}
