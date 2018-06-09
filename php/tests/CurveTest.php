<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
// call like this: php phpunit.phar --bootstrap src/autoload.php --testdox tests


final class CurveTest extends TestCase
{
    public function testCanConvert(): void
    {
    	  $crv = new Curve25519();
        $this->assertInstanceOf(
            Curve25519::class,
            $crv
        );
        
        echo $crv;
    }

//    public function testCannotBeCreatedFromInvalidEmailAddress(): void
//    {
//        $this->expectException(InvalidArgumentException::class);
//
//        Curve25519::fromString('invalid');
//    }
//
//    public function testCanBeUsedAsString(): void
//    {
//        $this->assertEquals(
//            'user@example.com',
//            Curve25519::fromString('user@example.com')
//        );
//    }
}
