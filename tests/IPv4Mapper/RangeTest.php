<?php
namespace Tests\IPv4Mapper;

use PHPUnit\Framework\TestCase;

use mracine\IPTools\Doctrine\IPv4Mapper\Range;
use mracine\IPTools\IPv4;

/**
 * @coversDefaultClass mracine\IPTools\Doctrine\IPv4Mapper\Range
 */
class RangeTest extends TestCase
{
    /**
     * @covers ::set
     * @covers ::get
     */
    public function testBehaviours()
    {
        $this->assertNull(Range::set());
        $this->assertNull(Range::get());

        $IPv4Range = new IPv4\Range(IPv4\Address::fromString("10.0.0.2"), IPv4\Address::fromString("10.0.0.3"));
        $range = Range::set($IPv4Range);

        $this->assertInstanceOf(Range::class, $range);
        $this->assertSame($IPv4Range, Range::get($range));
    }

    /**
     * Doctrine does not call getters or setters, it retreive the object internals via reflexion to store them in database
     * Check the value wich will be stored
     */
    public function testInternals()
    {
        $IPv4Range = new IPv4\Range(IPv4\Address::fromString("10.0.0.2"), IPv4\Address::fromString("10.0.0.3"));

        $range = Range::set($IPv4Range);
        $this->assertAttributeEquals(0x0a000002, 'address', $range);
        $this->assertAttributeEquals(2, 'count', $range);
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
        $range = new Range();
        $this->assertNull(Range::get($range));
        // And subsquent calls too
        $this->assertNull(Range::get($range));

        // Hydrate the object has Doctrine will
        $reflexionClass = new \ReflectionClass(Range::class);
        $reflexionPropertyAddress = $reflexionClass->getProperty('address');
        $reflexionPropertyCount = $reflexionClass->getProperty('count');
        $reflexionPropertyAddress->setAccessible('true');
        $reflexionPropertyCount->setAccessible('true');
        $reflexionPropertyAddress->setValue($range, 0x0a000002);
        $reflexionPropertyCount->setValue($range, 2);

        // Retreive it via getter an ensure the final object has been correctly constructed
        $IPv4Range = Range::get($range);
        $this->assertNotNull($IPv4Range);
        $this->assertEquals(0x0a000002, $IPv4Range->getLowerBound()->int());
        $this->assertEquals(2, count($IPv4Range));
    }
    // Tester avec des valeurs > 32bits => Exception
    // Tester avec différents adresses borders : 0-255.255.255.255
}