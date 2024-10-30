<?php
//Envía el mensaje SMS
function mdn_sms_envia_sms( $mdnsms_settings, $telefono, $mensaje ) {
	switch ( $mdnsms_settings['servicio'] ) {
		case "mdnsms_esms":
			$respuesta = wp_remote_get( "http://sms.mdnlimited.xyz/smsapi?api_key=" . $mdnsms_settings['clave_mdnsms_esms'] . "&type=text&contacts=" . $telefono . "&senderid=" . $mdnsms_settings['identificador_mdnsms_esms'] . "&msg=" . mdn_sms_codifica_el_mensaje( $mensaje ) );
			break;
		case "mdnsms_dotbd":
			$respuesta = wp_remote_get( "http://sms.mdn.com.bd/smsAPI?sendsms&apikey=" . $mdnsms_settings['apikey_mdnsms_dotbd'] . "&apitoken=" . $mdnsms_settings['apitoken_mdnsms_dotbd'] . "&type=" . $mdnsms_settings['sms_type_mdnsms_dotbd'] . "&from=" . $mdnsms_settings['senderid_mdnsms_dotbd'] . "&to=" . $telefono . "&text=" . mdn_sms_codifica_el_mensaje( $mensaje ) . "&route=0" );
			break;
	}

	if ( isset( $mdnsms_settings['debug'] ) && $mdnsms_settings['debug'] == "1" && isset( $mdnsms_settings['campo_debug'] ) ) {
		$correo	= __( 'Mobile number:', 'mdnsms-sms-notifications-for-woocommerce' ) . "\r\n" . $telefono . "\r\n\r\n";
		$correo	.= __( 'Message: ', 'mdnsms-sms-notifications-for-woocommerce' ) . "\r\n" . $mensaje . "\r\n\r\n"; 
		$correo	.= __( 'Gateway answer: ', 'mdnsms-sms-notifications-for-woocommerce' ) . "\r\n" . print_r( $respuesta, true );
		wp_mail( $mdnsms_settings['campo_debug'], 'WC - MDNSMS SMS Notifications', $correo, 'charset=UTF-8' . "\r\n" ); 
	}
}