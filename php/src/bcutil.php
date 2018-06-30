<?php

/** 
 * Bit manipulations for bcmath.
 * Each function takes length in bytes
 * 
 */
class BcUtil
{
    // util functions without fixed bit length:
    public static function bchexdec($hex) {
        if(strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, self::bchexdec($remain)), hexdec($last));
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

    /**
     * Check if hex string is negative. INput must be hey string with even count chars: 2 chars for one byte
     * @param string $hex
     * @return bool
     */
    public static function isHexStringNegative(string $hex) : bool {
        $firstByte = substr($hex, 0, 2);
        $firstByteVal = hexdec($firstByte);
        $is_negative = $firstByteVal >= 0x80 ? TRUE : FALSE;
        return $is_negative;
    }
    
    /**
     * check byte length, throws exception if wrong byte length
     * auto adjust length: if more bytes needed: sign is prepended until byte nuber is ok
     * if less bytes needed: if highest bit set (negative): try shortening while 0xff byte found
     *   if highest bit not set (positive number): try shortening while zero byte found
     * @param string $hex
     * @param int $bytes
     * @throws Exception
     * @return string
     */
    public static function lengthHex( string $hex, int $bytes) :string
    {
        $original = $hex;
        $is_negative = self::isHexStringNegative($hex);
//         if ($is_negative )
//             echo $hex." is neg\n";
//         else
//             echo $hex." is pos\n";
        $lenBytes = intdiv(strlen($hex)+1, 2);
        //echo "len ".$lenBytes."\n";
        // shorten:
        if ($lenBytes > $bytes) {
            if ($is_negative) {
                $discardBytes = $lenBytes - $bytes;
                $hex = substr($hex, $discardBytes * 2, $bytes * 2);
                echo "short ".$hex."\n";
                if (!self::isHexStringNegative($hex)) {
                    // shortening left a positive number: fail!
                    throw new Exception("hex input could not be shortened to neg number: ".$original);
                }
                $lenBytes = intdiv(strlen($hex)+1, 2);
            }
        }
        if ($lenBytes > $bytes) {
            throw new Exception("hex input too long: ".$hex);
        }
        while (strlen($hex) < $bytes*2) {
            $hex = "0".$hex;
        }
        return $hex;
    }
    
    
    // conversions: use formats dec, hex, array. Array is used internally for all bit operations
    
    public static function hex2dec( string $hex, int $bytes) :string
    {
    	$hex = self::lengthHex($hex, $bytes);
    	return self::bchexdec($hex);
    }
    
    public static function dec2hex( string $dec, int $bytes) :string
    {
    	$hex = self::bcdechex($dec);
    	echo "dec2hex ".$hex."\n";
    	return self::lengthHex($hex, $bytes);
    }
    
    public static function dec2array( string $dec, int $bytes) :array
    {
        $hex = self::dec2hex($dec, $bytes);
        return self::hex2array($hex, $bytes);
    }
    
