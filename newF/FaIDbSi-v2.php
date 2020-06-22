<?php
	/**
	 * Phiên bản thử nghiệm đang trong quá trình xây dựng có thể gặp lỗi
	 * Sử dụng require để sử dụng hoặc copy nội dung file vào file cửa bạn
	 * Đây là FaIDbSi phiên v2
	 * Hàm này dùng để xử lý kết nối và truy vấn sql sử dụng bảng mã utf-8 unicode ci
	 * So sánh v1 và v2
	 *    Chuyển đổi từ hàm thường sang đối tương dễ dàng hơn trong việc tổ chức và quản lý
	 *    Thêm các tính năng thay đổi kết nối, check kết nối
	 *    Tự thay đổi cho phù hợp với 2 phiên bản php query mysql cũ và mới
	 *    Thêm tính năng validate để xét trường hợp sql injection
	 *    Thêm đa dạng kết quả trả về có 2 lựa chọn trả về mảng và trả về Json
	 * Các tính năng và hướng dẫn sử dụng xem comment ở cuối file
	 */
	$s = microtime(true);
	class FaIDbSi{
		protected $a, $b, $c, $d, $e, $f, $g;
		public function __construct($a, $b, $c, $d){
			/**
			 * $a là host của máy chủ mysql
			 * $b là username đăng nhập vào máy chủ
			 * $c là mật khẩu của username
			 * $d là database mà người dùng lựa chọn
			*/

			//lưu vào biến xử lý
			$this->a=$a;
			$this->b=$b;
			$this->c=$c;
			$this->d=$d;
		}

		public function checkConnect($a=0){
			//hàm này dùng để kiểm tra trạng thái connect với database
			$rs=$this->Connect();
			if($rs['status']!=0){
				unset($rs);
				$this->closeConnect();
				$rs['status']=1;
			}
			if($a==1){
				return json_encode($rs);
			}else{
				return $rs;
			}
		}

		public function validate($string){
			$rs=$this->Connect();
			if($rs['status']==0){
				return false;
			}else{
				if($this->e==0||function_exists('mysqli_connect')){
					//xác thực theo chuẩn mới
					$rss = mysqli_real_escape_string($this->f, $string);
				}else{
					//xác thực theo chuẩn cũ
					$rss = mysql_real_escape_string($this->f, $string);
				}
				$this->closeConnect();
				return $rss;
			}
		}

		public function query($a, $b=0){
			$rs=$this->Connect();
			if($rs['status']==0){
				return $rs;
			}else{
				unset($rs);
				//thực hiện các truy vẫn ở đây
				if($this->e==0||function_exists('mysqli_connect')){
					//đây là chuẩn mới
					$rs_tmp=$this->f->query($a);
					if(!empty($this->f->error)){
						//lỗi truy vẫn
						$rs['status']=0;
						$rs['error']=$this->f->error;
					}else{
						//kết quả trả về :)
						$rs['status']=1;
						//kiểm tra xem có id không nếu có trả về cùng
						if(isset($this->f->insert_id)){
							$rs['id']=$this->f->insert_id;
						}
						//đưa toàn bộ kết quả vào biến result
						if(isset($rs_tmp->num_rows)){
							if($rs_tmp->num_rows<1){
								$rs['amount']=0;
							}else{
								$rs['amount']=$rs_tmp->num_rows;
								for($i=0; $i<$rs['amount']; $i++){
									$rs['result'][$i]=$rs_tmp->fetch_assoc();
								}
							}
						}else{
							$rs['amount']=0;
						}
					}
				}else{
					//đây là chuẩn cũ
					$rs['status']=1;
					$rs_tmp=mysql_query($a, $this->f);
					$rs['error']=mysql_error();
					$rs['id']=mysql_insert_id();
					$i=0;
					while($result = mysql_fetch_assoc($query)){
						$rs['result'][$i]=$result;
						$i++;
					}
					$rs['amount']=$i;
					//truy vấn xong kiểm tra dữ liệu
				}
				$this->closeConnect();
				$rs['sql']=$a;

				if($b==1){
					return json_encode($rs);
				}else{
					return $rs;
				}
			}
		}

		private function closeConnect(){
			if($this->e==0||function_exists('mysqli_connect')){
				//tắt theo chuẩn mới
				$this->f->close();
			}else{
				//tắt theo chuẩn cũ
				mysql_close($this->f);
			}
		}

		private function Connect(){
			//hàm này dùng để kiểm tra việc kết nối với database
			if($this->e==0||function_exists('mysqli_connect')){
				$this->e=0;
				$this->f=mysqli_connect($this->a, $this->b, $this->c, $this->d);
				if(!empty($this->f->connect_error)){
					//kết nối không thành công
					$rs['status']=0;
					$rs['error']=$this->f->connect_error;
				}else{
					//kết nối thành công sẽ quy định mã hoá utf-8
					$this->f->set_charset('UTF8');
					$rs['status']=1;
				}
			}else{
				$this->e=1;
				//kết nối theo chuẩn cũ
				$this->f = mysql_connect($this->a, $this->b, $this->c, $this->d);
				if($this->f){
					//kết nối thành công
					mysql_set_charset('utf8', $this->f);
					$rs['status']=1;
				}else{
					//kết nối không thành công
					$rs['status']=0;
					$rs['error']=mysql_error();
				}
			}
			return $rs;
		}

		public function changeDb($d){
			//hàm này dùng để đổi sang 1 Db khác trong host
			$this->d=$d;
		}

		public function changeConnect($a, $b, $c, $d){
			//hàm này dùng để đổi sang kết nối với database khác
			$this->a=$a;
			$this->b=$b;
			$this->c=$c;
			$this->d=$d;
		}
	}

	function compareTriplets($a, $b) {
	    $la=count($a);
	    // $lb=count($b);
	    // $AML=($la==$lb?$la:($la<$lb?$lb:$la));

	    for($i=0; $i<$la; $i++){
	        switch($a[$i]<=>$b[$i]){
	            case -1: {
	                $rs[1]=(empty($rs[1])?0:$rs[1])+1;
	                break;
	            }
	            case 1: {
	                $rs[0]=(empty($rs[0])?0:$rs[0])+1;
	                break;
	            }
	        }
	    }
	    return $rs;

	}

	// $db_faiDbSi = new FaIDbSi("localhost", "root", "", "faiso");
	// header("HTTP/1.1 200 OK");
	// header("Content-Type: application/json");
	// print_r($db_faiDbSi->query("SELECT * FROM news"));

	// $t = microtime(true);
	// echo "<br>Thời gian xử lý của server: ".round($t-$s, 4)."s";

	/* Hướng dẫn sử dụng cơ bản tính năng

	//kết nối đến cơ sở dữ liệu
	$db_faiDbSi = new FaIDbSi("localhost", "root", "password", "database");

	//kiểm tra kết nối
	print_r($db_faiDbSi->checkConnect());

	//kiểm tra kết nối và trả về Json
	header("HTTP/1.1 200 OK");
	header("Content-Type: application/json");
	print_r($db_faiDbSi->checkConnect(1));

	//thay đổi database
	db_faiDbSi->changeDb("database1");

	//thay đổi toàn bộ kết nối
	db_faiDbSi->changeConnect("127.0.0.1", "admin", "password", "database");

	//validate 1 biến trước khi đưa vào truy vấn
	db_faiDbSi->validate("test'");
	//kết quả sẽ trả về "test\'"

	//truy vẫn T-sql
	print_r(db_faiDbSi->query("SELECT * FROM tbl_data"));

	//trả kết quả truy vấn về dạng Json
	header("HTTP/1.1 200 OK");
	header("Content-Type: application/json");
	print_r(db_faiDbSi->query("SELECT * FROM tbl_data", 1));

	//cảm ơn các bạn đã đọc document mong các bạn sẽ sử dụng tính năng này nhiều hơn!
	*/
?>