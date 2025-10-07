<?php
namespace Tests\IPv4Mapper;

use PHPUnit\Framework\TestCase;

use mracine\IPTools\Doctrine\IPv4Mapper\Subnet;
use mracine\IPTools\IPv4;

/**
 * @coversDefaultClass mracine\IPTools\Doctrine\IPv4Mapper\Subnet
 */
class SubnetTest extends TestCase
{
    /**
     * @covers ::set
     * @covers ::get
     */
    public function testBehaviours()
    {
        $this->assertNull(Subnet::set());
        $this->assertNull(Subnet::get());

        $IPv4Subnet = IPv4\Subnet::fromCidr(IPv4\Address::fromString("10.0.0.0"), 24);
        $subnet = Subnet::set($IPv4Subnet);

        $this->assertInstanceOf(Subnet::class, $subnet);
        $this->assertSame($IPv4Subnet, Subnet::get($subnet));
    }

    /**
     * Doctrine does not call getters or setters, it retreive the object internals via reflexion to store them in database
     * Check the value wich will be stored
     */
    public function testInternals()
    {
        $IPv4Subnet = IPv4\Subnet::fromCidr(IPv4\Address::fromString("10.0.0.0"), 24);

        $subnet = Subnet::set($IPv4Subnet);

        $reflexionClass = new \ReflectionClass(Subnet::class);
        $reflexionPropertyAddress = $reflexionClass->getProperty('address');
        $reflexionPropertyNetmask = $reflexionClass->getProperty('netmask');
        $reflexionPropertyAddress->setAccessible(true);
        $reflexionPropertyNetmask->setAccessible(true);

        $this->assertEquals(0x0a000000, $reflexionPropertyAddress->getValue($subnet));
        $this->assertEquals(24, $reflexionPropertyNetmask->getValue($subnet));
    }
    
    /**
     * Doctrine does not call getters or setters, it hydrate the object via reflexion
     * The first call to the getter find a null IPv4 cached object wich creates it
     * 
     * @covers ::get
     */
    public function testInitCache()
    {
        // If database contains null, the get method must return anull
        $subnet = new Subnet();
        $this->assertNull(Subnet::get($subnet));
        // And subsquent calls too
        $this->assertNull(Subnet::get($subnet));

        // Hydrate the object has Doctrine will
        $reflexionClass = new \ReflectionClass(Subnet::class);
        $reflexionPropertyAddress = $reflexionClass->getProperty('address');
        $reflexionPropertyNetmask = $reflexionClass->getProperty('netmask');
        $reflexionPropertyAddress->setAccessible(true);
        $reflexionPropertyNetmask->setAccessible(true);
        $reflexionPropertyAddress->setValue($subnet, 0x0a000000);
        $reflexionPropertyNetmask->setValue($subnet, 24);

        // Retreive it via getter an ensure the final object has been correctly constructed
        $IPv4Subnet = Subnet::get($subnet);
        $this->assertNotNull($IPv4Subnet);
        $this->assertEquals(0x0a000000, $IPv4Subnet->getNetworkAddress()->asInteger());
        $this->assertEquals(24, $IPv4Subnet->getNetmaskAddress()->asCidr());
    }
    // Tester avec des valeurs > 32bits => Exception
    // Tester avec différents adresses borders : 0-255.255.255.255
}