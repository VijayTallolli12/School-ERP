<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandingController extends ApiBaseController
{
    private const DEFAULT_LOGO_PATH = '/images/school-logo.png';

    private const DEFAULT_FAVICON_PATH = '/favicon.ico';

    public function show(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);

        if (! $schoolId) {
            return $this->success($this->defaultBranding(), 'Default branding returned.');
        }

        $school = School::query()->find($schoolId);

        if (! $school) {
            return $this->success($this->defaultBranding(), 'School not found. Default branding returned.');
        }

        app(SchoolContext::class)->set($schoolId);

        return $this->success($this->buildBranding($school), 'Branding retrieved.');
    }

    private function resolveSchoolId(Request $request): ?int
    {
        $schoolId = (int) $request->header('X-School-Id', $request->input('school_id', 0));

        if ($schoolId <= 0) {
            return null;
        }

        return $schoolId;
    }

    private function buildBranding(School $school): array
    {
        $settings = $school->settings ?? [];

        return [
            'school_name'     => $school->name ?: config('app.name', 'School ERP'),
            'school_logo'     => $this->schoolLogo($school),
            'favicon'         => $this->favicon($school),
            'primary_color'   => data_get($settings, 'brand.primary_color', '#2563eb'),
            'secondary_color' => data_get($settings, 'brand.secondary_color', '#64748b'),
            'school_website'  => data_get($settings, 'school.website', ''),
            'school_address'  => $school->address ?? '',
            'school_phone'    => $school->phone ?? '',
            'app_name'        => config('app.name', 'School ERP'),
        ];
    }

    private function defaultBranding(): array
    {
        return [
            'school_name'     => config('app.name', 'School ERP'),
            'school_logo'     => $this->absoluteAssetUrl(self::DEFAULT_LOGO_PATH),
            'favicon'         => $this->absoluteAssetUrl(self::DEFAULT_FAVICON_PATH),
            'primary_color'   => '#2563eb',
            'secondary_color' => '#64748b',
            'school_website'  => '',
            'school_address'  => '',
            'school_phone'    => '',
            'app_name'        => config('app.name', 'School ERP'),
        ];
    }

    private function schoolLogo(School $school): string
    {
        if ($school->logo_path) {
            return $this->storageUrl($school->logo_path);
        }

        return $this->absoluteAssetUrl(self::DEFAULT_LOGO_PATH);
    }

    private function favicon(School $school): string
    {
        $faviconPath = data_get($school->settings ?? [], 'school.favicon_path');

        if ($faviconPath) {
            return $this->storageUrl($faviconPath);
        }

        return $this->absoluteAssetUrl(self::DEFAULT_FAVICON_PATH);
    }

    private function storageUrl(string $path): string
    {
        return $this->absoluteAssetUrl('storage/'.ltrim($path, '/'));
    }

    private function absoluteAssetUrl(string $path): string
    {
        return rtrim($this->baseUrl(), '/').'/'.ltrim($path, '/');
    }

    /**
     * Resolve a base URL that mobile clients can reach. Never returns localhost.
     *
     * Prefers the current request scheme + host (so a phone hitting the dev
     * machine over the LAN gets http://<local-ip>:8000), then falls back to
     * APP_URL. Any localhost/127.0.0.1 host is swapped for the machine's local
     * network IP while keeping the scheme and port.
     */
    private function baseUrl(): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        $requestBase = $this->requestBase();

        $candidates = array_values(array_filter([$requestBase, $appUrl], static fn ($url) => $url !== ''));

        foreach ($candidates as $candidate) {
            if (! $this->isLocalHostname($this->hostnameOf($candidate))) {
                return $candidate;
            }
        }

        $base = $candidates[0] ?? ($appUrl ?: 'http://localhost');

        return $this->withLocalNetworkHost($base);
    }

    private function requestBase(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = app('request');

        if (! $request->getHttpHost()) {
            return null;
        }

        return $request->getScheme().'://'.$request->getHttpHost();
    }

    private function hostnameOf(string $url): string
    {
        return strtolower((string) parse_url($url, PHP_URL_HOST));
    }

    private function isLocalHostname(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true);
    }

    private function withLocalNetworkHost(string $url): string
    {
        $parts = parse_url($url);

        if (! isset($parts['host']) || ! $this->isLocalHostname(strtolower((string) $parts['host']))) {
            return $url;
        }

        $localIp = $this->localNetworkIp();

        if (! $localIp) {
            return $url;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$localIp.$port;
    }

    private function localNetworkIp(): ?string
    {
        $ip = $this->socketLocalIp();

        return $ip ?: $this->envLocalIp();
    }

    private function socketLocalIp(): ?string
    {
        if (! function_exists('socket_create')) {
            return null;
        }

        try {
            $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

            if ($socket === false) {
                return null;
            }

            @socket_connect($socket, '8.8.8.8', 53);
            @socket_getsockname($socket, $localIp);
            @socket_close($socket);

            if (is_string($localIp) && filter_var($localIp, FILTER_VALIDATE_IP) && ! $this->isLocalHostname($localIp)) {
                return $localIp;
            }
        } catch (\Throwable) {
            // Local IP resolution is best-effort; fall back gracefully.
        }

        return null;
    }

    private function envLocalIp(): ?string
    {
        $ip = env('LOCAL_IP');

        if (is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) && ! $this->isLocalHostname($ip)) {
            return $ip;
        }

        return null;
    }
}
