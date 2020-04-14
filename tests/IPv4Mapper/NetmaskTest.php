<?php
namespace Tests\IPv4Mapper;

use PHPUnit\Framework\TestCase;

use mracine\IPTools\Doctrine\IPv4Mapper\Netmask;
use mracine\IPTools\IPv4;

/**
 * @coversDefaultClass mracine\IPTools\Doctrine\IPv4Mapper\Netmask
 */
class NetmaskTest extends TestCase
{
    /**
     * @covers ::set
     * @covers ::get
     */
    public function testBehaviours()
    {
        $this->assertNull(Netmask::set());
        $this->assertNull(Netmask::get());

        $IPv4Netmask = IPv4\Netmask::fromString("255.255.255.0");
        $netmask = Netmask::set($IPv4Netmask);

        $this->assertInstanceOf(Netmask::class, $netmask);
        $this->assertSame($IPv4Netmask, Netmask::get($netmask));
    }

    /**
     * Doctrine does not call getters or setters, it retreive the object internals via reflexion to store them in database
     * Check the value wich will be stored
     */
    public function testInternals()
    {
        $netmask = Netmask::set(IPv4\Netmask::fromString("255.255.0.0"));
        $this->assertAttributeEquals(16, 'netmask', $netmask);
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
        $netmask = new Netmask();
        $this->assertNull(Netmask::get($netmask));
        // And subsquent calls too
        $this->assertNull(Netmask::get($netmask));

        // Hydrate the object has Doctrine will
        $reflexionClass = new \ReflectionClass(Netmask::class);
        $reflexionProperty = $reflexionClass->getProperty('netmask');
        $reflexionProperty->setAccessible('true');
        $reflexionProperty->setValue($netmask, 0);

        $IPv4Netmask = Netmask::get($netmask);
        $this->assertNotNull($IPv4Netmask);
        $this->assertEquals(0, $IPv4Netmask->int());
    }
    // Tester avec des valeurs > 32bits => Exception
    // Tester avec différents adresses borders : 0-255.255.255.255
}