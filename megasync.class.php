<?php

#
#    megaSync implementation in PHP
#
#    Copyright (c) 2017 XJIOP
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#

class megaSync {
	
	public $CONFIG = array();
	public $FILES = array();
	
	public function sync() {

		$argv = $_SERVER['argv'];

		if(!isset($argv[1]))
			$this->error("Please specify local folder!");

		if(!isset($argv[2]))
			$this->error("Please specify remote folder!");	
	
		$this->CONFIG["LOCAL_PATH"] = htmlspecialchars($argv[1]);
		$this->CONFIG["MEGA_PATH"] = htmlspecialchars($argv[2]);
	
		if(!$this->CONFIG["LOGIN"] || !$this->CONFIG["PASS"])
			$this->error("Please set login and password!");	
		
		$this->getActionFiles();
		$this->deleteFromMega();
		$this->uploadToMega();
	}	
	
	public function getActionFiles() {
		
		$this->getLocalFiles();
		$this->getMegaFiles();
		
		$this->FILES["DELETE"] = array_diff($this->FILES["MEGA"], $this->FILES["LOCAL"]);
		$this->megaLog("Files for delete from mega | ".count($this->FILES["DELETE"]));
		
		$this->FILES["UPLOAD"] = array_diff($this->FILES["LOCAL"], $this->FILES["MEGA"]);
		$this->megaLog("Files for upload to mega | ".count($this->FILES["UPLOAD"]));
	}		
	
	public function getLocalFiles() {

		$files = preg_grep($this->extension_local(), scandir($this->CONFIG["LOCAL_PATH"]));
		if(!$files)
			$this->error("Local files not found...");
		
		$this->FILES["LOCAL"] = $files;
		$this->megaLog("Local files | ".count($files));
	}	
	
	public function getMegaFiles() {

		$megals = shell_exec("megals ".$this->CONFIG["ID"]." ".$this->CONFIG["MEGA_PATH"]." ".$this->extension_remote()." | sed 's!.*/!!' 2>&1");
		$files = array_filter(preg_split('/\s+/', trim($megals)));

		$this->FILES["MEGA"] = $files;
		$this->megaLog("Mega files | ".count($files));
	}	
	
	public function deleteFromMega() {
		
		$total = count($this->FILES["DELETE"]);
		$i=0;	
	
		foreach($this->FILES["DELETE"] AS $d) {

			$i++;	
			$time = time();
		
			for(;;) {
				$res = shell_exec("megarm ".$this->CONFIG["ID"]." ".$this->CONFIG["MEGA_PATH"]."/".$d." 2>&1");
				if(preg_match("#CURL error#i", $res))
					$this->megaLog("--- Delete error | $d | Try again | ".(time() - $time)." sec");
				else
					break;		
			}
		
			$this->megaLog("$i of $total | $d | Deleted | ".(time() - $time)." sec");
		}		
	}

	public function uploadToMega() {
		
		$total = count($this->FILES["UPLOAD"]);
		$i=0;

		// run megacopy if there are no mega files
		if(!$this->FILES["MEGA"]) {
			echo shell_exec("megacopy ".$this->CONFIG["ID"]." --local ".$this->CONFIG["LOCAL_PATH"]." --remote ".$this->CONFIG["MEGA_PATH"]." 2>&1");
			return;
		}
	
		foreach($this->FILES["UPLOAD"] AS $u) {
		
			$i++;
			$time = time();
		
			for(;;) {
				$res = shell_exec("megaput ".$this->CONFIG["ID"]." --path ".$this->CONFIG["MEGA_PATH"]."/".$u." ".$this->CONFIG["LOCAL_PATH"]."/".$u." 2>&1");
				if(preg_match("#CURL error#i", $res))
					$this->megalog("--- Upload error | $u | Try again | ".(time() - $time)." sec");
				else
					break;
			}
		
			$this->megaLog("$i of $total | $u | Uploaded | ".(time() - $time)." sec");
		}
	}
	
	public function extension_local() {
		
		if($this->CONFIG["EXTENSION"])
			$ext = "~\.(".implode("|", $this->CONFIG["EXTENSION"]).")$~";
		else			
			$ext = "/\.[^\.]+$/i";
		
		return $ext;
	}	
	
	public function extension_remote() {
		
		if($this->CONFIG["EXTENSION"]) {
			
			foreach($this->CONFIG["EXTENSION"] AS $e)
				$res[] = "\.$e$";

			$ext = "| egrep '".implode("|", $res)."'";
		}
		
		return $ext;
	}	
	
	public function megaLog($msg) {
	
		if(!$this->CONFIG["LOG"])
			return;
	
		echo $msg."\n";
		flush();
	}

	public function error($msg) {
		echo $msg."\n";
		die();
	}	
}

?>
