<?php
	function getUrl($a=0){
		//$a=0 //trả về mảng
		//$a=1 //trả về json
		$url['method'] = ( array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on" ? "https" : "http");
		$url['port']=$_SERVER["SERVER_PORT"];
		$url['server']=$url['method']."://".$_SERVER["SERVER_NAME"].($url['port']!=80&&$url['port']!=443 ? ":".$url['port'] : "");
		$url['host']=$_SERVER["SERVER_NAME"];
		$url['uri'] = substr($_SERVER["REQUEST_URI"],1);
		$url['full'] = $url['server']."/".$url['uri'];
		if($url['uri']==""){
			$url['uri']="/";
		}

		if($a==1){
			return json_encode($url);
		}else{
			return $url;
		}
	}

	echo "<pre>";
	print_r(getUrl());
	echo "</pre>";
?>