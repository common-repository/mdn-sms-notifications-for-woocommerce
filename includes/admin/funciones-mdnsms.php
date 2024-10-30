<?php
//Definimos las variables
$mdn_sms = array( 	
	'plugin' 		=> 'SMS Notifications for WooCommerce', 
	'plugin_uri' 	=> 'https://mdn.com.bd/plugin', 
	'donacion' 		=> 'https://bit.ly/bulksmsmdn',
	'soporte' 		=> 'https://mdnlimited.xyz/supporttickets.php',
	'plugin_url' 	=> 'https://mdn.com.bd/plugin', 
	'ajustes' 		=> 'admin.php?page=mdn_sms', 
	'puntuacion' 	=> 'https://wordpress.org' 
);

//Carga el idioma
load_plugin_textdomain( 'mdnsms-sms-notifications-for-woocommerce', null, dirname( DIRECCION_mdn_sms ) . '/languages' );

//Carga la configuración del plugin
$mdnsms_settings = get_option( 'mdnsms_settings' );

//Enlaces adicionales personalizados
function mdn_sms_enlaces( $enlaces, $archivo ) {
	global $mdn_sms;

	if ( $archivo == DIRECCION_mdn_sms ) {
		$enlaces[] = '<a href="' . $mdn_sms['donacion'] . '" target="_blank" title="' . __( 'If you interested in this plugins service, buy now our sms package', 'mdnsms-sms-notifications-for-woocommerce' ) . 'MDNSMS"><span class="genericon genericon-cart"></span></a>';
		$enlaces[] = '<a href="'. $mdn_sms['plugin_url'] . '" target="_blank" title="' . $mdn_sms['plugin'] . '"><strong class="codeforhostinc">mdnSMS</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/mdreamnetwork" title="' . __( 'Follow us on ', 'mdnsms-sms-notifications-for-woocommerce' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/mdreamnetwork" title="' . __( 'Follow us on ', 'mdnsms-sms-notifications-for-woocommerce' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://www.linkedin.com/company/mdn2018/about/" title="' . __( 'Follow us on ', 'mdnsms-sms-notifications-for-woocommerce' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[] = '<a href="HTTPS://mdn.com.bd/plugin" title="' . __( 'More plugins on ', 'mdnsms-sms-notifications-for-woocommerce' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<a href="mailto:help@mdn.com.bd" title="' . __( 'Contact with us by ', 'mdnsms-sms-notifications-for-woocommerce' ) . 'e-mail"><span class="genericon genericon-mail"></span></a> ';
		$enlaces[] = mdn_sms_plugin( $mdn_sms['plugin_uri'] );
	}

	return $enlaces;
}
add_filter( 'plugin_row_meta', 'mdn_sms_enlaces', 10, 2 );

//Añade el botón de configuración
function mdn_sms_enlace_de_ajustes( $enlaces ) { 
	global $mdn_sms;

	$enlaces_de_ajustes = array( 
		'<a href="' . $mdn_sms['ajustes'] . '" title="' . __( 'Settings of ', 'mdnsms-sms-notifications-for-woocommerce' ) . $mdn_sms['plugin'] .'">' . __( 'Settings', 'mdnsms-sms-notifications-for-woocommerce' ) . '</a>', 
		'<a href="' . $mdn_sms['soporte'] . '" title="' . __( 'Support of ', 'mdnsms-sms-notifications-for-woocommerce' ) . $mdn_sms['plugin'] .'">' . __( 'Support', 'mdnsms-sms-notifications-for-woocommerce' ) . '</a>' 
	);
	foreach( $enlaces_de_ajustes as $enlace_de_ajustes )	{
		array_unshift( $enlaces, $enlace_de_ajustes );
	}

	return $enlaces; 
}
$plugin = DIRECCION_mdn_sms; 
add_filter( "plugin_action_links_$plugin", 'mdn_sms_enlace_de_ajustes' );

//Obtiene toda la información sobre el plugin
function mdn_sms_plugin( $nombre ) {
	global $mdn_sms;
	
	$argumentos	= ( object ) array( 
		'slug'		=> $nombre 
	);
	$consulta	= array( 
		'action'	=> 'plugin_information', 
		'timeout'	=> 15, 
		'request'	=> serialize( $argumentos )
	);
	$respuesta	= get_transient( 'mdn_sms_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_post( 'https://api.wordpress.org/plugins/info/1.0/', array( 'body'	=> $consulta ) );
		set_transient( 'mdn_sms_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( !is_wp_error( $respuesta ) ) {
		$plugin = get_object_vars( unserialize( $respuesta['body'] ) );
	} else {
		$plugin['rating'] = 100;
	}
	$plugin['rating'] = 100;

	$rating = array(
	   'rating'		=> $plugin['rating'],
	   'type'		=> 'percent',
	   'number'		=> $plugin['num_ratings'],
	);
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

	return '<a title="' . sprintf( __( 'Please, rate %s:', 'mdnsms-sms-notifications-for-woocommerce' ), $mdn_sms['plugin'] ) . '" href="' . $mdn_sms['puntuacion'] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

//Hoja de estilo
function mdn_sms_estilo() {
	wp_register_style( 'mdn_sms_hoja_de_estilo', plugins_url( 'assets/css/style.css', DIRECCION_mdn_sms ) ); //Carga la hoja de estilo
	wp_enqueue_style( 'mdn_sms_hoja_de_estilo' ); //Carga la hoja de estilo
}
add_action( 'admin_enqueue_scripts', 'mdn_sms_estilo' );

//Eliminamos todo rastro del plugin al desinstalarlo
function mdn_sms_desinstalar() {
	delete_option( 'mdnsms_settings' );
	delete_transient( 'mdn_sms_plugin' );
}
register_uninstall_hook( __FILE__, 'mdn_sms_desinstalar' );
