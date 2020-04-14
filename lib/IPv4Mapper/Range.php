<?php
namespace mracine\IPTools\Doctrine\IPv4Mapper;

use Doctrine\ORM\Mapping as ORM;

use mracine\IPTools\IPv4;

/**
 * @ORM\Embeddable
 */
class Range
{
    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    private $address;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    private $count;

    private $proxied = null;

    public static function get(self $me=null): ?IPv4\Range
    {
        if (null===$me || is_null($me->address)) {
            return null;
        }

        if (null===$me->proxied) {
            $me->proxied = IPv4\Range::fromCount(IPv4\Address::fromInteger($me->address), $me->count);
        }

        return $me->proxied;
    }

    public static function set(IPv4\Range $range=null)
    {
        if (null === $range) {
            return null;
        }

        $me = new self();
        $me->proxied = $range;
        $me->address = $range->getLowerBound()->asInteger();
        $me->count = count($range);
        return $me;
    }
}