<?php

namespace App\Helpers;

class Helper
{
    /**
     * Generate a random alphanumeric string.
     */
    public static function randomAlphaNum($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generate slug for user profile
     */
    public static function generateSlug(string $name): string
    {
        return strtolower($name) . self::randomAlphaNum(8);
    }

    /**
     * Generate username for user profile
     */
    public static function generateUsername(string $name): string
    {
        return '@' . strtolower($name) . self::randomAlphaNum(8);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Status Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get CSS class for an order status badge.
     */
    public static function statusBadge(string $status): string
    {
        $classes = [
            'pending'   => 'bg-warning text-dark',
            'confirmed' => 'bg-info',
            'processing'=> 'bg-primary',
            'shipped'   => 'bg-secondary',
            'delivered' => 'bg-success',
            'cancelled' => 'bg-danger',
            'refunded'  => 'bg-dark',
        ];
        $class = $classes[$status] ?? 'bg-secondary';
        return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
    }

    /**
     * Get CSS class for a payment status badge.
     */
    public static function paymentBadge(string $paymentStatus): string
    {
        $classes = [
            'unpaid'             => 'bg-danger',
            'paid'               => 'bg-success',
            'refunded'           => 'bg-dark',
            'partially_refunded' => 'bg-warning text-dark',
        ];
        $class = $classes[$paymentStatus] ?? 'bg-secondary';
        return '<span class="badge ' . $class . '">' . str_replace('_', ' ', ucfirst($paymentStatus)) . '</span>';
    }

    /**
     * Get Bootstrap button color for an order status.
     */
    public static function statusColor(string $status): string
    {
        $colors = [
            'pending'   => 'warning',
            'confirmed' => 'info',
            'processing'=> 'primary',
            'shipped'   => 'secondary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'refunded'  => 'dark',
        ];
        return $colors[$status] ?? 'secondary';
    }

    /**
     * Get Bootstrap button color for a payment status.
     */
    public static function paymentColor(string $paymentStatus): string
    {
        $colors = [
            'unpaid'             => 'danger',
            'paid'               => 'success',
            'refunded'           => 'dark',
            'partially_refunded' => 'warning',
        ];
        return $colors[$paymentStatus] ?? 'secondary';
    }
}
