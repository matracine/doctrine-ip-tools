<?php
namespace mracine\IPTools\Doctrine\IPv4Mapper;

use Doctrine\ORM\Mapping as ORM;

use mracine\IPTools\IPv4;

/**
 * @ORM\Embeddable
 */
class Subnet
{
    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    private $address;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    private $netmask;

    private $objectCache = null;

    public static function get(self $me=null): ?IPv4\Subnet
    {
        if (null===$me || is_null($me->address)) {
            return null;
        }

        if (null===$me->objectCache) {
            $me->objectCache = IPv4\Subnet::fromCidr(IPv4\Address::fromInteger($me->address), $me->netmask);
        }

        return $me->objectCache;
    }

    public static function set(IPv4\Subnet $subnet=null)
    {
        if (null === $subnet) {
            return null;
        }

        $me = new self();
        $me->objectCache = $subnet;
        $me->address = $subnet->getNetworkAddress()->asInteger();
        $me->netmask = $subnet->getNetmaskAddress()->asCidr();
        return $me;
    }
}