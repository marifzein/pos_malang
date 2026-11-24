<?php

namespace App\Helpers;

class DeviceHelper
{
    public static function isMobile(): bool
    {
        $userAgent = request()->header('User-Agent', '');
        return (bool) preg_match(
            '/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet)/i',
            $userAgent
        );
    }
}