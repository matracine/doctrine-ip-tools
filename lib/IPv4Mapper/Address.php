<?php
namespace mracine\IPTools\Doctrine\IPv4Mapper;

use Doctrine\ORM\Mapping as ORM;

use mracine\IPTools\IPv4;

/**
 * @ORM\Embeddable
 */
class Address
{
    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    private $address;

    private $objectCache = null;

    public static function get(self $me=null): ?IPv4\Address
    {
        if (null===$me || is_null($me->address)) {
            return null;
        }

        if (null===$me->objectCache) {
            $me->objectCache = IPv4\Address::fromInteger($me->address);
        }

        return $me->objectCache;
    }

    public static function set(IPv4\Address $address=null)
    {
        if (null === $address) {
            return null;
        }

        $me = new self();
        $me->objectCache = $address;
        $me->address = $address->asInteger();
        return $me;
    }
}