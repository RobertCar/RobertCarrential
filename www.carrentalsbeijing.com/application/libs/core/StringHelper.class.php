<?php
class StringHelper {
	
	private static $cryptKey;
	
	private function __construct() {}
	
	/**
	 * 设置加密所用的key
	 * @param string $key
	 */
	public static function setCryptKey($key) {
		self::$cryptKey = $key;
	}
	
	/**
	 * 生成加密后的数组字符串
	 * @param array $data k/v形式的数组
	 * @param string $key 加密所用的key
	 * @return string|NULL base64编码的字串。若传递可能需要urlencode
	 */
	public static function crypt(array $data, $key=null) {
		if (empty($key)) {
			$key = self::$cryptKey;
		}
		$size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($size, MCRYPT_DEV_RANDOM);
		$info = json_encode($data);
		$enc = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $info, MCRYPT_MODE_CBC, $iv);
		$sig = md5($enc . $iv . $key);
		$token = base64_encode(sprintf("%s\n%s\n%s", base64_encode($enc), base64_encode($iv), $sig));
		return $token;
	}
	
	/**
	 * 解码加密信息字串
	 * @param string $string 原始base64编码的认证信息字串
	 * @param string $key 加密时所用的key
	 * @return NULL|array 成功则返回k/v形式的数组，否则返回null
	 */
	public static function decrypt($string, $key=null) {
		if (empty($string)) {
			return null;
		}
		if (empty($key)) {
			$key = self::$cryptKey;
		}
		$token = base64_decode($string);
		list($enc, $iv, $verify) = explode("\n", $token);
		if (empty($enc) || empty($iv) || empty($verify)) {
			return null;
		}
		$enc = base64_decode($enc);
		$iv = base64_decode($iv);
		$sig = md5($enc . $iv . $key);
		if ($sig != $verify) {
			return null;
		}
		$info = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $key ,$enc ,MCRYPT_MODE_CBC, $iv));
		return json_decode($info, true);
	}

	public static function strEndsWith($str, $needle)
	{
		if ( ($nl=strlen($needle)) > ($sl=strlen($str)) )
			return false;
		if ( ($needle == $str) && !empty($needle) )
			return true;
		return strpos($str, $needle, $sl-$nl)!==false;
	}

	public static function strStartsWith($str, $needle)
	{
		if ( ($len = strlen($needle)) > strlen($str) )
			return false;
		if ( ($needle == $str) && !empty($needle) )
			return true;
		return strncmp($str, $needle, $len)==0;
	}
	
	public static function getRandomString($length, $numeric = 0)
	{
		if($numeric)
		{
			$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
		}
		else
		{
			$hash = '';
			$chars = 'ABCHIJghijklmnopqrs456DEFG789tuvwKLMNOPQRSXYZ0123abcdefTUVWxyz';
			$max = strlen($chars) - 1;
			for($i = 0; $i < $length; $i++)
			{
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}
	public static function checkUtf8($str) {
		$len = strlen($str);
		for($i = 0; $i < $len; $i++){
			$c = ord($str[$i]);
			if ($c > 128) {
				if (($c > 247)) return false;
				elseif ($c > 239) $bytes = 4;
				elseif ($c > 223) $bytes = 3;
				elseif ($c > 191) $bytes = 2;
				else return false;
				if (($i + $bytes) > $len) return false;
				while ($bytes > 1) {
					$i++;
					$b = ord($str[$i]);
					if ($b < 128 || $b > 191) return false;
					$bytes--;
				}
			}
		}
		return true;
	}
	public static function isMobileNo($no) {
		return preg_match('#^(13|14|15|16|17|18)[0-9]{9}$#isU', $no);
	}
}

/* vim: set ts=4 sw=4 ff=unix: */
