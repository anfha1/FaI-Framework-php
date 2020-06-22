<a href="/">Trang chủ</a>
<a href="/signup/">Đăng kí</a>
<form method="POST" action="/login/" onsubmit="return function_resLogin();" name="formLogin" id="formLogin">
	<input type="text" name="user" id="user" placeholder="Tài khoản" required>
	<input type="password" name="pass" id="pass" placeholder="Mật khẩu" required>
	<button type="submit" name="goto" value="login">Đăng nhập</button>
</form>