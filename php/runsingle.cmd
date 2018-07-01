@echo off
php phpunit.phar --bootstrap ./src/autoload.php --testdox tests --filter testCanBitwiseBCMath
