HiveHomeAPI-PHP
================

PHP class to set and get values for the British Gas Hive Home heating/automation


What is the purpose?
====================

I want to include my Hive Home thermostat in my OpenHAB2 installation. PHP scripts seem the easiest way to do this

Why should I use your code?
===========================

You shouldn't. It's untested. I'm not very good at writing PHP. I wrote it for me, not you, but you can have a look if you like.


How exactly does this code work?
================================

It's a PHP helper class around the v6 REST API for Hive products - AlertMe products that have been bought out by British Gas


What things can I do with this code?
====================================

Currently you can
- Get the current temperature and setpoint
- tell me when the boiler is firing or not

 I want it to:
- boost heating and hotwater
- turn the system onto holiday mode

all from the command line, so I can interface to it easily through OpenHAB



Whats the simplest piece of code to get up and running
======================================================

```php
<?php
	include ("class.hivehome.php");
	$hive = new HiveHome("hiveusername", "hivepassword");
	$hive->printCurrentTemp();
?>
```
