<?php 
	function isFile($url){
		if(!is_file($url)){
			return false;
		}
		return true;
	}
	function isJson($string) {
		return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
	}
	class objFaIDb{
		public $db;
		public $res;
		function __construct(string $db){
			if(isFile("app/FaIDb/{$db}.json")){
				$dbFaI=filesize("app/FaIDb/{$db}.json");
				if($dbFaI==0){
					$dbFaI = @fopen("app/FaIDb/{$db}.json", "w+");
					fwrite($dbFaI, "{}");
					fclose($dbFaI);
					$this->db = $db;
				}else{
					$dbFaI=fread(@fopen("app/FaIDb/{$db}.json", "r"), $dbFaI);
					$this->db = $db;
					if(isJson($dbFaI)){
						$this->res = json_decode(fread(@fopen("app/FaIDb/{$this->db}.json", "r"), filesize("app/FaIDb/{$this->db}.json")), true);
					}else{
						return false;
					}
				}
			}else{
				$dbFaI = @fopen("app/FaIDb/{$db}.json", "a");
				fwrite($dbFaI, "{}");
				fclose($dbFaI);
				$this->db = $db;
			}
		}
		function clear(){
			$dbFaI = @fopen("app/FaIDb/{$this->db}.json", 'w+');
			if(!$dbFaI){
				return false;
			}else{
				fwrite($dbFaI, '{}');
				fclose($dbFaI);
				return true;
			}
		}
		function get(){
			return $this->res;
		}
		function save($data){
			$dbFaI = @fopen("app/FaIDb/{$this->db}.json", 'w+');
			if(!$dbFaI){
				return false;
			}else{
				fwrite($dbFaI, json_encode($data));
				fclose($dbFaI);
				$this->res=$data;
				return true;
			}
		}
	}
	/**
	 * xử lý thời gian
	 */
	class objTime{
		public int $time;
		private int $tt;
		public function __construct($strTime=""){
			if(is_int($strTime)) {
				$this->time = $strTime;
			}elseif(is_string($strTime)){
				$this->time = strtotime($strTime==""?date("Y-m-d H:i:s"):$strTime);
			}
		}
		public function change($strTime=""){
			if(is_int($strTime)) {
				$this->time = $strTime;
			}elseif(is_string($strTime)){
				$this->time = strtotime($strTime==""?date("Y-m-d H:i:s"):$strTime);
			}
		}
		public function strTimeToInt(string $str){
			preg_match('/(\d*)s/', $str, $b);
			$this->tt=$b[1]??0;
			preg_match('/(\d*)i/', $str, $b);
			$this->tt += ($b[1]??0)*60;
			preg_match('/(\d*)h/', $str, $b);
			$this->tt += ($b[1]??0)*3600;
			preg_match('/(\d*)d/', $str, $b);
			$this->tt += ($b[1]??0)*86400;
			preg_match('/(\d*)m/', $str, $b);
			$this->tt += ($b[1]??0)*2629800;
			preg_match('/(\d*)y/', $str, $b);
			$this->tt += ($b[1]??0)*31557600;
		}
		public function com(string $str){
			$this->strTimeToInt($str);
			return $this->time+$this->tt;
		}
		public function sub(string $str){
			$this->strTimeToInt($str);
			return $this->time-$this->tt;
		}
		public function div(string $str){
			$this->strTimeToInt($str);
			return $this->time/($this->tt==0?1:$this->tt);
		}
		public function intDiv(string $str){
			$this->strTimeToInt($str);
			return intdiv($this->time, ($this->tt==0?1:$this->tt));
		}
		public function get(){
			return $this->time;
		}
	}

	function isFolder($url){
		if(!is_dir($url)){
			return false;
		}
		return true;
	}
	function redirect(string $a, int $b=1){
		switch($b){
			case 1:{
				header("location:$a");
				break;
			}
			case 2:{
				echo '<script type="text/javascript">window.location="'.$a.'";</script>';
			}
			default:{
				header("location:$a");
				break;
			}
		}
		exit();
	}
	function query($a, $b=1){
		$b--;
		$c=json_decode(fread(@fopen('app/database.json', "r"), filesize('app/database.json')), true);

		if(isset($c[$b])){
			$d=mysqli_connect($c[$b]['host'], $c[$b]['user'], $c[$b]['pass'], $c[$b]['db']);

			$e['sql']=$a;

			if(isset($d->connect_error)){
				$e['db_error']=$d->connect_error;
			}else{
				$d->set_charset('UTF8');
				$f=$d->query($a);

				if(isset($d->error)){
					$e['error']=$d->error;
				}else{
					if(isset($d->insert_id)) {
						$e['id']=$d->insert_id;
					}
					if(isset($f->num_rows)){
						if($f->num_rows<1){
							$e['sl']=0;
						}else{
							$e['sl']=$f->num_rows;
							for($i=0; $i<$e['sl']; $i++){
								$e[$i]=$f->fetch_assoc();
							}
						}
					}
				}
			}
			return $e;
			$d->close();
		}else{
			$e['db_error']='không tìm thấy database';
		}
	}
?>