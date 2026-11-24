<?php

namespace App\Helpers;

class DeviceHelper
{
    public static function isMobile(): bool
    {
        // Jika belum login atau role-nya BUKAN kasir (Owner, Admin, SPV) -> paksa Desktop
        if (!auth()->check() || strtolower(auth()->user()->role) !== 'kasir') {
            return false;
        }
        
        $userAgent = request()->header('User-Agent', '');
        return (bool) preg_match(
            '/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet)/i',
            $userAgent
        );
    }
}