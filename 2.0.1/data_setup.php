<?php
	$s = microtime(true); date_default_timezone_set('Asia/Ho_Chi_Minh');
	if(!isset($_SESSION)){
		session_start();
	}
	$url = array();
	$url['method'] = ( array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on" ? "https" : "http");
	$url['port']=$_SERVER["SERVER_PORT"];
	$url['server']=$url['method']."://".$_SERVER["SERVER_NAME"].($url['port']!=80 ? ":".$url['port'] : "");
	$url['uri'] = substr($_SERVER["REQUEST_URI"],1);
	$url['full'] = $url['server']."/".$url['uri'];
	//xử lý uri;
	if($url['uri']==""){
		$url['uri']="/";
	}
	//file xử lý router
	/**
	 * tại đây router sẽ được kiểm tra và phân luồng như sau
	 kiểm tra sự thay đổi router fontend>kiểm tra thay đổi backend> xử lý uri
	 */
	class router{

		private $configRouter, $interFaceRouter, $backRouter, $fastRouter;
		private $capacity, $version, $default;
		private $uri;
		private $dataconfigRouter, $databackRouter, $datafastRouter;

		function __construct($uri, $a=0){
			$this->uri=$uri;
			$this->configRouter = new objFaIDb('configRouter');
			$this->dataconfigRouter = $this->configRouter->get();
			if($a==0){
				$this->d($this->seach());
			}else{
				if(!isFile("app/router.json")){
					echo "lỗi 0x000001: File xử lý router không tồn tại";
				}else{
					$this->capacity = filesize("app/router.json");
					if($this->capacity==0){
						echo "lỗi 0x000002: File router không có dữ liệu";
					}else{
						$this->interFaceRouter=fread(@fopen("app/router.json", "r"), $this->capacity);
						if(!isJson($this->interFaceRouter)){
							echo "lỗi 0x000003: File router có định dạng không đúng";
						}else{
							$this->interFaceRouter=json_decode($this->interFaceRouter, true);
							if(!isset($this->interFaceRouter['f@config']['version'])){
								echo "lỗi 0x000004: Không tồn tại version trong f@config ở file router";
							}else{
								$this->version = $this->interFaceRouter['f@config']['version'];
								if((!isset($this->dataconfigRouter['version']))||($this->dataconfigRouter['version']!=$this->version)){
									$this->change();
									$this->d($this->seach());
								}elseif((!isset($this->dataconfigRouter['capacity']))||($this->dataconfigRouter['capacity']!=$this->capacity)){
									$this->change();
									$this->d($this->seach());
								}else{
									$this->d($this->seach());
									$this->update();
								}
							}
						}
					}
				}
			}
		}

		private function a($a){
			$b[0]=$a['model']??null;
			$b[1]=$a['controler']??null;
			if(isset($a['view']['body'])&&is_array($a['view']['body'])){
				$b[2]=$a['view']['head']??null;
				if(isset($a['view']['body']['header'])||isset($a['view']['body']['contenter'])||isset($a['view']['body']['footer'])){
					$b[3]=$a['view']['body']['header']??null;
					$b[4]=$a['view']['body']['contenter']??null;
					$b[5]=$a['view']['body']['footer']??null;
				}else{
					$b[3]=((isset($a['view']['body'][0])&&is_string($a['view']['body'][0]))?$a['view']['body'][0]:null);
					$b[4]=((isset($a['view']['body'][1])&&is_string($a['view']['body'][1]))?$a['view']['body'][1]:null);
					$b[5]=((isset($a['view']['body'][2])&&is_string($a['view']['body'][2]))?$a['view']['body'][2]:null);
				}
			}else{
				$b[2]=((isset($a['view'][0])&&is_string($a['view'][0]))?$a['view'][0]:null);
				$b[3]=((isset($a['view'][1])&&is_string($a['view'][1]))?$a['view'][1]:null);
				$b[4]=((isset($a['view'][2])&&is_string($a['view'][2]))?$a['view'][2]:null);
				$b[5]=((isset($a['view'][3])&&is_string($a['view'][3]))?$a['view'][3]:null);
				
			}
			return $b;
		}

		private function b($a, $b){
			for($i=0; $i<6; $i++){
				$b[$i]=($b[$i]==null?$a[$i]:$b[$i]);
			}
			return $b;
		}

		private function c($a, $b, $c){
			//echo $a."<br>";
			/*
				*anvia*: anh có thể bỏ cách này được không em chẳng hiểu đc sao anh lại chơi cái trò này -_-
				*anfha*: cái này để tránh bị lộ mã nguồn :)
				*anvia*: đã chơi khó còn không có ghi chú sau này tha hồ mà bug
				*anfha*: có em theo dõi và bug rồi còn gì :)
				*anfha*: $d là biến router tạm thời :D
				*anvia*: .....
				*anvia*: lậu vậy? bắt em thức đến mấy giờ đây?
				*anfha*: anh sai giải thuật :v
			*/
			if(isset($b['f@setup'])){
				$c=$this->b($c, $this->a($b['f@setup']));
				unset($b['f@setup']);
			}

			if(count($b)>0){
				$a=(preg_match('/\/$/', $a)?$a:$a.'/');

				foreach (array_keys($b) as $d){
					if(isset($b[$d]['f@setup'])){
						$e=$this->b($c, $this->a($b[$d]['f@setup']));
						unset($b[$d]['f@setup']);
					}else{
						$e=$c;
					}

					if(is_array($b[$d]) && count($b[$d])>0){
						if($a=="/"){
							if($d==""||$d=="/"){
								$f=$this->c("/", $b[$d], $e);
								foreach(array_keys($f) as $h){
									$g[$h]=$f[$h];
								}
							}else{
								$f=$this->c($d, $b[$d], $e);
								foreach(array_keys($f) as $h){
									$g[$h]=$f[$h];
								}
							}
						}else{
							if($d==""||$d=="/"){
								$f=$this->c($a, $b[$d], $e);
								foreach(array_keys($f) as $h){
									$g[$h]=$f[$h];
								}
							}else{
								$f=$this->c($a.$d, $b[$d], $e);
								foreach(array_keys($f) as $h){
									$g[$h]=$f[$h];
								}
							}
						}
					}else{
						if($d=="/"||$d==""){
							$g[$a]=$e;
						}else{
							$g[$a.$d]=$e;
						}
					}
				}
				return $g;
			}else{
				return ;
			}
		}
		private function d($a){
			if(isset($a[0])&&$a[0]!=''&&$a[0]!=null){
				if(preg_match('/\@/', $a[0])){
					$b=preg_replace('/(.*)@(.*)/', '$1', $a[0]);
					$c=preg_replace('/(.*)@(.*)/', '$2', $a[0]);
					if(isFile('model/'.$b.'/'.$c.'.php')){
						require('model/'.$b.'/'.$c.'.php');
					}else{
						if(isFile('model/'.$b.'.php')){
							require('model/'.$b.'.php');
						}else{
							echo "Không tìm thấy file model<br>";
						}
					}
				}else{
					if(isFile('model/'.$a[0].'.php')){
						require('model/'.$a[0].'.php');
					}else{
						echo 'Không tìm thấy file model/'.$a[0].'.php<br>';
					}
				}
			}
			if(isset($a[1])&&$a[1]!=''&&$a[1]!=null){
				if(preg_match('/\@/', $a[1])){
					$b=preg_replace('/(.*)@(.*)/', '$1', $a[1]);
					$c=preg_replace('/(.*)@(.*)/', '$2', $a[1]);
					if(isFile('controller/'.$b.'/'.$c.'.php')){
						require('controller/'.$b.'/'.$c.'.php');
					}else{
						if(isFile('controller/'.$b.'.php')){
							require('controller/'.$b.'.php');
						}else{
							echo "Không tìm thấy file controller<br>";
						}
					}
				}else{
					if(isFile('controller/'.$a[1].'.php')){
						require('controller/'.$a[1].'.php');
					}else{
						echo 'Không tìm thấy file controller/'.$a[1].'.php<br>';
					}
				}
			}
			if(isset($a[2])&&$a[2]!=''&&$a[2]!=null){
				if(preg_match('/\@/', $a[2])){
					$b=preg_replace('/(.*)@(.*)/', '$1', $a[2]);
					$c=preg_replace('/(.*)@(.*)/', '$2', $a[2]);
					if(isFile('view/head/'.$b.'/'.$c.'.php')){
						require('view/head/'.$b.'/'.$c.'.php');
					}else{
						if(isFile('view/head/'.$b.'.php')){
							require('view/head/'.$b.'.php');
						}else{
							echo "Không tìm thấy file hiển thị head trong view/head<br>";
						}
					}
				}else{
					if(isFile('view/head/'.$a[2].'.php')){
						require('view/head/'.$a[2].'.php');
					}else{
						echo 'Không tìm thấy file view/head/'.$a[2].'.php<br>';
					}
				}
			}
			if(isset($a[3])&&$a[3]!=''&&$a[3]!=null){
				if(preg_match('/\@/', $a[3])){
					$b=preg_replace('/(.*)@(.*)/', '$1', $a[3]);
					$c=preg_replace('/(.*)@(.*)/', '$2', $a[3]);
					if(isFile('view/body/header/'.$b.'/'.$c.'.php')){
						require('view/body/header/'.$b.'/'.$c.'.php');
					}else{
						if(isFile('view/body/header/'.$b.'.php')){
							require('view/body/header/'.$b.'.php');
						}else{
							echo "Không tìm thấy file hiển thị header trong view/body/header<br>";
						}
					}
				}else{
					if(isFile('view/body/header/'.$a[3].'.php')){
						require('view/body/header/'.$a[3].'.php');
					}else{
						echo 'Không tìm thấy file view/body/header/'.$a[3].'.php<br>';
					}
				}
			}
			if(isset($a[4])&&$a[4]!=''&&$a[4]!=null){
				if(preg_match('/\@/', $a[4])){
					$b=preg_replace('/(.*)@(.*)/', '$1', $a[4]);
					$c=preg_replace('/(.*)@(.*)/', '$2', $a[4]);
					if(isFile('view/body/contenter/'.$b.'/'.$c.'.php')){
						require('view/body/contenter/'.$b.'/'.$c.'.php');
					}else{
						if(isFile('view/body/contenter/'.$b.'.php')){
							require('view/body/contenter/'.$b.'.php');
						}else{
							echo "Không tìm thấy file hiển thị contenter trong view/body/contenter<br>";
						}
					}
				}else{
					if(isFile('view/body/contenter/'.$a[4].'.php')){
						require('view/body/contenter/'.$a[4].'.php');
					}else{
						echo 'Không tìm thấy file view/body/contenter/'.$a[4].'.php<br>';
					}
				}
			}
			if(isset($a[5])&&$a[5]!=''&&$a[5]!=null){
				if(preg_match('/\@/', $a[5])){
					$b=preg_replace('/(.*)@(.*)/', '$1', $a[5]);
					$c=preg_replace('/(.*)@(.*)/', '$2', $a[5]);
					if(isFile('view/body/footer/'.$b.'/'.$c.'.php')){
						require('view/body/footer/'.$b.'/'.$c.'.php');
					}else{
						if(isFile('view/body/footer/'.$b.'.php')){
							require('view/body/footer/'.$b.'.php');
						}else{
							echo "Không tìm thấy file hiển thị footer trong view/body/footer<br>";
						}
					}
				}else{
					if(isFile('view/body/footer/'.$a[5].'.php')){
						require('view/body/footer/'.$a[5].'.php');
					}else{
						echo 'Không tìm thấy file view/body/footer/'.$a[5].'.php<br>';
					}
				}
			}
		}

		private function update(){
			$this->dataconfigRouter['updateAt']=(new objTime())->get();
			$this->configRouter->save($this->dataconfigRouter);
		}

		private function seach(){
			if($this->fastRouter==null){
				$this->fastRouter = new objFaIDb('fastRouter');
			}
			$this->datafastRouter=$this->fastRouter->get();
			if(isset($this->datafastRouter[$this->uri])){
				if(isset($this->datafastRouter[$this->uri]['error'])){
					switch($this->datafastRouter[$this->uri]['error']){
						case 404:{
							if(isset($this->dataconfigRouter['404'])){
								return $this->dataconfigRouter['404'];
							}else{
								header("HTTP/1.1 404 Not Found");
								die();
							}
							break;
						}
						
					}
				}else{
					if(isset($this->datafastRouter[$this->uri]['f@var'])){
						foreach(array_keys($this->datafastRouter[$this->uri]['f@var']) as $b){
							$GLOBALS['url'][$b]=$this->datafastRouter[$this->uri]['f@var'][$b];
						}
						unset($this->datafastRouter[$this->uri]['f@var']);
					}
					return $this->datafastRouter[$this->uri];
				}
			}else{
				//tìm kiếm chuyên sâu
				if($this->backRouter==null){
					$this->backRouter = new objFaIDb('backRouter');
				}
				$this->databackRouter=$this->backRouter->get();

				$c=0;
				if($this->databackRouter!=null){
					foreach(array_keys($this->databackRouter) as $a){
						if(preg_match($a, $this->uri)){
							$this->datafastRouter[$this->uri]=$this->databackRouter[$a];
							if(isset($this->databackRouter[$a]['f@var'])){
								unset($this->datafastRouter[$this->uri]['f@var']);
								$i=1;
								foreach($this->databackRouter[$a]['f@var'] as $b){
									$GLOBALS['url'][$b]=$this->datafastRouter[$this->uri]['f@var'][$b]=preg_replace($a, '$'.$i, $this->uri);
									$i++;
								}
								unset($this->databackRouter[$a]['f@var']);
							}
							$c=1;
							$this->fastRouter->save($this->datafastRouter);
							break;
						}
					}
				}

				if($c==0){
					$this->datafastRouter[$this->uri]['error']=404;
					$this->fastRouter->save($this->datafastRouter);
					if(isset($this->dataconfigRouter['404'])){
						return $this->dataconfigRouter['404'];
					}else{
						header("HTTP/1.1 404 Not Found");
						die();
					}
				}else{
					return $this->databackRouter[$a];
				}
			}
			//$this->fastRouter = ;
		}

		private function change(){
			//chạy cập nhật file

			//xoá data tất cả các file dataconfigRouter, databackRouter, datafastRouter
			unset($this->dataconfigRouter);
			//cập nhật capacity và version trong configRouter
			$this->dataconfigRouter['capacity']=$this->capacity;
			$this->dataconfigRouter['version']=$this->version;

			//xử lý config trong interFaceRouter

			//sử dụng model và view
			$this->dataconfigRouter['useModel']=$this->interFaceRouter['f@config']['useModel']??1;
			$this->dataconfigRouter['useView']=$this->interFaceRouter['f@config']['useView']??1;

			//chuyển default thành mảng
			$this->default=$this->a($this->interFaceRouter['f@config']['default']);

			//cập nhật các lỗi 404 và 319
			/*
				*anvia*: cái gì ở đây thế này lại a, b ak?
				*anfha*: cho giống JavaScript tý :v
				*anvia*: em không quan tâm :|
			*/
			if(isset($this->interFaceRouter['f@config']['404'])){
				$this->dataconfigRouter['404']=$this->b($this->default, $this->a($this->interFaceRouter['f@config']['404']));
			}
			if(isset($this->interFaceRouter['f@config']['319'])){
				$this->dataconfigRouter['319']=$this->b($this->default, $this->a($this->interFaceRouter['f@config']['319']));
			}
			

			//quét lấy các router
			foreach(array_keys($this->interFaceRouter) as $router){
				if($router!='f@config'){
					/*
						*anvia*: anh định làm gì ở đây?
						*anfha*: làm việc cần làm thôi :)
						*anfha*: kiểm tra xem nó có router con hay không nếu có thì gộp nó vào :)
						*anfha*: à không trước khi đó cần quét xem nó có file cấu hình không nếu có thì làm như ở trên nhìn nhé.
						*anfha*: khó qua thôi anh chạy đệ quy cho nó lành :D
						*anfha*: đầu tiên sẽ quét xem trong còn router không nhé
					*/
					if(isset($this->interFaceRouter[$router]['f@setup'])){
						$configTmp=$this->b($this->default, $this->a($this->interFaceRouter[$router]['f@setup']));
						unset($this->interFaceRouter[$router]['f@setup']);
					}else{
						$configTmp=$this->default;
					}

					if(count($this->interFaceRouter[$router])>0){
						$subRouter=$this->c($router, $this->interFaceRouter[$router], $configTmp);
						
						foreach(array_keys($subRouter) as $subRouterKey){
							$databackRouterTmp[$subRouterKey]=$subRouter[$subRouterKey];
						}
					}else{
						$databackRouterTmp[$router]=$configTmp;
					}
				}
			}

			/*
				*anfha*: anh sẽ cho chạy thử xem nó hoạt động thế nào đã :))
				*anfha*: ok chạy bình thường rồi giời tiến hành chuyển thành regexp thôi :D
			*/

			foreach(array_keys($databackRouterTmp) as $a){

				if($this->dataconfigRouter['useModel']==0){
					$databackRouterTmp[$a][0]="";
				}
				if($this->dataconfigRouter['useView']==0){
					$databackRouterTmp[$a][2]=$databackRouterTmp[$a][3]=$databackRouterTmp[$a][4]=$databackRouterTmp[$a][5]="";
				}

				if($a[-1]!="/"){
					$b=$a.'/';
				}else{
					$b=$a;
				}

				$f=1;
				$e=1;
				do{
					$c="/^";
					for($i=1; $i<=$e; $i++){
						$c.="(.*)\{(.*)\}";
					}
					$c.="(.*)/";
					if(preg_match($c, $b)){
						$d=$c;
						preg_match($c, $b, $g);
						$e++;
					}else{
						$f=0;
					}
				}while($f==1);

				//print_r($g);

				if(--$e>0){
					$kt=$e*2+1;
					$h=0;
					$k='/^';
					for($i=1; $i<=$kt; $i++){
						if($i%2==0){
							if(preg_match('/(.*)@regexp:(.*)/', $g[$i])){
								$m['f@var'][$h]=preg_replace("/^(.*)@regexp:(.*)$/", "$1", $g[$i]);
								$k.='('.preg_replace("/^(.*)@regexp:(.*)$/", "$2", $g[$i]).'*)';
							}elseif(preg_match('/(.*)@number/', $g[$i])){
								$m['f@var'][$h]=preg_replace("/^(.*)@number/", "$1", $g[$i]);
								$k.='('.preg_replace("/^(.*)@number/", "\\d", $g[$i]).'*)';
							}elseif(preg_match('/(.*)@string/', $g[$i])){
								$m['f@var'][$h]=preg_replace("/^(.*)@string/", "$1", $g[$i]);
								$k.='('.preg_replace("/^(.*)@string/", "[A-Za-z0-9_]", $g[$i]).'*)';
							}
							
							$h++;
						}else{
							$k.=preg_replace('/\./', '\\.',substr(json_encode($g[$i]), 1, -1));
						}
					}
					$k.='$/';
					$l[$k]=$databackRouterTmp[$a];
					$l[$k]['f@var']=$m['f@var'];
				}else{
					$l["/^".preg_replace('/\./', '\\.', substr(json_encode($b), 1, -1))."$/"]=$databackRouterTmp[$a];
				}
			}

			if($this->backRouter==null){
				$this->backRouter = new objFaIDb('backRouter');
			}
			$this->backRouter->save($l);
			$this->databackRouter = $l;

			//reset lại fastRouter
			if($this->fastRouter==null){
				$this->fastRouter = new objFaIDb('fastRouter');
			}
			$this->fastRouter->clear();

			//lưu file cấu hình
			$this->dataconfigRouter['updateAt']=(new objTime())->get();

			$this->configRouter->save($this->dataconfigRouter);

		}
	}

	//các tính năng bổ sung cho fa
	if(is_file('app/libFaI2.0.1.php')){
		require 'app/libFaI2.0.1.php';
		//echo "<pre>";
		/*
			*anfha*: nếu truyền vào trong hàm này mày k có 1 thì nó sẽ không check router cái này cần thiết khi đã ổn định rồi thì tắt nó đi để nó chạy nhanh hơn
			*anvia*: có ai đó đọc đoạn hội thoại rồi đúng k?
			*anfha*: ừkm lưu ở đây thì ai chả đọc :v
			*anvia*: thế anh k định để nó ở đâu cho an toàn ạ?
			*anfha*: mình xây dựng mã nguồn mở mà thôi phải cho họ sửa code chứ :)
		*/
		new router($url['uri'], 1);
		//echo "</pre>";
	}else{
		echo 'lỗi 1x000001: Thiếu thư viện bổ sung cho chương trình';
	}
	$t = microtime(true);
	//echo "<br>Thời gian xử lý của server: ".round($t-$s, 4)."s";
	// echo "<pre>";
	// print_r($GLOBALS);
	// echo "</pre>";

	/*
		*anfha*: cuối cùng cũng xong hơn 500 dòng lệnh :)
		*anvia*: mệt anh quá :( định giữ các đoạn hội thoại này cho nhiều dòng à?
		*anfha*: ừkm anh để đây để kỉ niệm này được lưu giữ thật lâu :) và thật nhiều người được biết :) cảm ơn đã debug hộ anh :)
	*/
?>