<?php
declare(strict_types = 1);

require_once 'C:\dev\repos\Crypto\php\src\bcutil.php';
require_once 'C:\dev\repos\Crypto\php\src\curve25519.php';
// require_once 'D:\msvc\dev\repos\Crypto\php\src\bcutil.php';
// require_once 'D:\msvc\dev\repos\Crypto\php\src\curve25519.php';

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
    public static $DisableLongRunningTest = TRUE;
    
    public function testHexConversionException()
    {
        $this->expectException(Exception::class);
        BcUtil::dec2hex("-129", 1);
    }
    
    public function testHexConversionNeg() {

        $this->assertEquals(1, BcUtil::isHexStringNegative("f0"));
        $this->assertEquals(1, BcUtil::isHexStringNegative("ef"));
        $this->assertEquals(0, BcUtil::isHexStringNegative("7f"));
        for ($i = 0; $i < 200; $i++) {
            $v = 0 - $i;
            $v_str = "".$v;
            if ($v < -128) $this->expectException(Exception::class);
            BcUtil::dec2hex($v_str, 1);
        }
    }
 
    public function testHexConversion() {
        $h = BCUtil::dec2hex("1", 1);
        $this->assertEquals("01", $h);
        $h = BCUtil::dec2hex("255", 1);
        $this->assertEquals("ff", $h);
        $h = BCUtil::dec2hex("-1", 1);
        $this->assertEquals("ff", $h);
        $h = BCUtil::dec2hex("-2", 1);
        $this->assertEquals("fe", $h);
        $h = BCUtil::dec2hex("-129", 2);
        $this->assertEquals("ff7f", $h);
        
        $h = BCUtil::bcdechex("270789746419331941377545078918457577159845530448005805937855319561987125569");
        $this->assertEquals("9942f5edfb5cb4d58cdd9a573f118f2eaf04a9260e97f52f3adb0584d37141", $h);
        $h = BCUtil::dec2hex("270789746419331941377545078918457577159845530448005805937855319561987125569", 32);
        $this->assertEquals("009942f5edfb5cb4d58cdd9a573f118f2eaf04a9260e97f52f3adb0584d37141", $h);
        
        $this->expectException(Exception::class);
        $h = BCUtil::dec2hex("-200", 1);
    }

	public function testBits()
	{
		$v = BcUtil::shiftRightHex("04", 2, 1);
		$this->assertEquals("01", $v);
		$v = BcUtil::shiftRightHex("ff", 1, 1);
		$this->assertEquals("7f", $v);
		$v = BcUtil::shiftRightHex("00ff", 1, 2);
		$this->assertEquals("007f", $v);
		//echo "shifted ".$v."\n";
	}
	
	public function testLengthException()
	{
		$this->expectException(Exception::class);
		$bc = new BcUtil();
		BcUtil::lengthHex("0102034", 3);
	}
	
	public function testCanBitwiseBCMath() : void
    {
        // conversions
        $this->assertEquals("000000", BcUtil::lengthHex("", 3));
        //$this->assertEquals("fffffe", BcUtil::lengthHex("fe", 3));
        $this->assertEquals("0000fe", BcUtil::lengthHex("fe", 3));
        $this->assertEquals("0000fe", BcUtil::lengthHex("00fe", 3));
        $this->assertEquals("000001", BcUtil::dec2hex("1", 3));
        $this->assertEquals("000010", BcUtil::dec2hex("16", 3));
        $this->assertEquals("fffffe", BcUtil::dec2hex("-2", 3));
        $d = "31029842492115040904895560451863089656472772604678260265531221036453811406496";
        $this->assertEquals("449a44ba44226a50185afcc10a4c1462dd5e46824b15163b9d7c52f06be346a0", BcUtil::dec2hex($d, 32));
        $d = bcadd($d, "1");
        $this->assertEquals("449a44ba44226a50185afcc10a4c1462dd5e46824b15163b9d7c52f06be346a1", BcUtil::dec2hex($d, 32));
        $this->assertEquals(
            [0x00, 0x01, 0x02],
            BcUtil::hex2array("000102", 3));
        $a = BcUtil::hex2array("449a44ba44226a50185afcc10a4c1462dd5e46824b15163b9d7c52f06be346a0", 32);
        $this->assertEquals($a[0], 0x44);
        $this->assertEquals($a[31], 0xa0);
        $a = BcUtil::dec2array($d, 32);
        $this->assertEquals($a[0], 0x44);
        $this->assertEquals($a[31], 0xa1);
        
        // AND
        $this->assertEquals("01", BcUtil::andHex("ff", "01", 1));
    }
    
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
        echo " u decoded ".BcUtil::bcdechex($uDecoded)."\n";
        $uEncoded = $crv->encodeUCoordinate($uDecoded, 255);
        echo " u encoded ".$uEncoded."\n";
        $x = 0 - 2;
        echo dechex($x)."\n";
        echo " skalar decoded " .BcUtil::bcdechex($scalar1Decoded)."\n";
        $uDecodedHex = BcUtil::dec2hex($uDecoded, 32);
        $scalar1DecodedHex = BcUtil::dec2hex($scalar1Decoded, 32);
        $crv->out($scalar1Decoded, " skalar decoded ");
        $outU = $crv->x25519($scalar1Decoded, $uDecoded, 255);
        $crv->out($outU, "output U:");
    }

    /**
     * Test curve25519 according to RFC 7748, section 5.2. test vectors
     * Test single curve25519 examples
     */
    public function testVectors() {
    	$crv = new Curve25519();
    	// first set of vectors
    	$scalarString = "a546e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449ac4";
    	$uInString    = "e6db6867583030db3594c1a424b15f7c726624ec26b3353b10a903a6d0ab1c4c";
    	$uOutString   = "c3da55379de9c6908e94ea4df28d084f32eccf03491c71f754b4075577a28552";
    	
    	$scalar = $crv->decodeScalar25519($crv->toByteArray($scalarString));
    	$uIn = $crv->decodeUCoordinate($crv->toByteArray($uInString), 255);
    	$uOut = $crv->x25519($scalar, $uIn, 255);
    	$this->assertEquals($uOutString, $crv->asLittleEndianHexString($uOut));
    	
    	// second set of vectors
    	$scalarString = "4b66e9d4d1b4673c5ad22691957d6af5c11b6421e0ea01d42ca4169e7918ba0d";
    	$uInString    = "e5210f12786811d3f4b7959d0538ae2c31dbe7106fc03c3efc4cd549c715a493";
    	$uOutString   = "95cbde9476e8907d7aade45cb4b873f88b595a68799fa152e6f8f7647aac7957";
    	
    	$scalar = $crv->decodeScalar25519($crv->toByteArray($scalarString));
    	$uIn = $crv->decodeUCoordinate($crv->toByteArray($uInString), 255);
    	$uOut = $crv->x25519($scalar, $uIn, 255);
    	$this->assertEquals($uOutString, $crv->asLittleEndianHexString($uOut));
    }
    
    /*
     * String API tests
     */
    
    /**
     * Test curve25519 according to RFC 7748, section 5.2. test vectors
     * Test single curve25519 examples
     */
    public function testVectorsString() {
    	$crv = new Curve25519();
    	// first set of vectors
    	$scalarString = "a546e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449ac4";
    	$uInString    = "e6db6867583030db3594c1a424b15f7c726624ec26b3353b10a903a6d0ab1c4c";
    	$uOutString   = "c3da55379de9c6908e94ea4df28d084f32eccf03491c71f754b4075577a28552";
    	
    	$uOut = $crv->x25519Simple($scalarString,$uInString);
    	$this->assertEquals($uOutString, $uOut);
    	
    	// second set of vectors
    	$scalarString = "4b66e9d4d1b4673c5ad22691957d6af5c11b6421e0ea01d42ca4169e7918ba0d";
    	$uInString    = "e5210f12786811d3f4b7959d0538ae2c31dbe7106fc03c3efc4cd549c715a493";
    	$uOutString   = "95cbde9476e8907d7aade45cb4b873f88b595a68799fa152e6f8f7647aac7957";
    	
    	$uOut = $crv->x25519Simple($scalarString,$uInString);
    	$this->assertEquals($uOutString, $uOut);
    }
    
    /**
     * Test curve25519 according to RFC 7748, section 5.2. test vectors
     * Test calling curve25519 multiple times
     */
    public function testVectorsMultiString() {
    	$crv = new Curve25519();
    	$scalarString = "0900000000000000000000000000000000000000000000000000000000000000";
    	$uInString    = "0900000000000000000000000000000000000000000000000000000000000000";
    	$uOutString1    = "422c8e7a6227d7bca1350b3e2bb7279f7897b87bb6854b783c60e80311ae3079";
    	$uOutString1000 = "684cf59ba83309552800ef566f2f4d3c1c3887c49360e3875f2eb94d99532c51";
    	$uOutString1Mio = "7c3911e0ab2586fd864497297e575e6f3bc601c0883c30df5f4dd2d24f665424";
    	
    	// one iteration:
    	$uOut = $crv->x25519Simple($scalarString,$uInString);
    	$this->assertEquals($uOutString1, $uOut);

    	if (!self::$DisableLongRunningTest) {
        	// 1,000 iterations:
        	$scalarStringIntermediate = $scalarString;
        	$uInStringIntermediate = $uInString;
        	for ($i = 1; $i <= 1000; $i++) {
        		$uOut = $crv->x25519Simple($scalarStringIntermediate, $uInStringIntermediate);
        		//crv.out(uOut, (i) + ":");
        		$uInStringIntermediate = $scalarStringIntermediate;
        		$scalarStringIntermediate = $uOut;
        		if ($i % 25 === 0) {
        			fwrite(STDERR, "done ".$i." / 1000\n");
        		}
        	}
        	$this->assertEquals($uOutString1000, $scalarStringIntermediate);
    	}
    	
    	//if (disableLongRunningTest) return;
    	//		// 1,000,000 iterations:
    	//		scalar = crv.decodeScalar25519(crv.toByteArray(scalarString));
    	//		scalarStringIntermediate = scalarString;
    	//		uIn = crv.decodeUCoordinate(crv.toByteArray(uInString), 255);
    	//		for (int i = 1; i <= 1000000; i++) {
    	//			uOut = crv.x25519(scalar, uIn, 255);
    	//			if(i % 1000 == 0)
    		//			  crv.out(uOut, (i) + ":");
    		//			uIn = crv.decodeUCoordinate(crv.toByteArray(scalarStringIntermediate), 255);
    		//			scalarStringIntermediate = crv.asLittleEndianHexString(uOut);
    		//			scalar = crv.decodeScalar25519(crv.toByteArray(scalarStringIntermediate));
    		//		}
    	//assertEquals(uOutString1Mio, scalarStringIntermediate);
    }
}
