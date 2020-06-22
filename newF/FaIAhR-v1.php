<?php
	function resapi($status=200, $content="json"){
		$status=(string)$status;
		$statusCode=array(
			200 => 'OK',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',

			100 => 'Continue', 101 => 'Switching Protocols',
			201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information',
			204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content',
			300 => 'Multiple Choices', 301 => 'Moved Permanently',
			302 => 'Moved Temporarily', 303 => 'See Other',
			304 => 'Not Modified', 305 => 'Use Proxy',
			400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required',
			405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required',
			408 => 'Request Time-out', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required',
			412 => 'Precondition Failed', 413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large', 415 => 'Unsupported Media Type',
			501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable',
			504 => 'Gateway Time-out', 505 => 'HTTP Version not supported'
		);
		$contentType=array(
			"html" => "text/html",
			"json" => "application/json",
			"xml" => "application/xml",
			"csv" => "text/csv",
			"txt" => "text/plain",
			"javascript" => "text/javascript",
			"js" => "application/javascript",

			"jpge" => "image/jpeg",
			"jpg" => "image/jpeg",
			"othor" => "application/octet-stream",
			"oog" => "application/ogg",
			"pdf" => "application/pdf",
			"xhml" => "application/xhtml+xml",
			"zip" = > "application/zip"

		);
		header("HTTP/1.1 ".$status." " . $statusCode[$status]);
		header("Content-Type: ".$contentType[$content]);
	}

	/**
	//Hướng dẫn sử dụng
	
	//khởi tạo 1 response trả về cho người dùng mặc định là status 200 và type json
	resapi();

	//thay đổi trả về trang không tìm thấy 
	resapi(404);

	//trả về html
	resapi(200, 'html');


	//nếu thay đổi type thì phải nhập thêm cả status code

	//thông tin về status code xem tại biến mảng $statusCode

	//xem thông tin về type xem tại biếng mảng $contentType

	*/
?>