<?php
error_reporting(E_ALL);
require("AudioStream.php");

$source = 'hw:0';

$quality = 128;
$channels = 2;
$frequency = 44100;

$filters = Array();
$filters[]= 'highpass=f=150:width_type=h:w=100';
$filters[]= 'lowpass=f=12000';
$filters[]= 'dynaudnorm=f=100:m=50.0:r=1.0:s=30.0';

$cmd = sprintf('ffmpeg -loglevel panic -hide_banner -nostats -f alsa -i %s -f mp3 -c:a libmp3lame -ac %d -ar %d -b:a %dk -af \'%s\' pipe:1', $source, $channels, $frequency, $quality, implode(',', $filters));


header( 'Pragma: no-cache' );
header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header( 'Content-Type: audio/mpeg' ); 

$stream = new AudioStream();
$stream->streamCmdStdout($cmd);
