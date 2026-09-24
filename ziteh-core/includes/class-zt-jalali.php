<?php
/**
 * Jalali (Persian / Shamsi) calendar helpers.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Jalali
 */
class ZT_Jalali {

	/**
	 * Month names.
	 *
	 * @var string[]
	 */
	public static $months = array( 1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );

	/**
	 * Gregorian -> Jalali.
	 *
	 * @param int $gy Year.
	 * @param int $gm Month.
	 * @param int $gd Day.
	 * @return int[] [jy, jm, jd]
	 */
	public static function to_jalali( $gy, $gm, $gd ) {
		$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
		$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
		$days  = 355666 + ( 365 * $gy ) + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 ) + $gd + $g_d_m[ $gm - 1 ];
		$jy    = -1595 + ( 33 * (int) ( $days / 12053 ) );
		$days %= 12053;
		$jy   += 4 * (int) ( $days / 1461 );
		$days %= 1461;
		if ( $days > 365 ) {
			$jy  += (int) ( ( $days - 1 ) / 365 );
			$days = ( $days - 1 ) % 365;
		}
		if ( $days < 186 ) {
			$jm = 1 + (int) ( $days / 31 );
			$jd = 1 + ( $days % 31 );
		} else {
			$jm = 7 + (int) ( ( $days - 186 ) / 30 );
			$jd = 1 + ( ( $days - 186 ) % 30 );
		}
		return array( $jy, $jm, $jd );
	}

	/**
	 * Jalali -> Gregorian.
	 *
	 * @param int $jy Year.
	 * @param int $jm Month.
	 * @param int $jd Day.
	 * @return int[] [gy, gm, gd]
	 */
	public static function to_gregorian( $jy, $jm, $jd ) {
		$jy  += 1595;
		$days = -355668 + ( 365 * $jy ) + ( (int) ( $jy / 33 ) * 8 ) + (int) ( ( ( $jy % 33 ) + 3 ) / 4 ) + $jd + ( ( $jm < 7 ) ? ( $jm - 1 ) * 31 : ( ( $jm - 7 ) * 30 ) + 186 );
		$gy   = 400 * (int) ( $days / 146097 );
		$days %= 146097;
		if ( $days > 36524 ) {
			$gy  += 100 * (int) ( --$days / 36524 );
			$days %= 36524;
			if ( $days >= 365 ) {
				$days++;
			}
		}
		$gy  += 4 * (int) ( $days / 1461 );
		$days %= 1461;
		if ( $days > 365 ) {
			$gy  += (int) ( ( $days - 1 ) / 365 );
			$days = ( $days - 1 ) % 365;
		}
		$gd    = $days + 1;
		$leap  = ( ( 0 === $gy % 4 && 0 !== $gy % 100 ) || 0 === $gy % 400 );
		$sal_a = array( 0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
		for ( $gm = 0; $gm < 13 && $gd > $sal_a[ $gm ]; $gm++ ) {
			$gd -= $sal_a[ $gm ];
		}
		return array( $gy, $gm, $gd );
	}

	/**
	 * Format a timestamp in Jalali. Supports: Y y m n d j F H i s.
	 * Digits are converted to Persian (when enabled).
	 *
	 * @param string $format    Format.
	 * @param int    $timestamp Unix timestamp.
	 * @return string
	 */
	public static function date( $format, $timestamp ) {
		$tz = wp_timezone();
		$dt = ( new DateTimeImmutable( '@' . (int) $timestamp ) )->setTimezone( $tz );
		list( $jy, $jm, $jd ) = self::to_jalali( (int) $dt->format( 'Y' ), (int) $dt->format( 'n' ), (int) $dt->format( 'j' ) );
		$map = array(
			'Y' => $jy,
			'y' => substr( (string) $jy, -2 ),
			'm' => sprintf( '%02d', $jm ),
			'n' => $jm,
			'd' => sprintf( '%02d', $jd ),
			'j' => $jd,
			'F' => self::$months[ $jm ],
			'H' => $dt->format( 'H' ),
			'i' => $dt->format( 'i' ),
			's' => $dt->format( 's' ),
		);
		$out = '';
		$len = strlen( $format );
		for ( $i = 0; $i < $len; $i++ ) {
			$c = $format[ $i ];
			if ( '\\' === $c && $i + 1 < $len ) {
				$out .= $format[ ++$i ];
				continue;
			}
			$out .= isset( $map[ $c ] ) ? $map[ $c ] : $c;
		}
		return zt_fa( $out );
	}

	/**
	 * Parse a Jalali date string "1403/02/23" (Persian or Latin digits) to a timestamp.
	 *
	 * @param string $str Date.
	 * @return int|false
	 */
	public static function parse( $str ) {
		$str = zt_en( trim( (string) $str ) );
		if ( ! preg_match( '#^(\d{4})[/\-.](\d{1,2})(?:[/\-.](\d{1,2}))?(?:\s+(\d{1,2}):(\d{2}))?#', $str, $m ) ) {
			return false;
		}
		$jd = isset( $m[3] ) && '' !== $m[3] ? (int) $m[3] : 1;
		list( $gy, $gm, $gd ) = self::to_gregorian( (int) $m[1], (int) $m[2], $jd );
		$h  = isset( $m[4] ) ? (int) $m[4] : 0;
		$mi = isset( $m[5] ) ? (int) $m[5] : 0;
		$dt = new DateTimeImmutable( sprintf( '%04d-%02d-%02d %02d:%02d:00', $gy, $gm, $gd, $h, $mi ), wp_timezone() );
		return $dt->getTimestamp();
	}
}
