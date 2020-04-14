<?php
namespace mracine\IPTools\Doctrine\IPv4Mapper;

use Doctrine\ORM\Mapping as ORM;

use mracine\IPTools\IPv4;

/**
 * @ORM\Embeddable
 */
class Netmask
{
    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    private $netmask;

    private $objectCache = null;

    public static function get(self $me=null): ?IPv4\Netmask
    {
        if (null===$me || is_null($me->netmask)) {
            return null;
        }

        if (null===$me->objectCache) {
            $me->objectCache = IPv4\Netmask::fromCidr($me->netmask);
        }

        return $me->objectCache;
    }

    public static function set(IPv4\Netmask $netmask=null)
    {
        if (null === $netmask) {
            return null;
        }

        $me = new self();
        $me->objectCache = $netmask;
        $me->netmask     = $netmask->asCidr();
        return $me;
    }
}