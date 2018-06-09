<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

//$obj1  = new Curve25519();
//$obj2 = new MyClass2(); 
?>
