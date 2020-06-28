<?php

declare(strict_types=1);

namespace App\Classes;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class CloudFlare
{
    /**
     * List of IP's used by CloudFlare.
     * @const array
     */
    protected const IPS = [
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '104.16.0.0/12',
        '108.162.192.0/18',
        '131.0.72.0/22',
        '141.101.64.0/18',
        '162.158.0.0/15',
        '172.64.0.0/13',
        '173.245.48.0/20',
        '188.114.96.0/20',
        '190.93.240.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '2400:cb00::/32',
        '2405:8100::/32',
        '2405:b500::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2c0f:f248::/32',
        '2a06:98c0::/29'
    ];

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Checks if current request is coming from CloudFlare servers.
     *
     * @return bool
     */
    public function isTrustedRequest(): bool
    {
        return IpUtils::checkIp($this->request->ip(), static::IPS);
    }

    /**
     * Executes a callback on a trusted request.
     *
     * @param  Closure $callback
     *
     * @return mixed
     */
    public function onTrustedRequest(Closure $callback)
    {
        if ($this->isTrustedRequest()) {
            return $callback();
        }
    }

    /**
     * Determines "the real" IP address from the current request.
     *
     * @return string
     */
    public function ip(): string
    {
        return $this->onTrustedRequest(function () {
            return filter_var($this->request->header('CF_CONNECTING_IP'), FILTER_VALIDATE_IP);
        }) ?: $this->request->ip();
    }

    /**
     * Determines country from the current request.
     *
     * @return string
     */
    public function country(): string
    {
        return $this->onTrustedRequest(function () {
            return $this->request->header('CF_IPCOUNTRY');
        }) ?: '';
    }
}
