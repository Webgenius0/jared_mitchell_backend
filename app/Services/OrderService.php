<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    /**
     * Place an order from the current cart contents.
     *
     * @param int $userId
     * @param array $shipping Address data for shipping
     * @param array|null $billing Address data for billing (defaults to shipping)
     * @param array $options { notes, payment_method, tax_rate, shipping_cost }
     *
     * @return Order
     */
    public function placeFromCart(int $userId, array $shipping, ?array $billing = null, array $options = []): Order
    {
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();

        if ($cartItems->isEmpty()) {
            throw new RuntimeException('Your cart is empty.');
        }

        // Validate stock for all items before proceeding
        $this->validateStock($cartItems);

        $taxRate = $options['tax_rate'] ?? 0;
        $shippingCost = $options['shipping_cost'] ?? 0;
        $notes = $options['notes'] ?? null;

        return DB::transaction(function () use ($userId, $cartItems, $shipping, $billing, $taxRate, $shippingCost, $notes, $options) {
            // Calculate totals
            $subtotal = $cartItems->sum(function (Cart $item) {
                return $item->quantity * $item->product->display_price;
            });

            $tax = round($subtotal * ($taxRate / 100), 2);
            $discount = 0; // Could integrate a discount service later
            $total = round($subtotal + $tax + $shippingCost - $discount, 2);

            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'payment_method' => $options['payment_method'] ?? null,
                'notes' => $notes,
            ]);

            // Create order items from cart
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $itemSubtotal = $cartItem->quantity * $product->display_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'product_thumbnail' => $product->thumbnail,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $itemSubtotal,
                ]);

                // Decrement stock
                if ($product->track_stock) {
                    $product->decrement('stock', $cartItem->quantity);
                }
            }

            // Create shipping address
            OrderAddress::create(array_merge($shipping, ['order_id' => $order->id, 'type' => 'shipping']));

            // Create billing address (defaults to shipping if not provided)
            $billingData = $billing ?? $shipping;
            OrderAddress::create(array_merge($billingData, ['order_id' => $order->id, 'type' => 'billing']));

            // Clear the cart
            Cart::where('user_id', $userId)->delete();

            return $order->load(['items', 'shippingAddress', 'billingAddress']);
        });
    }

    /**
     * Place an order for a single product directly (buy now flow).
     */
    public function placeDirect(
        int $userId,
        int $productId,
        int $quantity,
        array $shipping,
        ?array $billing = null,
        array $options = []
    ): Order {
        $product = Product::active()->findOrFail($productId);

        if ($product->track_stock && $product->stock < $quantity) {
            throw new \RuntimeException(
                "Insufficient stock. Only {$product->stock} available."
            );
        }

        $taxRate = $options['tax_rate'] ?? 0;
        $shippingCost = $options['shipping_cost'] ?? 0;
        $notes = $options['notes'] ?? null;

        return DB::transaction(function () use (
            $userId, $product, $quantity, $shipping, $billing,
            $taxRate, $shippingCost, $notes, $options
        ) {
            $subtotal = $quantity * $product->display_price;
            $tax = round($subtotal * ($taxRate / 100), 2);
            $total = round($subtotal + $tax + $shippingCost, 2);

            $order = Order::create([
                'user_id'        => $userId,
                'order_number'   => $this->generateOrderNumber(),
                'subtotal'       => $subtotal,
                'tax'            => $tax,
                'shipping_cost'  => $shippingCost,
                'discount'       => 0,
                'total'          => $total,
                'status'         => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'payment_method' => $options['payment_method'] ?? null,
                'notes'          => $notes,
            ]);

            OrderItem::create([
                'order_id'         => $order->id,
                'product_id'       => $product->id,
                'product_name'     => $product->name,
                'product_price'    => $product->price,
                'sale_price'       => $product->sale_price,
                'product_thumbnail' => $product->thumbnail,
                'quantity'         => $quantity,
                'subtotal'         => $subtotal,
            ]);

            if ($product->track_stock) {
                $product->decrement('stock', $quantity);
            }

            OrderAddress::create(array_merge(
                $shipping,
                ['order_id' => $order->id, 'type' => 'shipping']
            ));

            $billingData = $billing ?? $shipping;
            OrderAddress::create(array_merge(
                $billingData,
                ['order_id' => $order->id, 'type' => 'billing']
            ));

            return $order->load(['items', 'shippingAddress', 'billingAddress']);
        });
    }

    /**
     * List orders for a user.
     */
    public function list(int $userId, array $filters = [])
    {
        $query = Order::with(['items', 'shippingAddress'])
            ->byUser($userId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        return $query->orderBy($sortBy, $sortOrder)
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Show a single order.
     */
    public function show(int $userId, int $orderId): Order
    {
        return Order::with(['items.product', 'shippingAddress', 'billingAddress'])
            ->byUser($userId)
            ->findOrFail($orderId);
    }

    /**
     * Cancel an order (only if pending).
     */
    public function cancel(int $userId, int $orderId, ?string $reason = null): Order
    {
        $order = Order::byUser($userId)->findOrFail($orderId);

        if ($order->status !== Order::STATUS_PENDING) {
            throw new \RuntimeException(
                'Only pending orders can be cancelled.'
            );
        }

        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status'       => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'notes'        => $reason
                    ? ($order->notes ? $order->notes . "\n\nCancel reason: " . $reason : 'Cancel reason: ' . $reason)
                    : $order->notes,
            ]);

            // Restore stock for each item
            foreach ($order->items as $item) {
                if ($item->product && $item->product->track_stock) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        });

        return $order->fresh(['items', 'shippingAddress', 'billingAddress']);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Update order status.
     */
    public function updateStatus(int $orderId, string $status): Order
    {
        $order = Order::findOrFail($orderId);

        $timestampField = match ($status) {
            Order::STATUS_CONFIRMED => 'confirmed_at',
            Order::STATUS_SHIPPED   => 'shipped_at',
            Order::STATUS_DELIVERED => 'delivered_at',
            Order::STATUS_CANCELLED => 'cancelled_at',
            Order::STATUS_REFUNDED  => 'refunded_at',
            default                 => null,
        };

        $updateData = ['status' => $status];
        if ($timestampField) {
            $updateData[$timestampField] = now();
        }

        // If cancelling, restore stock
        if ($status === Order::STATUS_CANCELLED && $order->status !== Order::STATUS_CANCELLED) {
            DB::transaction(function () use ($order, $updateData) {
                $order->update($updateData);

                foreach ($order->items as $item) {
                    if ($item->product && $item->product->track_stock) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            });

            return $order->fresh(['items', 'user.profile', 'shippingAddress', 'billingAddress']);
        }

        // If refunding, restore stock if not already cancelled/refunded
        if ($status === Order::STATUS_REFUNDED && !in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED])) {
            DB::transaction(function () use ($order, $updateData) {
                $order->update($updateData);

                foreach ($order->items as $item) {
                    if ($item->product && $item->product->track_stock) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            });

            return $order->fresh(['items', 'user.profile', 'shippingAddress', 'billingAddress']);
        }

        $order->update($updateData);

        return $order->fresh(['items', 'user.profile', 'shippingAddress', 'billingAddress']);
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(int $orderId, string $paymentStatus, ?string $transactionId = null): Order
    {
        $order = Order::findOrFail($orderId);

        $updateData = ['payment_status' => $paymentStatus];

        if ($transactionId) {
            $updateData['payment_transaction_id'] = $transactionId;
        }

        // Auto-confirm order when payment is marked as paid
        if ($paymentStatus === Order::PAYMENT_PAID && $order->status === Order::STATUS_PENDING) {
            $updateData['status'] = Order::STATUS_CONFIRMED;
            $updateData['confirmed_at'] = now();
        }

        $order->update($updateData);

        return $order->fresh(['items', 'user.profile', 'shippingAddress', 'billingAddress']);
    }

    /**
     * Process a refund for an order.
     */
    public function processRefund(int $orderId, ?string $note = null): Order
    {
        $order = Order::findOrFail($orderId);

        DB::transaction(function () use ($order, $note) {
            $order->update([
                'status'         => Order::STATUS_REFUNDED,
                'payment_status' => Order::PAYMENT_REFUNDED,
                'refunded_at'    => now(),
                'admin_notes'    => $note
                    ? ($order->admin_notes ? $order->admin_notes . "\n\nRefund note: " . $note : 'Refund note: ' . $note)
                    : $order->admin_notes,
            ]);

            // Restore stock if not already done
            if (!in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED])) {
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->track_stock) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }
        });

        return $order->fresh(['items', 'user.profile', 'shippingAddress', 'billingAddress']);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-';
        $timestamp = now()->format('YmdHis');

        do {
            $random = strtoupper(Str::random(6));
            $orderNumber = $prefix . $timestamp . '-' . $random;
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Validate stock availability for all cart items.
     *
     * @throws \RuntimeException
     */
    private function validateStock(Collection $cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active) {
                throw new \RuntimeException(
                    "Product '{$item->product->name}' is no longer available."
                );
            }

            if ($product->track_stock && $product->stock < $item->quantity) {
                throw new \RuntimeException(
                    "Insufficient stock for '{$product->name}'. " .
                    "Requested: {$item->quantity}, Available: {$product->stock}."
                );
            }
        }
    }
}
