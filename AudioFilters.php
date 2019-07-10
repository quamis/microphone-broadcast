<?php
class CliCommand {
	static protected $instance;
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new CliCommand();
		}
		
		return self::$instance;
	}
	
	public function buildCmdStr($cmd, array $params, $redirectStdError=null) {
		foreach ($params as $k=>$v) {
			$cmd = str_replace("{".$k."}", escapeshellarg($v), $cmd);
		}
		
		if ($redirectStdError===null) {
			// do nothing
		}
		elseif ($redirectStdError==true) {
			$cmd.= " 2>&1";
		}
		elseif (is_string($redirectStdError)) {
			$cmd.= sprintf(" 2>%s", escapeshellarg($redirectStdError));
		}
		
		return $cmd;
	}
	
	public function execRaw($cmd, array $params, $redirectStdError=null) {
		$cmd = $this->buildCmdStr($cmd, $params, $redirectStdErr);
		
		$output = [];
		$return_var = -1;
		exec($cmd, $output, $return_var);
		
		return [implode("\n", $output), $return_var];
	}
	
	public function execAndExpectSuccess($cmd, $params, $redirectStdError=true) {
		list($output, $return_var) = $this->execRaw($cmd, $params, $redirectStdError);
		
		if ($return_var!==0) {
			throw new \RuntimeException(sprintf("Failed running `%s`. Got error: %s", $cmd, $output));
		}
		
		return $output;
	}
}

class AudioFilters_hiq extends AudioFilters {
	public function getFilters_ffmpeg() {
		$filters = [];
		$filters[]= 'highpass=f=150:width_type=h:w=100';
		$filters[]= 'lowpass=f=12000';
		$filters[]= 'dynaudnorm=f=100:m=50.0:r=1.0:s=30.0';
		
		return $filters;
	}
	
	
	public function getSourceFrequency() {
		return 44100;
	}
	
	public function getOutputQualityKb() {
		return 128;
	}
	
	public function getSourceChannels() {
		return 2;
	}
	
	public function getOutputChannels() {
		return 1;
	}
}


class AudioFilters_lowq extends AudioFilters {
	public function getFilters_ffmpeg() {
		$filters = [];
		$filters[]= 'highpass=f=400:width_type=h:w=200';
		$filters[]= 'lowpass=f=6000';
		$filters[]= 'dynaudnorm=f=100:m=50.0:r=1.0:s=30.0';
		
		return $filters;
	}
	
	
	public function getSourceFrequency() {
		return 22050;
	}
	
	public function getOutputQualityKb() {
		return 32;
	}
	
	public function getSourceChannels() {
		return 2;
	}
	
	public function getOutputChannels() {
		return 1;
	}
}

abstract class AudioFilters {
	protected $tmpFile = 'tmp/noise-sample.wav';
	protected $noiseFile = 'tmp/noise-sample.wav.noiseprofile';
	
	public function getAudioSource() {
		return 'hw:0,0';
	}
	
	public function generateNoiseProfile($timeToSampleNoise=15) {
		if (is_file($this->tmpFile)) { 
			unlink($this->tmpFile); 
		}
		if (is_file($this->noiseFile)) { 
			unlink($this->noiseFile); 
		}
		
		\CliCommand::getInstance()->execAndExpectSuccess('ffmpeg -t {timeToSampleNoise} -f alsa -i {source} -f wav -ac {channels} -ar {frequency} {output}', [
			'timeToSampleNoise' => 	$timeToSampleNoise,
			'source' => 			$this->getAudioSource(), 
			'channels' => 			$this->getSourceChannels(), 
			'frequency' => 			$this->getSourceFrequency(), 
			'output' => 			$this->tmpFile,
		]);
		
		\CliCommand::getInstance()->execAndExpectSuccess('sox {input} -n noiseprof {output}', [
			'input' => 		$this->tmpFile, 
			'output' => 	$this->noiseFile,
		]);
		
		return $this;
	}
	
	public function getNoiseProfile() {
		if (!is_file($this->noiseFile)) {
			$this->generateNoiseProfile();
		}
		
		return $this->noiseFile;
	}
}
