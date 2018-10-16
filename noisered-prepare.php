<?php
#error_reporting(E_ALL ^ E_NOTICE);
#error_reporting(E_ALL); ini_set('display_errors', 'On');

require("AudioStream.php");
require("AudioFilters.php");


echo <<<PHP
<html class="" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
	<head>
		<title> - </title>
	</head>
</html>
PHP;


try {
	$af = new AudioFilters_hiq();
	$af->generateNoiseProfile();
} 
catch (\Exception $ex) {
	echo "<pre>";
	var_dump($ex->getMessage());
	echo "</pre>";
}
