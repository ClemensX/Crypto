<?php
declare(strict_types=1);

final class Curve25519
{

		public static $p;
		public static $p_minus2;
		public static $a24;

    public function __construct()
    {
        //$this->ensureIsValidEmail($email);
				// calculate p as 2 ^ 255 - 19
				self::$p = "2";
				self::$p = bcpow(self::$p, "255");
				self::$p = bcsub(self::$p, "19");
				self::$p_minus2 = bcsub(self::$p, "2");
				self::$a24 = "121665";

    }

    public function __toString(): string
    {
        return "p=".self::$p;
    }

}

	$c = new Curve25519();
	print($c);
//	echo "xxx".$c."\n";
//	echo "yyy".Curve25519::$p;
