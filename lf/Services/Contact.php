<?php

/**
 * Contacts: Service de Contacts
 * @link http://lf.goodsenses.net/fw/services/Location
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 01/03/2013
 * @modified 
 */

namespace lf\Services;

class Contact extends Service {
	
	public function email($to, $from, $subject, $message, $format = 'text', $attached = null) {
		//echo "Email envoyé";
		
		$default_headers = array(
			'MIME-Version' => '1.0',
			'X-Priority' => '3',
			'X-Sender' => SITE_ADDRESS,
			'X-Mailer' =>"PHP ". phpversion(),
		);

		if( 'html' == $format ) {
			$default_headers['Content-type']  = 'text/html; charset=iso-8859-1';
		} else {
			$default_headers['Content-type']  = 'text/plain; charset=iso-8859-1';
			$message = wordwrap($message, 70, "\r\n");
		}

		if( !is_array( $from ) ) {
			$from = array( 'From' => $from );
		}

		$default_headers['Reply-To'] = $from['From'];
		$default_headers['Return-Path'] = $from['From'];

		$from = array_merge( $default_headers , $from );

		$headers = "";
		foreach ($from as $key => $value) {
			if( "" != $headers ) $headers .= "\r\n";
			$headers .= $key .": ".$value;
		}
		
		return mail( $to, $subject, $message, $headers );

	}
	
	public function sms($telephone, $message) {
		//echo "SMS envoyé";
	}
	
}