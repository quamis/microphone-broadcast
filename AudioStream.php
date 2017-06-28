<?php
class AudioStream {
	public function streamCmdStdout($cmd) {
		error_log("open stream cmd: {$cmd}");
		
		$ph = popen($cmd, "r");
		$idx = 0;
		while (!feof($ph)) { 
			if ($idx%10==0) {
				if (connection_aborted()) {
					break;
				}
			}
			if ($idx>2000000) {
				$idx = 0;
			}
			
			echo fread($ph, 2*1024);
			ob_flush();flush();
			
			$idx++;
		}
		pclose($ph);
	}
}
