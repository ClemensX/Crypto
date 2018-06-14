<?php
spl_autoload_register(function ($class_name) {
    //include $class_name . '.php';
	$query = "PHPUnit";
	if (substr( $class_name, 0, strlen($query) ) === $query) {
		return;
	}
	$query = "Symfony";
	if (substr( $class_name, 0, strlen($query) ) === $query) {
		return;
	}
	$query = "Composer";
	if (substr( $class_name, 0, strlen($query) ) === $query) {
		return;
	}
	//echo "autoload ".$class_name . '.php'."\n";
	include $class_name . '.php';
});

//$obj1  = new Curve25519();
//$obj2 = new MyClass2(); 
?>
