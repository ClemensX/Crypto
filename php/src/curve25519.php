<?php
declare(strict_types = 1);

final class Curve25519
{

    public static $p;

    public static $p_minus2;

    public static $a24;

    public function __construct()
    {
        // $this->ensureIsValidEmail($email);
        // calculate p as 2 ^ 255 - 19
        self::$p = "2";
        self::$p = bcpow(self::$p, "255");
        self::$p = bcsub(self::$p, "19");
        self::$p_minus2 = bcsub(self::$p, "2");
        self::$a24 = "121665";
    }

    public function __toString(): string
    {
        return "p=" . self::$p;
    }
    
    /**
     * Convert Hex String FA01EE... to int array 0xfa,0x01,0xee
     * Hex byte arrays are strings in PHP.
     * @return string
     */
    public function toByteArray(string $hexString): array
    {
        // make string 64 chars by prepending '0'
        while(strlen($hexString) < 64) {
            $hexString = "0".$hexString;
        }
        
        // split hex string up to bytes (2 hex digits -> one byte)
        $a = str_split(pack("H*", $hexString));
        foreach ($a as &$v) {
            // convert each string byte to integer
            $unp = unpack("H2b", $v);
            //echo "\n".$unp['b'];
            $hexString = $unp['b'];
            $i = hexdec($hexString);
            //echo "\n".$hexString." ".$i;
            //replace string with integer in array:
            $v = $i;
        }
        return $a;
    }

    public function toByteArrayLittleEndian(string $hexString): array
    {
        return array_reverse($this->toByteArray($hexString));
    }
    
    /**
     *
     * python code:def decodeLittleEndian(b, bits):
     *  return sum([b[i] << 8*i for i in range((bits+7)/8)])
     *
     * @param b
     * @param bits
     * @return
     */
    public function decodeLittleEndian( array $b, int $bits): string {
    	if(count($b) != 32) {
    		throw new Exception(' arrays for curve have to be 32 bytes: '.count($b));
    	}
    	$big = "0";
    	$range = intdiv(($bits+7), 8);  // yields 32 bytes for curve25519
    	//echo "Range ".$range."\n";
    	$factor = "1";//BigInteger.ONE;
    	for($i = 0; $i < $range; $i++) {
    		$v = ($b[$i]) & 0xff;
    		$byteVal = bcadd("0", strval($v));//BigInteger.valueOf(v);
    		$byteVal = bcmul($byteVal, $factor);
    		$big = bcadd($big, $byteVal);
    		$factor = bcmul($factor, "256");
    	}
    	//echo " big = ".$big."\n";
    	return $big;
    }
}

// $c = new Curve25519();
// print($c);
