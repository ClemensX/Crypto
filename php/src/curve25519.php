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
     * Decode scalar by masking highest bit and lowest 3 bits,
     * then set 2nd highest bit
     * Input scalar has to be a 32 byte value, IllegalArgumentException thrown if not
     * @param b
     * @return
     */
    public function decodeScalar25519(array $cloned) : string {
    	if(count($cloned) != 32) {
    		throw new Exception(' arrays for curve have to be 32 bytes: '.count($b));
    	}
    	// we can savely work on $b, it is a copy already...
    	// clear lowest bit
    	$cloned[0] = (($cloned[0]) & 248);
    	// clear highest bit
    	$cloned[31] = (($cloned[31]) & 127);
    	// set 2nd highest bit:
    	$cloned[31] = (($cloned[31]) | 64);
    	return $this->decodeLittleEndian($cloned, 255);
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

    public function asString(array $b): string {
    	if(count($b) != 32) {
    		throw new Exception(' arrays for curve have to be 32 bytes: '.count($b));
    	}
    	$buf = "";
    	for ($i = 0; $i < 32; $i++) {
    		$h = dechex($b[$i]);
    		if (strlen($h) < 2) {
    			$h = '0'.$h;
    		}
    		$buf = $buf.$h;
    	}
    	return $buf;
    }
/*    
	public String toString(byte[] bytes) {
		StringBuffer buf = new StringBuffer();
		for (int i = 0; i < bytes.length; i++) {
			buf.append(String.format("%02x", bytes[i]));
		}
		return buf.toString();
	}

*/
    public function decodeUCoordinate(array $cloned, int $bits): string {
    	if(count($cloned) != 32) {
    		throw new Exception(' arrays for curve have to be 32 bytes: '.count($cloned));
    	}
    	// we can savely work on $b, it is a copy already...
    	// clear highest bit
    	$cloned[31] = (($cloned[31]) & 0x7f);
    	return $this->decodeLittleEndian($cloned, $bits);
    	
    }

    public function encodeUCoordinate(string $u, int $bits):string {
    	$u = bcmod($u, self::$p);
    	$v = self::bcdechex($u);//u.toString(16);
    	if (strlen($v) != 64) {
    		throw new Exception(' bcmath number too long: '.$v);
    	}
    	return $v;
    }

    public function x25519( string $k, string $u, int $bits): string {
        //BigInteger x_1, x_2, z_2, x_3, z_3, swap;
        $x_1 = $u;
        $x_2 = "1";
        $z_2 = "0";
        $x_3 = $u;
        $z_3 = "1";
        $swap = "0";
        for ($t = $bits-1; $t >= 0; $t--) {
        }
        $cond2 = $this->cswap($swap, $x_2, $x_3);
        return "1";
    }
    
    private function cswap(string $swap, string $x_2, string $x_3): array {
        // swap is 0 or 1
        //out(x_2, "swap a");
        //out(x_3, "swap b");
        //System.out.println(swap);
        $dummy = bcsub("0", $swap);
        // bitwise operations: ? scalar -> decodeLittleendian -> hexString -> toByteArray -> operation
        $dummy = BcUtil::andHex($dummy, BcUtil::xorHex($x_2, $x_3, 32), 32); //$dummy.and(x_2.xor(x_3));
        $a = [BcUtil::xorHex($x_2, $dummy, 32),
              BcUtil::xorHex($x_3, $dummy, 32)
        ];
        return $a;
    }
    
    public function out(string $x, string $str) {
    	echo $str ." ".$this->asLittleEndianHexString($x)."\n";
    }
    
    
    public function asLittleEndianHexString(string $x): string {
    	$r = $this->toByteArrayLittleEndian(self::bcdechex($x));
    	if(count($r) != 32) {
    		throw new Exception(' arrays for curve have to be 32 bytes: '.count($r));
    	}
    	return $this->asString($r);
    }
    
    // further php utils:
    public static function bchexdec($hex) {
    	if(strlen($hex) == 1) {
    		return hexdec($hex);
    	} else {
    		$remain = substr($hex, 0, -1);
    		$last = substr($hex, -1);
    		return bcadd(bcmul(16, bchexdec($remain)), hexdec($last));
    	}
    }
    
    public static function bcdechex($dec) {
    	$last = bcmod($dec, "16");
    	$remain = bcdiv(bcsub($dec, $last), "16");
    	
    	if($remain == 0) {
    		return dechex($last);
    	} else {
    		return self::bcdechex($remain).dechex($last);
    	}
    }
}

// $c = new Curve25519();
// print($c);
