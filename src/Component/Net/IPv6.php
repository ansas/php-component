<?php

namespace Ansas\Component\Net;

class IPv6
{
    public function __construct(protected string $ip)
    {
    }

    public static function bin2hex(string $bin): string
    {

        if (strlen($bin) < 128) {
            $bin = str_pad($bin, 128, '0', STR_PAD_LEFT);
        }

        $hex = [];
        foreach (str_split($bin, "16") as $v) {
            $hex[] = base_convert($v, 2, 16);
        }

        return implode(':', $hex);
    }

    public static function create(string $ip): static
    {
        return new static($ip);
    }

    public static function fromHostname(string $hostname): ?static
    {
        $record = dns_get_record($hostname, DNS_AAAA)[0]['ipv6'] ?? null;

        return $record ? new static($record) : null;
    }

    public static function hex2bin(string $hex): string
    {
        $bin = [];
        foreach (explode(':', static::create($hex)->getIpLong()) as $v) {
            $bin[] = str_pad(base_convert($v, 16, 2), 16, '0', STR_PAD_LEFT);
        }

        return implode($bin);
    }

    public function equals(IPv6 $compare): bool
    {
        return $this->getIpShort() === $compare->getIpShort();
    }

    public function equalsPrefix(IPv6 $compare, int $bits): bool
    {
        return $this->getPrefix($bits)->equals($compare->getPrefix($bits));
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getIpLong(): string
    {
        return implode(':', str_split(bin2hex(inet_pton($this->ip)), 4));
    }

    public function getIpShort(): string
    {
        return inet_ntop(inet_pton($this->ip));
    }

    public function getPrefix(int $bits): static
    {
        $binNetmask = str_repeat('1', $bits) . str_repeat('0', 128 - $bits);

        return new static(static::bin2hex(static::hex2bin($this->getIp()) & $binNetmask));
    }

    public function isValid(): bool
    {
        return !!filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }
}
