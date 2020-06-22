<?php
	/**
	 * Đây là hàm đi cùng FaIDbJ nếu bạn xoá thì hàm bên dưới sẽ bị lỗi
	 * Tính năng 
	 *    Kiểm tra xem dữ liệu vào có phải đang sử dụng cấu trúc Json hay không
	*/
	function isJson($string) {
		return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
	}
	/**
	 * Phiên bản thử nghiệm đang trong quá trình xây dựng có thể gặp lỗi
	 * Đây là FaIDbJ phiên v1
	 * Hàm này dùng để xử lý đọc ghi file Json tự động tạo nếu file chưa tồn tại
	 * Các tính năng
	 *    Đọc file trả về mảng tạo file nếu chưa tồn tại
	 *    Ghi file lưu với mảng hệ thông sẽ tự chuyển thành mảng
	 *    Reset file về dạng rỗng (tuy là rỗng nhưng rỗng với trạng thái json là '{}' vậy nhé lúc nào cũng có 2 ký tự)
	 *    Thay đổi kết nối với file
	 *    Đi kèm theo function kiểm tra cấu trúc json
	 */
	class FaIDbJ{
		protected $a, $b;
		// $a là đường dẫn đến file
		// $b là các tuỳ chọn nâng cao cho file mà người dùng cần
		function __construct($file, $b=2){
			/**
			 * biếm $b là biến dùng để tuỳ biến file hay nói cái khác là tương ứng với $b bên trên
			 * Nếu $b==0 thì hệ thống sẽ để yên và không làm gì cả
			 * Nếu $b==1 thì khi file bạn mở không đúng cấu trúc sẽ bị xoá nội dung bên trong hoặc tự động tạo nếu file không tồn tại
			 * Nếu $b==2 thì khi file bạn mở không đúng cấu trúc sẽ giữu nguyên nội dung bên trong và tự động tạo nếu file không tồn tại
			*/
			$this->a=$file;
			$this->b=$b;
		}
		public function read($isJson=0){
			/**
			 * Hàm này dùng để đọc file và trả về các trang thái của file
			 * $isJson quy định trả về json hay trả về mảng (0) toàn bộ kết quả trả về mảng hay trả về json (1)
			*/
			if(is_file($this->a)){
				//file có tồn tại
				//kiểm tra xem file có dữ liệu hay không
				$dbFaI=filesize($this->a);
				if($dbFaI==0){
					//file hoàn toàn rỗng không có 1 chút gì cả :)
					//oki được sử dụng thôi
					$dbFaI = @fopen($this->a, 'w');
					if($dbFaI){
						fwrite($dbFaI, '{}');
						fclose($dbFaI);
						$rs['status']=true;
						$rs['result']=[];
					}else{
						//tạo file gặp lỗi
						$rs['status']=false;
						$rs['error']="Không thể đọc File #FJ03";
					}
				}else{
					//file này có dữ liệu bên trong nên cần
					//kiểm tra xem file có đúng cấu trúc json nếu có dữ liệu
					//print_r($this->a);
					$res=fread(@fopen($this->a, "r"), $dbFaI);
					if(isJson($res)){
						//đúng là json
						$rs['status']=true;
						$rs['result']=json_decode($res, true);
					}else{
						//không phải là json

						//kiểm tra xem tác vụ nâng cao người dùng có cho phép xoá đi và làm lại hay không
						if($this->b==1){
							//xoá toàn bộ dữ liệu bên trong
							$dbFaI = @fopen($this->a, "w+");
							if($dbFaI){
								fwrite($dbFaI, "{}");
								fclose($dbFaI);
								$rs['status']=true;
								$rs['result']=[];
							}else{
								$rs['status']=false;
								$rs['error']="Không thể ghi File #FJ04";
							}
						}else{
							$rs['status']=false;
							$rs['error']="File của bạn bị lỗi cấu trúc #FJ05";
						}
					}
				}
			}else{
				//kiểm tra xem người dùng có cho phép xử lý nâng cao hay không :)
				if($this->b==0){
					$rs['status']=false;
					$rs['error']="File của bạn không tồn tại #FJ01";
				}else{
					//tạo file nhé vì người dùng yêu cầu tạo file khi file không tồn tại
					$dbFaI = @fopen($this->a, 'w+');
					if($dbFaI){
						//hoàn toàn bình thường
						fwrite($dbFaI, '{}');
						fclose($dbFaI);
						$rs['status']=true;
						$rs['result']=[];
					}else{
						//tạo file gặp lỗi
						$rs['status']=false;
						$rs['error']="Lỗi ghi file! #FJ02";
					}
				}
			}

			return $rs;
		}

		public function clear(){
			if(is_file($this->a)){
				$dbFaI = @fopen($this->a, 'w+');
				if($dbFaI){
					//hoàn toàn bình thường
					fwrite($dbFaI, '{}');
					fclose($dbFaI);
					$rs['status']=true;
				}else{
					//tạo file gặp lỗi
					$rs['status']=false;
					$rs['error']="Lỗi ghi file! #FJ15";
				}
			}else{
				//kiểm tra xem người dùng có cho phép xử lý nâng cao hay không :)
				if($this->b==0){
					$rs['status']=false;
					$rs['error']="File của bạn không tồn tại #FJ13";
				}else{
					//tạo file nhé vì người dùng yêu cầu tạo file khi file không tồn tại
					$dbFaI = @fopen($this->a, 'w+');
					if($dbFaI){
						//hoàn toàn bình thường
						fwrite($dbFaI, '{}');
						fclose($dbFaI);
						$rs['status']=true;
					}else{
						//tạo file gặp lỗi
						$rs['status']=false;
						$rs['error']="Lỗi ghi file! #FJ14";
					}
				}
			}
		}
		
		public function write($array){
			//hàm này sẽ chuyển array thành json và lưu vào file
			if(is_file($this->a)){
				//file có tồn tại
				//có thì cứ ghi k lo gì cả :V mất dữ liệu chết thằng người dùng k chịu check
				$dbFaI = @fopen($this->a, 'w+');
				if($dbFaI){
					//hoàn toàn bình thường
					fwrite($dbFaI, json_encode($array));
					fclose($dbFaI);
					$rs['status']=true;
				}else{
					//tạo file gặp lỗi
					$rs['status']=false;
					$rs['error']="Lỗi ghi file! #FJ08";
				}
			}else{
				//kiểm tra xem người dùng có cho phép xử lý nâng cao hay không :)
				if($this->b==0){
					$rs['status']=false;
					$rs['error']="File của bạn không tồn tại #FJ06";
				}else{
					//tạo file cho người dùng và đẩy dữ liệu vào.
					$dbFaI = @fopen($this->a, 'w+');
					if($dbFaI){
						//hoàn toàn bình thường
						fwrite($dbFaI, json_decode($array));
						fclose($dbFaI);
						$rs['status']=true;
					}else{
						//tạo file gặp lỗi
						$rs['status']=false;
						$rs['error']="Lỗi ghi file! #FJ07";
					}
				}
			}
			return $rs;
		}

		public function check(){
			//hàm này dùng để kiểm tra xem file có đúng định dạng hay không
			if(is_file($this->a)){
				//file có tồn tại
				//có thì cứ ghi k lo gì cả :V mất dữ liệu chết thằng người dùng k chịu check
				//file có tồn tại
				//kiểm tra xem file có dữ liệu hay không
				$dbFaI=filesize($this->a);
				if($dbFaI==0){
					//file hoàn toàn rỗng không có 1 chút gì cả :)
					//oki được sử dụng thôi
					$dbFaI = @fopen($this->a, 'w');
					if($dbFaI){
						fwrite($dbFaI, '{}');
						fclose($dbFaI);
						$rs['status']=true;
					}else{
						//tạo file gặp lỗi
						$rs['status']=false;
						$rs['error']="Không thể đọc File #FJ11";
					}
				}else{
					//file này có dữ liệu bên trong nên cần
					//kiểm tra xem file có đúng cấu trúc json nếu có dữ liệu
					$res=fread(@fopen($this->a, "r"), $dbFaI);
					if(isJson($res)){
						//đúng là json
						$rs['status']=true;
					}else{
						//không phải là json
						$rs['status']=false;
						$rs['error']="File không đúng cấu trúc #FJ12";
					}
				}
			}else{
				//kiểm tra xem người dùng có cho phép xử lý nâng cao hay không :)
				if($this->b==0){
					$rs['status']=false;
					$rs['error']="File của bạn không tồn tại #FJ10";
				}else{
					//tạo file cho người dùng và đẩy dữ liệu vào.
					$dbFaI = @fopen($this->a, 'w+');
					if($dbFaI){
						//hoàn toàn bình thường
						fwrite($dbFaI, '{}');
						fclose($dbFaI);
						$rs['status']=true;
					}else{
						//tạo file gặp lỗi
						$rs['status']=false;
						$rs['error']="File không thể ghi! #FJ09";
					}
				}
			}
		}
	}
	
	/**
	 * Hướng dẫn sử dụng thư viện FaIDbJ v1
	//khai báo và sử dụng
	$fileJson = new FaIDbJ('đường/dẫn-đền-file.json');
	
	//khai báo nâng cao
	$n=0 //file không được tạo mới nếu không tồn tại, file không được reset nếu không đúng định dạng
	$n=1 //file được tạo mới nếu không tồn tại, file sẽ được reset nếu không đúng định dang
	$n=2 //mặc định file sẽ sử dụng dạng này, tự động tạo file nếu chưa tồn tại, file sẽ không reset nếu lỗi cấu trúc
	$fileJson1 = new FaIDbJ('đường/dẫn-đền-file.json', $n);

	//kiểm tra xem file như thế nào
	$fileJson->check()

	//làm rỗng cấu trúc file
	$fileJson->clear()

	*/
?>