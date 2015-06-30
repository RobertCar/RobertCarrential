<?php
class ImageHelper
{
	private static $config = null;
	
	private static $convert = '';
	private static $composite = '';
	private static $exiv2 = '';
	
	private function __construct() {}
	
	public static function setConfig($config) {
		self::$config = $config;
	}

	public static function resize($origFile, $destFile, $size) {
		$convert = self::getConvert();
		$cmd = "\"{$convert}\" -strip -scale {$size} \"{$origFile}\" \"{$destFile}\"";
		exec($cmd, $output, $return);
		if ($return) {
			return $cmd;
		} else {
			return 0;
		}
	}
	
	public static function sample($origFile, $destFile, $size) {
		$convert = self::getConvert();
		$cmd = "\"{$convert}\" -strip -sample {$size} \"{$origFile}\" \"{$destFile}\"";
		exec($cmd, $output, $return);
		if ($return) {
			return $cmd;
		} else {
			return 0;
		}
	}
	
	public static function crop($origFile, $destFile, $newDimensions, $startPos, $newSize=null){
		$convert = self::getConvert();
		$scaleInfo = "";
		if($newSize !== null){
			 $scaleInfo = "-scale {$newSize} ";
		}
		$cmd = "\"{$convert}\" -strip -crop {$newDimensions}+{$startPos['x']}+{$startPos['y']} {$scaleInfo}\"{$origFile}\" \"{$destFile}\"";
		exec($cmd, $output, $return);
		if ($return) {
			return $cmd;
		} else {
			return 0;
		}
	}
	
	public static function watermark($file, $watermarkFile, $pos='', $transparent='75') {
		$composite = self::getComposite();
		if (empty($pos)) {
			$pos = 'SouthEast';
		}
		$t1 = 100-$transparent;
		$t = "{$transparent}x{$t1}";
		$cmd = "\"{$composite}\" -strip -gravity {$pos} -blend {$t} \"{$watermarkFile}\" \"{$file}\" \"{$file}\"";
		exec($cmd, $output, $return);
		if ($return) {
			return $cmd;
		} else {
			return 0;
		}
	}
	
	public static function getExif($file) {
		$exiv2 = self::getExiv2();
		$cmd = "\"{$exiv2}\" \"{$file}\"";
		$output = array();
		$status = 0;
		@exec($cmd, $output, $status);
		$exif = array();
		if ($status != 0) {
			return $exif;
		}
		$exif['make'] = self::getExifFieldDataFromArray($output, 4);
		$exif['model'] = str_replace("{$exif['make']} ", '', self::getExifFieldDataFromArray($output, 5));
		$exif['time'] = self::getExifFieldDataFromArray($output, 6);
		$exif['tv'] = str_replace(' s', '', self::getExifFieldDataFromArray($output, 8));
		$exif['av'] = str_replace('F', '', self::getExifFieldDataFromArray($output, 9));
		$exif['ev'] = str_replace(' EV', '', self::getExifFieldDataFromArray($output, 10));
		$exif['focalLength'] = str_replace(' ', '', self::getExifFieldDataFromArray($output, 13));
		$exif['iso'] = self::getExifFieldDataFromArray($output, 15);
		$exif['ep'] = self::getExifFieldDataFromArray($output, 16);
		$exif['wb'] = self::getExifFieldDataFromArray($output, 21);
		return $exif;
	}
	
	private static function getExifFieldDataFromArray($array, $index) {
		if (array_key_exists($index, $array)) {
			return self::getExifFieldValue($array[$index]);
		} else {
			return null;
		}
	}
	
	private static function getExifFieldValue($string) {
		if (($start = strpos($string, ':'))===false) {
			return null;
		}
		$val = trim(substr($string, $start+1));
		return $val;
	}
	
	public static function normalizePath($path)
	{
		return rtrim($path, '/\\').DIRECTORY_SEPARATOR;
	}
	
	private static function getConvert() {
		if (empty(self::$convert)) {
			self::$convert = self::$config['convert'];
		}
		return self::$convert;
	}
	
	private static function getComposite() {
		if (empty(self::$composite)) {
			self::$composite = self::$config['composite'];
		}
		return self::$composite;
	}
	
	private static function getExiv2() {
		if (empty(self::$exiv2)) {
			self::$exiv2 = self::$config['exiv2'];
		}
		return self::$exiv2;
	}
}
