<?php
#error_reporting(E_ALL); ini_set('display_errors', 'On');

require("AudioStream.php");
require("AudioFilters.php");

try {
	$af = new AudioFilters_hiq();
	$cmd = \CliCommand::getInstance()->buildCmdStr(
		 'ffmpeg -loglevel panic -hide_banner -nostats -f alsa -i {source} -f wav -ac {in_channels} -ar {in_frequency} pipe:1 | '
		.'sox -t wav - -t wav - noisered {noiseFile} 0.2 remix 2 loudness 5 dither | '
		.'ffmpeg -loglevel panic -hide_banner -nostats -i pipe:0 -f mp3 -c:a libmp3lame -ac {out_channels} -b:a {out_quality} -af {filters} pipe:1', [
			'source' => 		$af->getAudioSource(), 
			'in_channels' => 	$af->getSourceChannels(), 
			'in_frequency' => 	$af->getSourceFrequency(), 
			'filters' => 		implode(',', $af->getFilters_ffmpeg()),
			'noiseFile' => 		$af->getNoiseProfile(), 
			'out_channels' => 	$af->getOutputChannels(), 
			'out_quality' => 	$af->getOutputQualityKb(), 
	], null);
	
	if ($_GET['debug']) {
		printf("<pre>%s</pre>", $cmd); exit();
	}
} 
catch (\Exception $ex) {
	echo "<pre>";
	var_dump($ex->getMessage());
	echo "</pre>";
	exit();
}



header( 'Pragma: no-cache' );
header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header( 'Content-Type: audio/mpeg' ); 

$stream = new AudioStream();
$stream->streamCmdStdout($cmd);
