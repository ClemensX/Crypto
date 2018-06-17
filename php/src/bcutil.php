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

    // check byte length, throws exception if wrong byte length, prepends 0 if too short
    public static function lengthHex( string $hex, int $bytes) :string
    {
        $lenBytes = intdiv(strlen($hex)+1, 2);
        if ($lenBytes > $bytes) {
            throw new Exception("input too long: ".$hex);
        }
        while (strlen($hex) < $bytes*2) {
            $hex = "0".$hex;
        }
        return $hex;
    }
    
    
    // conversions: use formats dec, hex, array. Array is used internally for all bit operations
    
    public static function dec2hex( string $dec, int $bytes) :string
    {
        $hex = self::bcdechex($dec);
        return self::lengthHex($hex, $bytes);
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
    
    public static function hex2array( string $hex, int $bytes) :array
    {
        $hex = self::lengthHex($hex, $bytes);
        return self::toByteArray($hex, $bytes);
    }
    
    public static function and( string $a, string $b) :string
    {
        return "";
    }
}