    /**
     * Convert Hex String FA01EE... to int array 0xfa,0x01,0xee
     * Hex byte arrays are strings in PHP.
     * @return string
     */
    private static function toByteArray(string $hexString, int $bytes): array
    {
        if(strlen($hexString) != $bytes*2) {
            throw new Exception("Internal Error: incorrect length: ".$hexString);
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
        if(count($a) != $bytes) {
            throw new Exception("Internal Error: incorrect array length: ".$a);
        }
        return $a;
    }
    
    /**
     * @param string $hex
     * @param int $bytes
     * @return array
     */
    public static function hex2array( string $hex, int $bytes) :array
    {
        $hex = self::lengthHex($hex, $bytes);
        return self::toByteArray($hex, $bytes);
    }
    
    /**
     * convert array to hex string
     * @param array $b
     * @param int $bytes
     * @throws Exception
     * @return string
     */
    public static function array2hex(array $b, int $bytes): string {
    	if(count($b) != $bytes) {
    		throw new Exception(' arrays for curve have to be 32 bytes: '.count($b));
    	}
    	$buf = "";
    	for ($i = 0; $i < $bytes; $i++) {
    		$h = dechex($b[$i]);
    		if (strlen($h) < 2) {
    			$h = '0'.$h;
    		}
    		$buf = $buf.$h;
    	}
    	return self::lengthHex($buf, $bytes);
    }
    
    /**
     * convert array to decimal string
     * @param array $b
     * @param int $bytes
     * @throws Exception
     * @return string
     */
    public static function array2dec(array $b, int $bytes): string {
    	$hex = self::array2hex($b, $bytes);
    	return self::hex2dec($hex, $bytes);
    }
    
    // logical functions
    
    /**
     * Shift bits to the right, fill empty bits with 0
     * @param string $a
     * @param int $shift
     * @param int $bytes
     * @return string
     */
    public static function shiftRightDec( string $a, int $shift, int $bytes) :string
    {
    	// calc divisor:
    	$divisor = bcpow("2", $shift);
    	// calc division:
    	//echo "shiftright division: ".$a." / ".$divisor."\n";
    	$d = bcdiv($a, $divisor);
    	// return the division:
    	return $d;
    }
    
        /**
     * Shift bits to the right, fill empty bits with 0
     * @param string $a
     * @param int $shift
     * @param int $bytes
     * @return string
     */
    public static function shiftRightHex( string $a, int $shift, int $bytes) :string
    {
    	self::lengthHex($a, $bytes);
    	$a = self::bchexdec($a);
    	$d = self::shiftRightDec($a, $shift, $bytes);
    	// reformat to hex:
    	$d = self::bcdechex($d);
    	// return the division:
    	return self::lengthHex($d, $bytes);
    }
    
/**
     * Bitwise AND for arrays
     * @param array $a
     * @param array $b
     * @param int $bytes
     * @return array
     */
    public static function andArray( array $a, array $b, int $bytes) :array
    {
        // convert to arrays:
        for ($i = 0; $i < $bytes; $i++) {
            $a[$i] = 0xff & ($a[$i] & $b[$i]);
        }
        return $a;
    }
    
    /**
     * Bitwise AND for decimal strings
     * @param string $a
     * @param string $b
     * @param int $bytes
     * @return string
     */
    public static function andDec( string $a, string $b, int $bytes) :string
    {
    	// convert to arrays:
    	$a = self::dec2array($a, $bytes);
    	$b = self::dec2array($b, $bytes);
    	return self::array2dec(self::andArray($a, $b, $bytes), $bytes);
    }
    
        /**
     * Bitwise AND for hex strings
     * @param string $a
     * @param string $b
     * @param int $bytes
     * @return string
     */
    public static function andHex( string $a, string $b, int $bytes) :string
    {
        // convert to arrays:
        $a = self::hex2array($a, $bytes);
        $b = self::hex2array($b, $bytes);
        return self::array2hex(self::andArray($a, $b, $bytes), $bytes);
    }

    /**
     * Bitwise XOR for decimal strings
     * @param string $a
     * @param string $b
     * @param int $bytes
     * @return string
     */
    public static function xorDec( string $a, string $b, int $bytes) :string
    {
    	// convert to arrays:
    	$a = self::dec2array($a, $bytes);
    	$b = self::dec2array($b, $bytes);
    	return self::array2dec(self::xorArray($a, $b, $bytes), $bytes);
    }
    
    	/**
     * Bitwise XOR for hex strings
     * @param string $a
     * @param string $b
     * @param int $bytes
     * @return string
     */
    public static function xorHex( string $a, string $b, int $bytes) :string
    {
        // convert to arrays:
        $a = self::hex2array($a, $bytes);
        $b = self::hex2array($b, $bytes);
        return self::array2hex(self::xorArray($a, $b, $bytes), $bytes);
    }

/**
     * Bitwise XOR for arrays
     * @param array $a
     * @param array $b
     * @param int $bytes
     * @return array
     */
    public static function xorArray( array $a, array $b, int $bytes) :array
    {
    	// convert to arrays:
    	for ($i = 0; $i < $bytes; $i++) {
    		$a[$i] = 0xff & ($a[$i] ^ $b[$i]);
    	}
    	return $a;
    }
    
}

