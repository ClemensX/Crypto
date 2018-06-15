<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * Get the type, resource name, or class name of a variable.
 *
 * Returns the type (name if an object or resource) of the PHP variable $var.
 *
 * @link   http://php.net/manual/en/function.gettype.php#104224
 * @param  mixed $var The variable being type checked.
 * @return string
 */
function get_var_type($var)
{
    if (is_object($var)) {
        return get_class($var);
    }
    
    if (is_resource($var)) {
        return get_resource_type($var);
    }
    
    return gettype($var);
}

// call like this: php phpunit.phar --bootstrap src/autoload.php --testdox tests
final class CurveTest extends TestCase
{

    public function testCanConvert(): void
    {
        $crv = new Curve25519();
        $this->assertInstanceOf(Curve25519::class, $crv);
        
        $scalar1 = "a546e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449ac4";
        $ar1 = $crv->toByteArray($scalar1);
        $this->assertEquals(32, count($ar1));
        $this->assertEquals(0xa5, $ar1[0]);
        $this->assertEquals(0xc4, $ar1[31]);
        try {
            $ar1 = $crv->toByteArray("");
            $this->assertEquals(32, count($ar1));
            //var_dump($ar1);
        } catch (Exception $e) {
            //echo $e;
        }
        $ar2 = $crv->toByteArrayLittleEndian($scalar1);
        $this->assertEquals(0xc4, $ar2[0]);
        $this->assertEquals(0xa5, $ar2[31]);

        $bigScalarAsNumber = "31029842492115040904895560451863089656472772604678260265531221036453811406496";
        $big1 = $crv->decodeLittleEndian($ar2, 255);
        $this->assertNotEquals($bigScalarAsNumber,$big1);

		$scalarCorrected = "a046e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449a44";
        $big1 = $crv->decodeLittleEndian($crv->toByteArray($scalarCorrected), 255);
        $this->assertEquals($bigScalarAsNumber,$big1);

        $scalar1Decoded = $crv->decodeScalar25519($crv->toByteArray($scalar1));
        $this->assertEquals($bigScalarAsNumber, $scalar1Decoded);

        // U Coordinates
        $ucoord1 = "e6db6867583030db3594c1a424b15f7c726624ec26b3353b10a903a6d0ab1c4c";
        $biguAsNumber = "34426434033919594451155107781188821651316167215306631574996226621102155684838";
        //System.out.println(biguAsNumber.toString(16));
        $bigu = $crv->decodeLittleEndian($crv->toByteArray($ucoord1), 255);
        $this->assertEquals($biguAsNumber, $bigu);
        $uDecoded = $crv->decodeUCoordinate($crv->toByteArray($ucoord1), 255);
        $this->assertEquals($biguAsNumber, $uDecoded);
        echo " u decoded ".Curve25519::bcdechex($uDecoded)."\n";
        $uEncoded = $crv->encodeUCoordinate($uDecoded, 255);
        echo " u encoded ".$uEncoded."\n";
        $x = 0 - 2;
        echo dechex($x)."\n";
        echo " skalar decoded " .Curve25519::bcdechex($scalar1Decoded)."\n";
        $crv->out($scalar1Decoded, " skalar decoded ");
        $outU = $crv->x25519($scalar1Decoded, $uDecoded, 255);
        $crv->out($outU, "output U:");
    }
    
    // public function testCannotBeCreatedFromInvalidEmailAddress(): void
    // {
    // $this->expectException(InvalidArgumentException::class);
    //
    // Curve25519::fromString('invalid');
    // }
    //
    // public function testCanBeUsedAsString(): void
    // {
    // $this->assertEquals(
    // 'user@example.com',
    // Curve25519::fromString('user@example.com')
    // );
    // }
}
