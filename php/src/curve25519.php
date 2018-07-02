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
    	$v = BcUtil::bcdechex($u);//u.toString(16);
    	if (strlen($v) != 64) {
    		throw new Exception(' bcmath number too long: '.$v);
    	}
    	return $v;
    }

    /**
     * Curve25519 implementation. All input/output is long decimal
     * @param string $k
     * @param string $u
     * @param int $bits
     * @return string
     */
    public function x25519( string $k, string $u, int $bits): string {
//     	BcUtil::lengthHex($k, 32);
//     	BcUtil::lengthHex($u, 32);
    	//BigInteger x_1, x_2, z_2, x_3, z_3, swap;
        $x_1 = $u;
        $x_2 = "1";
        $z_2 = "0";
        $x_3 = $u;
        $z_3 = "1";
        $swap = "0";
        for ($t = $bits-1; $t >= 0; $t--) {
//         	BigInteger k_t = k.shiftRight(t).and(BigInteger.ONE);
//         	swap = swap.xor(k_t);
//         	//System.out.println("t k_t swap " + t + " " + k_t.toString(16) + " " + swap.toString(16) );
//         	BigInteger[] cs = cswap(swap, x_2, x_3);
//         	x_2 = cs[0];
//         	x_3 = cs[1];
//         	cs = cswap(swap, z_2, z_3);
//         	z_2 = cs[0];
//         	z_3 = cs[1];
//         	swap = k_t;
			$k_t = BcUtil::shiftRightDec($k, $t, 32);
//			$this->out($k_t, "k_t shift ");
			$k_t = BcUtil::andDec($k_t, "1", 32); 
// 			$this->out($k_t, "k_t and ");
			$swap = BcUtil::xorDec($swap, $k_t, 32);
//			echo "swap ".$swap."\n";
			// 			$this->out($swap, "swap xor ");
			//echo "t k_t swap ". $t. " " .self::bcdechex($k_t) . " " .self::bcdechex($swap)."\n";
			$cs = $this->cswapDec($swap, $x_2, $x_3);
        	$x_2 = $cs[0];
        	$x_3 = $cs[1];
        	$cs = $this->cswapDec($swap, $z_2, $z_3);
        	$z_2 = $cs[0];
        	$z_3 = $cs[1];
        	$swap = $k_t;
//         	$this->out($x_2, "x_2 ");
//         	$this->out($x_3, "x_3 ");
//         	$this->out($z_2, "z_2 ");
//         	$this->out($z_3, "z_3 ");
        	$A = bcadd($x_2, $z_2);
        	$AA = bcpow($A, "2");
        	$B = bcsub($x_2, $z_2);
        	$BB = bcpow($B, "2");
        	$E = bcsub($AA, $BB);
        	$C = bcadd($x_3, $z_3);
        	$D = bcsub($x_3, $z_3);
        	$DA = bcmul($D, $A);
        	$CB = bcmul($C, $B);
        	//         	x_3 = DA.add(CB).pow(2).mod(p);
			$x_3 = bcadd($DA, $CB);
			$x_3 = bcpow($x_3, "2");
			$x_3 = bcmod($x_3, self::$p);
//         	z_3 = x_1.multiply(DA.subtract(CB).pow(2)).mod(p);
			$z_3 = bcsub($DA, $CB);
			$z_3 = bcpow($z_3, "2");
			$z_3 = bcmul($x_1, $z_3);
			$z_3 = bcmod($z_3, self::$p);
			//         	x_2 = AA.multiply(BB).mod(p);
			$x_2 = bcmul($AA, $BB);
			$x_2 = bcmod($x_2, self::$p);
//         	z_2 = E.multiply(AA.add(a24.multiply(E))).mod(p);
			$z_2 = bcmul(self::$a24, $E);
			$z_2 = bcadd($AA, $z_2);
			$z_2 = bcmul($E, $z_2);
			$z_2 = bcmod($z_2, self::$p);
// 			echo "k_t ".$k_t."\n";
// 			echo "A ".$A."\n";
// 			echo "AA ".$AA."\n";
// 			echo "B ".$B."\n";
// 			echo "BB ".$BB."\n";
// 			echo "E ".$E."\n";
// 			echo "C ".$C."\n";
// 			echo "D ".$D."\n";
// 			echo "DA ".$DA."\n";
// 			echo "CB ".$CB."\n";
// 			echo "x_3 ".$x_3."\n";
// 			echo "z_3 ".$z_3."\n";
// 			echo "x_2 ".$x_2."\n";
// 			echo "z_2 ".$z_2."\n";
// 			echo " t = ".$t."\n";
			//if ($t < 100)exit;
			// 			$this->out($z_2, " z_2");
        }
//         BigInteger[] cond2 = cswap(swap, x_2, x_3);
//         x_2 = cond2[0];
//         x_3 = cond2[1];
//         cond2 = cswap(swap, z_2, z_3);
//         z_2 = cond2[0];
//         z_3 = cond2[1];
//         BigInteger ret = x_2.multiply(z_2.modPow(p_minus2, p));
//         ret = ret.mod(p);
        $cond2 = $this->cswapDec($swap, $x_2, $x_3);
        $x_2 = $cond2[0];
        $x_3 = $cond2[1];
        $cond2 = $this->cswapDec($swap, $z_2, $z_3);
        $z_2 = $cond2[0];
        $z_3 = $cond2[1];
        $mP = bcpowmod($z_2, self::$p_minus2, self::$p); 
        $ret = bcmul($x_2, $mP);
        return bcmod($ret, self::$p);
    }
    
    /**
     * cswap with long decimal input 
     * @param string $swap
     * @param string $x_2
     * @param string $x_3
     * @return array
     */
    private function cswapDec(string $swap, string $x_2, string $x_3): array {
        // swap is 0 or 1
        //out(x_2, "swap a");
        //out(x_3, "swap b");
        //System.out.println(swap);
        // $swap needs to be decimal:
        $dummy = bcsub("0", $swap);
        $dummy2 = $dummy;
//         echo "dummy ".$dummy."\n";
        // now switch everything to hex:
        //echo "cswap dummy ".$dummy."\n";
        $dummy = BCUtil::dec2hex($dummy, 32);
        //echo "cswap dummy ".$dummy."\n";
        //$dummy2 = BcUtil::bcdechex($dummy2);
//         echo "dummy2 ".$dummy2."\n";
        $x_2 = BCUtil::dec2hex($x_2, 32);
//         echo "cswap x_3 ".$x_3."\n";
        $x_3 = BCUtil::dec2hex($x_3, 32);
        $temp = BcUtil::xorHex($x_2, $x_3, 32);
//         echo "cswap x_2 ".$x_2."\n";
//         echo "cswap x_3 ".$x_3."\n";
//         echo "cswap x_2 xor x_3 ".$temp."\n";
        $dummy = BcUtil::andHex($dummy, BcUtil::xorHex($x_2, $x_3, 32), 32); //$dummy.and(x_2.xor(x_3));
        $a = [BcUtil::xorHex($x_2, $dummy, 32),
              BcUtil::xorHex($x_3, $dummy, 32)
        ];
        // convert result back to decimal:
//         echo "cswap x_2 ".$x_2."\n";
//         echo "cswap dummy ".$dummy."\n";
//         echo "cswap a 0 ".$a[0]."\n";
        //echo "cswap a 1 ".$a[1]."\n";
        $a[0] = BCUtil::hex2dec($a[0], 32);
        $a[1] = BCUtil::hex2dec($a[1], 32);
//         $this->out($a[0], "a[0] ");
//         $this->out($a[1], "a[1] ");
        return $a;
    }
    
    public function out(string $x, string $str) {
    	echo $str ." ".$this->asLittleEndianHexString($x)."\n";
    }
    
    
    public function asLittleEndianHexString(string $x): string {
        $r = $this->toByteArrayLittleEndian(BcUtil::bcdechex($x));
    	if(count($r) != 32) {
    		throw new Exception(' arrays for curve have to be 32 bytes: '.count($r));
    	}
    	return $this->asString($r);
    }
    
    public function x25519Simple( string $scalar, string $u): string {
    	$scalar = self::decodeScalar25519(self::toByteArray($scalar));
    	$uIn = self::decodeUCoordinate(self::toByteArray($u), 255);
    	$uOut = self::x25519($scalar, $uIn, 255);
    	return self::asLittleEndianHexString($uOut);
    }
}

// $c = new Curve25519();
// print($c);
