<?php
namespace common\helpers;

class GeoHelper
{
	/**
	 * Получить страну по ip текущего пользователя
	 *
	 * @return string | null
	 */
	public static function getUserCountryCode()
	{
		try {
			$ip = \Yii::$app->geoip->ip();
			
			return $ip->isoCode;
		} catch (\Throwable $exception) {
			return null;
		}
	}
	
	/**
	 * @return int
	 */
	public static function getUserCountryId()
	{
		$code = self::getUserCountryCode();
		if (!$code) {
			return 1;
		}
		switch ($code) {
			case 'JP':
				return 229;
			case 'EE':
				return 14;
			case 'BY':
				return 3;
			case 'KZ':
				return 4;
			case 'CZ':
				return 215;
			case 'NL':
				return 139;
			case 'PL':
				return 160;
			default:
				return 1;
		}
		return 1;
	}
}