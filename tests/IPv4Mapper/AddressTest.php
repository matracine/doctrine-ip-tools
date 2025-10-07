<?php
namespace Tests\IPv4Mapper;

use PHPUnit\Framework\TestCase;

use mracine\IPTools\Doctrine\IPv4Mapper\Address;
use mracine\IPTools\IPv4;

/**
 * @coversDefaultClass mracine\IPTools\Doctrine\IPv4Mapper\Address
 */
class AddressTest extends TestCase
{
    /**
     * @covers ::set
     * @covers ::get
     */
    public function testBehaviours()
    {
        $this->assertNull(Address::set());
        $this->assertNull(Address::get());

        $IPv4Address = IPv4\Address::fromString("10.0.0.2");
        $address = Address::set($IPv4Address);

        $this->assertInstanceOf(Address::class, $address);
        $this->assertSame($IPv4Address, Address::get($address));
    }

    /**
     * Doctrine does not call getters or setters, it retreive the object internals via reflexion to store them in database
     * Check the value wich will be stored
     */
    public function testInternals()
    {
        $address = Address::set(IPv4\Address::fromString("10.0.0.2"));

        $reflexionClass = new \ReflectionClass(Address::class);
        $reflexionProperty = $reflexionClass->getProperty('address');
        $reflexionProperty->setAccessible(true);
        $actualValue = $reflexionProperty->getValue($address);

        $this->assertEquals(0x0a000002, $actualValue);
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
        $address = new Address();
        $this->assertNull(Address::get($address));
        // And sibsquent calls too
        $this->assertNull(Address::get($address));

        // Hydrate the object has Doctrine will
        $reflexionClass = new \ReflectionClass(Address::class);
        $reflexionProperty = $reflexionClass->getProperty('address');
        $reflexionProperty->setAccessible(true);
        $reflexionProperty->setValue($address, 0);

        $IPv4Address = Address::get($address);
        $this->assertNotNull($IPv4Address);
        $this->assertEquals(0, $IPv4Address->asInteger());
    }
    // Tester avec des valeurs > 32bits => Exception
    // Tester avec différents adresses borders : 0-255.255.255.255
}