<?php

namespace App\Helpers;

class AgentHelper
{
    /**
     * Parse User Agent string to identify device, browser, and OS.
     *
     * @param string|null $userAgent
     * @return array [deviceType, browser, os]
     */
    public static function detect(?string $userAgent): array
    {
        $os = 'Unknown OS';
        $browser = 'Unknown Browser';
        $deviceType = 'Desktop';

        if (empty($userAgent)) {
            return [$deviceType, $browser, $os];
        }

        // OS Detection
        $osArray = [
            '/windows nt 10/i'      =>  'Windows 10/11',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/macintosh|mac os x/i' =>  'macOS',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iOS',
            '/ipod/i'               =>  'iOS',
            '/ipad/i'               =>  'iOS',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        ];

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $os = $value;
                break;
            }
        }

        // Browser Detection
        $browserArray = [
            '/msie/i'       =>  'Internet Explorer',
            '/firefox/i'    =>  'Firefox',
            '/safari/i'     =>  'Safari',
            '/chrome/i'     =>  'Chrome',
            '/edge/i'       =>  'Edge',
            '/opera/i'      =>  'Opera',
            '/netscape/i'   =>  'Netscape',
            '/maxthon/i'    =>  'Maxthon',
            '/konqueror/i'  =>  'Konqueror',
            '/mobile/i'     =>  'Handheld Browser'
        ];

        foreach ($browserArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $browser = $value;
                break;
            }
        }

        // Safari/Chrome override check
        if (preg_match('/chrome/i', $userAgent) && preg_match('/safari/i', $userAgent)) {
            if (preg_match('/edge|edg/i', $userAgent)) {
                $browser = 'Edge';
            } else {
                $browser = 'Chrome';
            }
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        }

        // Bot Detection
        if (preg_match('/(googlebot|bingbot|yandexbot|slurp|crawler|spider|curl|wget)/i', $userAgent)) {
            $deviceType = 'Bot';
            return [$deviceType, $browser, $os];
        }

        // Device Type Detection
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobi))/i', $userAgent)) {
            $deviceType = 'Tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|iphone|ipod)/i', $userAgent)) {
            $deviceType = 'Mobile';
        }

        return [$deviceType, $browser, $os];
    }
}
