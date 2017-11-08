<?php
/**
 * 各种加密工具的使用方法
 * ================================================================
 * ================================================================
 */

header('Content-type:text/html;charset=utf8');
date_default_timezone_set("Asia/Shanghai");
error_reporting(E_ALL ^ E_NOTICE);
//导入类库
require_once(dirname(__FILE__).'/util/CryptAES.class.php');
require_once(dirname(__FILE__).'/util/Crypt3DES.class.php');
require_once(dirname(__FILE__).'/util/CRyptRSA.class.php');
require_once(dirname(__FILE__).'/util/util.php');

//检测mcrypt模块，查看支持哪些加密算法和模式
$cipher_list = mcrypt_list_algorithms();//mcrypt支持的加密算法列表（其中rijndael-128，rijndael-192，rijndael-256都是AES算法。）
$mode_list = mcrypt_list_modes(); //mcrypt支持的加密模式列表
echo "<br>服务器mcrypt支持的加密算法：".implode('、', $cipher_list);
echo "<br>服务器mcrypt支持的加密模式：".implode('、', $mode_list);

//常用加密方法
$getPost = $_REQUEST;//表单提交的参数
$encryptResult = '';//加密结果
$decryptResult = '';//解密结果
switch ($getPost['encrypt_type']) {
	case 'RSA': //RSA加密解密
		$pubKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDMz1uRS++hrrog9IlE77/yDqiS
zzbfBc6ICsjs6wnE9eT9G3oivXBRTjds7nGZ6isvrFGbJPs8cNUD7neZ/k+ZkbLq
Q0w7p+Yp1J36r/kqL7UlhQumK6t78rNUcheCqls2ygn+RixqVMFQztNoFDd+KXsz
t4lXNGVvhvFwbeNi3wIDAQAB
-----END PUBLIC KEY-----';
		$priKey = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDMz1uRS++hrrog9IlE77/yDqiSzzbfBc6ICsjs6wnE9eT9G3oi
vXBRTjds7nGZ6isvrFGbJPs8cNUD7neZ/k+ZkbLqQ0w7p+Yp1J36r/kqL7UlhQum
K6t78rNUcheCqls2ygn+RixqVMFQztNoFDd+KXszt4lXNGVvhvFwbeNi3wIDAQAB
AoGAR1ycyCSQS2KpIeByj92FoN6wp+6hbNYGH2q6lapTjhgrgHF5fe9J2lqJf2AZ
nwpzn7nH+WnKTEX4QtVtQO/WZclHkIFP5XIOmHLY/A6jHb6tKm4G2QMUt/5UIaqC
8TgE1P9BXNOjcXNzG385T4r5SZJQnck21X3NHgMjtFflHWECQQDl+1FgRoR6nZEA
FnKIRD2i8ZQadGjqJ7mGip4u2NmOhk/LXdrnz7PX8Y9vNuV8tx2q1qeo/HMGeJhr
7d0GgorxAkEA4/sFkPctgg9FK3b8VmXk9mHotpMrV32xjLPn9AWiSv7DdJWO/iuB
4yvxX+qNMa+z/IcEPTm1xUD1GlgwoSGqzwJAYR+yrSL6vcGfQ9c3BT80fITjVAzH
ZePi4OPXi9c+gYdNWYhIc09vFwpH1eLsJbA7kjNW2PHMLfZuAF6S0jX9MQJAdAJ3
eaYeFTG6C/0XOMvO8AUwiz9mbbX7VFLz9HutcyYixb+ZLQNsq/HfeOR9BsyW9Sir
YpBsS7zbPJWl4UilhwJBAI3gX2+mMjM98ua+ntFa2UiqZNbNRRRcJUn1sTrRIdb4
uVqzLUFwsiJx4PFfahvqDUIDq4fMewAH/aVxtBsBpo0=
-----END RSA PRIVATE KEY-----';
		$rsa = new CryptRSA($pubKey, $priKey);
		$encryptResult = $rsa->encrypt($getPost['string']);
		$decryptResult = $rsa->decrypt($encryptResult);
		/* RSA同时还支持数字签名 */
		$data = array();
		$data['username'] = 'zzb';
		$data['age'] = '27';
		/* 生成签名 */
		//1、去除数组中的空值和签名字段(sign/sign_type)
		$data = array_filter($data);
		//2、按 ascii 码升序排序
		ksort($data);
		//3、按照“参数=参数值”的模式用“&”字符拼接成字符串
		$preStr = createLinkstring($data);//得到最终需要签名的字符串
		//4、私钥加密，并使用base64
		$sign = $rsa->sign($preStr);
		echo "<br><br>关于RSA的数字签名：";
		echo "<br>签名：".$sign;
		/* 验签 */
		$mySign = 'QRnnwj1PzbHSHCfAu464Qdnk+YMAeTmpirmQByJ5ndsxWa2zxalpWZUbKoU3eyDttYWB4NJWPHymloNGNsLfHHjmq7QLzTEOp8oP/F6j64dYlGT8aVeI2zucILww4tMP15oBTAuT0IloAZRvw7Vqb5G6MTStytHfKSlILNaUvgY=';
		echo "<br>验签：";
		var_dump($rsa->verify($preStr, $mySign));
		break;
	case 'AES': //AES加密解密
		$aes = new CryptAES($getPost['crypt_key'], $getPost['crypt_iv']);
		$encryptResult = $aes->encrypt($getPost['string']);
		$decryptResult = $aes->decrypt($encryptResult);
		break;
	case '3DES': //3DES加密解密
		$des = new Crypt3DES($getPost['crypt_key'], $getPost['crypt_iv']);
		$encryptResult = $des->encrypt($getPost['string']);
		$decryptResult = $des->decrypt($encryptResult);
		break;
	default:
		//echo '<br>请提交加密类型。';
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>常用加密工具</title>
	<link rel="stylesheet" href="">
</head>
<body>
	<div>
		<h1>常用加密方法</h1>
		<form action="" method="get" accept-charset="utf-8">
			<select name="encrypt_type">
				<option value="RSA" <?php if ($getPost['encrypt_type'] == 'RSA') {echo "selected=\"selected\"";}?>>RSA加密</option>
				<option value="AES" <?php if ($getPost['encrypt_type'] == 'AES') {echo "selected=\"selected\"";}?>>AES加密</option>
				<option value="3DES" <?php if ($getPost['encrypt_type'] == '3DES') {echo "selected=\"selected\"";}?>>3DES加密</option>
			</select><br>
			<label for="string">原字符串：</label>
			<input id="string" type="text" name="string" value="<?php echo $getPost['string']? $getPost['string']: 'root1234';?>"><br>
			<label for="crypt_key">加密key：</label>
			<input id="crypt_key" type="text" name="crypt_key" value="<?php echo $getPost['crypt_key']? $getPost['crypt_key']: '1234567890123456';?>">（RSA加密无须填写）<br>
			<label for="crypt_iv">加密向量iv：</label>
			<input id="crypt_iv" type="text" name="crypt_iv" value="<?php echo $getPost['crypt_iv']? $getPost['crypt_iv']: '1234567890123456';?>">（RSA加密无须填写）<br>
			<input type="submit" value="提交"><br>
			<div>加密后字符串：<?php echo $encryptResult; ?></div>
			<div>解密后字符串：<?php echo $decryptResult; ?></div>
		</form>
	</div>
</body>
</html>