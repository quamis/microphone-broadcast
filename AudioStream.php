<?php
class AudioStream {
	public function streamCmdStdout($cmd) {
		error_log("open stream cmd: {$cmd}");
		
		$ph = popen($cmd, "r");
		$idx = 0;
		while (!feof($ph)) { 
			if ($idx%50) {
				if (connection_aborted()) {
					break;
				}
			}
			
			echo fread($ph, 2*1024);
			ob_flush();flush();
			
			$idx++;
		}
		pclose($ph);
	}
}
