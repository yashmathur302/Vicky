<?php

namespace App\Support;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

final class ClientInfo
{
    public static function extract(Request $request): array
    {
        $userAgent = (string) $request->userAgent();

        $agent = new Agent();

        $agent->setUserAgent($userAgent);
        $agent->setHttpHeaders($request->headers->all());

        $fallbackBrowser = $agent->browser() ?: 'Unknown Browser';
        $fallbackBrowserVersion = $fallbackBrowser !== 'Unknown Browser'
            ? $agent->version($fallbackBrowser)
            : null;

        $fallbackOs = $agent->platform() ?: 'Unknown OS';
        $fallbackOsVersion = $fallbackOs !== 'Unknown OS'
            ? $agent->version($fallbackOs)
            : null;

        $serverPlatform = self::cleanValue(
            $request->header('Sec-CH-UA-Platform')
        );

        $serverPlatformVersion = self::cleanValue(
            $request->header('Sec-CH-UA-Platform-Version')
        );

        $serverFullVersionList = self::parseFullVersionList(
            $request->header('Sec-CH-UA-Full-Version-List')
        );

        $clientPlatform = self::cleanValue(
            $request->input('client_platform')
        );

        $clientPlatformVersion = self::cleanValue(
            $request->input('client_platform_version')
        );

        $clientFullVersionList = self::parseFullVersionList(
            $request->input('client_full_version_list')
        );

        $browserName = $fallbackBrowser;
        $browserVersion = $fallbackBrowserVersion;

        $fullVersionList = !empty($serverFullVersionList)
            ? $serverFullVersionList
            : $clientFullVersionList;

        if (!empty($fullVersionList)) {
            $clientBrowser = self::getRealBrowser($fullVersionList);

            if ($clientBrowser['name']) {
                $browserName = $clientBrowser['name'];
                $browserVersion = $clientBrowser['version'];
            }
        }

        $exactBrowser = self::formatBrowser(
            $browserName,
            $browserVersion
        );

        $platform = $serverPlatform ?: $clientPlatform;

        $platformVersion = $serverPlatformVersion ?: $clientPlatformVersion;

        [$osName, $osVersion] = self::resolveOperatingSystem(
            $platform,
            $platformVersion,
            $fallbackOs,
            $fallbackOsVersion
        );

        $exactOs = self::formatOperatingSystem(
            $osName,
            $osVersion
        );

        return [
            'browser' => $exactBrowser,
            'operating_system' => $exactOs,
        ];
    }
    private static function parseFullVersionList(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return collect($decoded)
                ->filter(function ($item) {
                    return isset($item['brand'], $item['version']);
                })
                ->map(function ($item) {
                    return [
                        'brand' => trim((string) $item['brand']),
                        'version' => trim((string) $item['version']),
                    ];
                })
                ->values()
                ->all();
        }

        preg_match_all(
            '/"([^"]+)"\s*;\s*v="([^"]+)"/',
            $value,
            $matches,
            PREG_SET_ORDER
        );

        return collect($matches)
            ->map(function ($match) {
                return [
                    'brand' => trim($match[1]),
                    'version' => trim($match[2]),
                ];
            })
            ->values()
            ->all();
    }

    private static function getRealBrowser(array $brands): array
    {
        $chromiumFallback = null;

        foreach ($brands as $brand) {
            $name = trim($brand['brand'] ?? '');
            $version = trim($brand['version'] ?? '');

            if ($name === '' || $version === '') {
                continue;
            }

            /*
            | Ignore GREASE brand.
            */
            if (preg_match('/not[\s\W_]*a[\s\W_]*brand/i', $name)) {
                continue;
            }

            /*
            | Keep Chromium as fallback.
            */
            if (strcasecmp($name, 'Chromium') === 0) {
                $chromiumFallback = [
                    'name' => $name,
                    'version' => $version,
                ];

                continue;
            }

            return [
                'name' => $name,
                'version' => $version,
            ];
        }

        return $chromiumFallback ?? [
            'name' => null,
            'version' => null,
        ];
    }

    private static function resolveOperatingSystem(
        ?string $platform,
        ?string $platformVersion,
        string $fallbackOs,
        ?string $fallbackOsVersion
    ): array {

        if (!$platform) {
            return [
                $fallbackOs !== 'Unknown OS'
                    ? $fallbackOs
                    : 'Unknown OS',
                null,
            ];
        }

        $platform = trim($platform);
        $platformVersion = trim((string) $platformVersion);

        if (strcasecmp($platform, 'Windows') === 0) {

            $majorVersion = (int) explode('.', $platformVersion)[0];

            if ($majorVersion >= 13) {
                return [
                    'Windows 11',
                    null,
                ];
            }

            if ($majorVersion >= 1 && $majorVersion <= 10) {
                return [
                    'Windows 10',
                    null,
                ];
            }

            if (
                $fallbackOs !== 'Unknown OS' &&
                strcasecmp($fallbackOs, 'Windows') !== 0
            ) {
                return [
                    $fallbackOs,
                    null,
                ];
            }

            return [
                'Windows',
                null,
            ];
        }

        return [
            $platform,
            null,
        ];
    }

    // private static function resolveOperatingSystem(
    //     ?string $platform,
    //     ?string $platformVersion,
    //     string $fallbackOs,
    //     ?string $fallbackOsVersion
    // ): array {
    //     if (!$platform) {
    //         return [
    //             $fallbackOs,
    //             $fallbackOsVersion,
    //         ];
    //     }

    //     $platform = trim($platform);
    //     $platformVersion = trim((string) $platformVersion);

    //     if (strcasecmp($platform, 'Windows') === 0) {
    //         $majorVersion = (int) explode('.', $platformVersion)[0];

    //         if ($majorVersion >= 13) {
    //             return [
    //                 'Windows 11',
    //                 $platformVersion,
    //             ];
    //         }

    //         if ($majorVersion >= 1 && $majorVersion <= 10) {
    //             return [
    //                 'Windows 10',
    //                 $platformVersion,
    //             ];
    //         }

    //         return [
    //             $fallbackOs !== 'Unknown OS' ? $fallbackOs : 'Windows',
    //             $platformVersion ?: $fallbackOsVersion,
    //         ];
    //     }

    //     return [
    //         $platform,
    //         $platformVersion ?: $fallbackOsVersion,
    //     ];
    // }

    private static function formatBrowser(
        string $name,
        ?string $version = null
    ): string {
        $name = trim($name);

        return $name !== ''
            ? $name
            : 'Unknown Browser';
    }

    // private static function formatBrowser(
    //     string $name,
    //     ?string $version
    // ): string {
    //     $name = trim($name);
    //     $version = trim((string) $version);

    //     if ($name === '') {
    //         return 'Unknown Browser';
    //     }

    //     return $version
    //         ? $name . ' ' . $version
    //         : $name;
    // }

    private static function formatOperatingSystem(
        string $name,
        ?string $version = null
    ): string {
        $name = trim($name);

        return $name !== ''
            ? $name
            : 'Unknown OS';
    }

    // private static function formatOperatingSystem(
    //     string $name,
    //     ?string $version
    // ): string {
    //     $name = trim($name);
    //     $version = trim((string) $version);

    //     if ($name === '') {
    //         return 'Unknown OS';
    //     }

    //     return $version
    //         ? $name . ' ' . $version
    //         : $name;
    // }



    private static function cleanValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value, " \t\n\r\0\x0B\"");
    }
}