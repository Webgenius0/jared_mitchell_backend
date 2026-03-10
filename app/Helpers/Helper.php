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
}
