<?php

namespace App\Helpers;

class EnvHelper
{
    public static function set($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {

            $content = file_get_contents($path);

            $pattern = "/^{$key}=.*/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}=\"{$value}\"", $content);
            } else {
                $content .= "\n{$key}=\"{$value}\"";
            }

            file_put_contents($path, $content);
        }
    }
}
