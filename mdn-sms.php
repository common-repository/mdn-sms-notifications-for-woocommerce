<?php
/*
Plugin Name: MDN Bulk SMS Notifications for WooCommerce
Version: 1.1.0
Plugin URI: https://mdn.com.bd/plugin
Description: Add to WooCommerce SMS notifications to your clients for order status changes. Also you can receive an SMS message when the shop get a new order and select if you want to send international SMS. The plugin add the international dial code automatically to the client phone number.
Tag: bulk sms Bd , free bd bulk sms , bulk sms API
Author URI: https://mdn.com.bd
Author: M Dream Network
Requires at least: 3.8
Tested up to: 5.4
WC requires at least: 2.1
WC tested up to: 4.0.1

Text Domain: MDN-bulk-sms-notifications-for-woocommerce
Domain Path: /languages

@package SMS Notifications fot WooCommerce
@category Core
@author M Dream Network
*/

//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Definimos constantes
define( 'DIRECCION_mdn_sms', plugin_basename( __FILE__ ) );

//Funciones generales de mdnSMS
include_once( 'includes/admin/funciones-mdnsms.php' );

//¿Está activo WooCommerce?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
	//Cargamos funciones necesarias
	include_once( 'includes/admin/funciones.php' );

	//Comprobamos si está instalado y activo WPML
	$wpml_activo = function_exists( 'icl_object_id' );
	
	//Actualiza las traducciones de los mensajes SMS
	function mdnsms_registra_wpml( $mdnsms_settings ) {
		global $wpml_activo;
		
		//Registramos los textos en WPML
		if ( $wpml_activo && function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'mdn_sms', 'mensaje_pedido', $mdnsms_settings['mensaje_pedido'] );
			icl_register_string( 'mdn_sms', 'mensaje_recibido', $mdnsms_settings['mensaje_recibido'] );
			icl_register_string( 'mdn_sms', 'mensaje_procesando', $mdnsms_settings['mensaje_procesando'] );
			icl_register_string( 'mdn_sms', 'mensaje_completado', $mdnsms_settings['mensaje_completado'] );
			icl_register_string( 'mdn_sms', 'mensaje_canceledado', $mdnsms_settings['mensaje_canceledado'] );
			icl_register_string( 'mdn_sms', 'mensaje_nota', $mdnsms_settings['mensaje_nota'] );
		} else if ( $wpml_activo ) {
			do_action( 'wpml_register_single_string', 'mdn_sms', 'mensaje_pedido', $mdnsms_settings['mensaje_pedido'] );
			do_action( 'wpml_register_single_string', 'mdn_sms', 'mensaje_recibido', $mdnsms_settings['mensaje_recibido'] );
			do_action( 'wpml_register_single_string', 'mdn_sms', 'mensaje_procesando', $mdnsms_settings['mensaje_procesando'] );
			do_action( 'wpml_register_single_string', 'mdn_sms', 'mensaje_completado', $mdnsms_settings['mensaje_completado'] );
			do_action( 'wpml_register_single_string', 'mdn_sms', 'mensaje_canceledado', $mdnsms_settings['mensaje_canceledado'] );
			do_action( 'wpml_register_single_string', 'mdn_sms', 'mensaje_nota', $mdnsms_settings['mensaje_nota'] );
		}
	}
	
	//Inicializamos las traducciones y los proveedores
	function mdn_sms_inicializacion() {
		global $mdnsms_settings;

		mdnsms_registra_wpml( $mdnsms_settings );
	}
	add_action( 'init', 'mdn_sms_inicializacion' );

	//Pinta el formulario de configuración
	function mdn_sms_tab() {
		include( 'includes/admin/funciones-formulario.php' );
		include( 'includes/formulario.php' );
	}

	//Añade en el menú a WooCommerce
	function mdn_sms_admin_menu() {
		add_submenu_page( 'woocommerce', __( ' WooCommerce SMS Notifications', 'mdnsms-sms-notifications-for-woocommerce' ),  __( 'SMS Notifications', 'mdnsms-sms-notifications-for-woocommerce' ) , 'manage_woocommerce', 'mdn_sms', 'mdn_sms_tab' );
	}
	add_action( 'admin_menu', 'mdn_sms_admin_menu', 15 );

	//Carga los scripts y CSS de WooCommerce
	function mdn_sms_screen_id( $woocommerce_screen_ids ) {
		$woocommerce_screen_ids[] = 'woocommerce_page_mdn_sms';

		return $woocommerce_screen_ids;
	}
	add_filter( 'woocommerce_screen_ids', 'mdn_sms_screen_id' );

	//Registra las opciones
	function mdn_sms_registra_opciones() {
		global $mdnsms_settings;
	
		register_setting( 'mdnsms_settings_group', 'mdnsms_settings', 'mdn_sms_update' );
		$mdnsms_settings = get_option( 'mdnsms_settings' );

		if ( isset( $mdnsms_settings['estados_personalizados'] ) && !empty( $mdnsms_settings['estados_personalizados'] ) ) { //Comprueba la existencia de estados personalizados
			foreach ( $mdnsms_settings['estados_personalizados'] as $estado ) {
				add_action( "woocommerce_order_status_{$estado}", 'mdn_sms_procesa_estados', 10 );
			}
		}
	}
	add_action( 'admin_init', 'mdn_sms_registra_opciones' );
	
	function mdn_sms_update( $mdnsms_settings ) {
		mdnsms_registra_wpml( $mdnsms_settings );
		
		return $mdnsms_settings;
	}

	//Procesa el SMS
	function mdn_sms_procesa_estados( $pedido, $notificacion = false ) {
		global $mdnsms_settings, $wpml_activo;
		
		$numero_de_pedido	= $pedido;
		$pedido				= new WC_Order( $numero_de_pedido );
		$estado				= is_callable( array( $pedido, 'get_status' ) ) ? $pedido->get_status() : $pedido->status;

		//Comprobamos si se tiene que enviar el mensaje o no
		if ( isset( $mdnsms_settings['mensajes'] ) ) {
			if ( $estado == 'on-hold' && !array_intersect( array( "todos", "mensaje_pedido", "mensaje_recibido" ), $mdnsms_settings['mensajes'] ) ) {
				return;
			} else if ( $estado == 'processing' && !array_intersect( array( "todos", "mensaje_pedido", "mensaje_procesando" ), $mdnsms_settings['mensajes'] ) ) {
				return;
			} else if ( $estado == 'completed' && !array_intersect( array( "todos", "mensaje_completado" ), $mdnsms_settings['mensajes'] ) ) {
				return;
			} else if ( $estado == 'cancelled' && !array_intersect( array( "todos", "mensaje_canceledado" ), $mdnsms_settings['mensajes'] ) ) {
				return;
			}
		} else {
			return;
		}
		//Permitir que otros plugins impidan que se envíe el SMS
		if ( !apply_filters( 'mdn_sms_send_message', true, $pedido ) ) {
			return;
		}

		//Recoge datos del formulario de facturación
		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;
		$campo_envio			= get_post_meta( $numero_de_pedido, $mdnsms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= mdn_sms_procesa_el_telefono( $pedido, $billing_phone, $mdnsms_settings['servicio'] );
		$telefono_envio			= mdn_sms_procesa_el_telefono( $pedido, $campo_envio, $mdnsms_settings['servicio'], false, true );
		$enviar_envio			= ( $telefono != $telefono_envio && isset( $mdnsms_settings['envio'] ) && $mdnsms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;
		//Teléfono propietario
		if ( strpos( $mdnsms_settings['telefono'], "|" ) ) {
			$administradores = explode( "|", $mdnsms_settings['telefono'] ); //Existe más de uno
		}
		if ( isset( $administradores ) ) {
			foreach( $administradores as $administrador ) {
				$telefono_propietario[]	= mdn_sms_procesa_el_telefono( $pedido, $administrador, $mdnsms_settings['servicio'], true );
			}
		} else {
			$telefono_propietario = mdn_sms_procesa_el_telefono( $pedido, $mdnsms_settings['telefono'], $mdnsms_settings['servicio'], true );	
		}
		
		//WPML
		if ( function_exists( 'icl_register_string' ) || !$wpml_activo ) { //Versión anterior a la 3.2
			$mensaje_pedido		= ( $wpml_activo ) ? icl_translate( 'mdn_sms', 'mensaje_pedido', $mdnsms_settings['mensaje_pedido'] ) : $mdnsms_settings['mensaje_pedido'];
			$mensaje_recibido	= ( $wpml_activo ) ? icl_translate( 'mdn_sms', 'mensaje_recibido', $mdnsms_settings['mensaje_recibido'] ) : $mdnsms_settings['mensaje_recibido'];
			$mensaje_procesando	= ( $wpml_activo ) ? icl_translate( 'mdn_sms', 'mensaje_procesando', $mdnsms_settings['mensaje_procesando'] ) : $mdnsms_settings['mensaje_procesando'];
			$mensaje_completado	= ( $wpml_activo ) ? icl_translate( 'mdn_sms', 'mensaje_completado', $mdnsms_settings['mensaje_completado'] ) : $mdnsms_settings['mensaje_completado'];
			$mensaje_canceledado	= ( $wpml_activo ) ? icl_translate( 'mdn_sms', 'mensaje_canceledado', $mdnsms_settings['mensaje_canceledado'] ) : $mdnsms_settings['mensaje_canceledado'];
		} else if ( $wpml_activo ) { //Versión 3.2 o superior
			$mensaje_pedido		= apply_filters( 'wpml_translate_single_string', $mdnsms_settings['mensaje_pedido'], 'mdn_sms', 'mensaje_pedido' );
			$mensaje_recibido	= apply_filters( 'wpml_translate_single_string', $mdnsms_settings['mensaje_recibido'], 'mdn_sms', 'mensaje_recibido' );
			$mensaje_procesando	= apply_filters( 'wpml_translate_single_string', $mdnsms_settings['mensaje_procesando'], 'mdn_sms', 'mensaje_procesando' );
			$mensaje_completado	= apply_filters( 'wpml_translate_single_string', $mdnsms_settings['mensaje_completado'], 'mdn_sms', 'mensaje_completado' );
			$mensaje_canceledado	= apply_filters( 'wpml_translate_single_string', $mdnsms_settings['mensaje_canceledado'], 'mdn_sms', 'mensaje_canceledado' );
		}
		
		//Cargamos los proveedores SMS
		include_once( 'includes/admin/proveedores.php' );
		//Envía el SMS
		switch( $estado ) {
			case 'on-hold': //Pedido en espera
				if ( !!array_intersect( array( "todos", "mensaje_pedido" ), $mdnsms_settings['mensajes'] ) && isset( $mdnsms_settings['notificacion'] ) && $mdnsms_settings['notificacion'] == 1 && !$notificacion ) {
					if ( !is_array( $telefono_propietario ) ) {
						mdn_sms_envia_sms( $mdnsms_settings, $telefono_propietario, mdn_sms_procesa_variables( $mensaje_pedido, $pedido, $mdnsms_settings['variables'] ) ); //Mensaje para el propietario
					} else {
						foreach( $telefono_propietario as $administrador ) {
							mdn_sms_envia_sms( $mdnsms_settings, $administrador, mdn_sms_procesa_variables( $mensaje_pedido, $pedido, $mdnsms_settings['variables'] ) ); //Mensaje para los propietarios
						}
					}
				}
						
				if ( !!array_intersect( array( "todos", "mensaje_recibido" ), $mdnsms_settings['mensajes'] ) ) {
					//Limpia el temporizador para pedidos recibidos
					wp_clear_scheduled_hook( 'mdn_sms_ejecuta_el_temporizador' );

					$mensaje = mdn_sms_procesa_variables( $mensaje_recibido, $pedido, $mdnsms_settings['variables'] ); //Mensaje para el cliente

					//Temporizador para pedidos recibidos
					if ( isset( $mdnsms_settings['temporizador'] ) && $mdnsms_settings['temporizador'] > 0 ) {
						wp_schedule_single_event( time() + ( absint( $mdnsms_settings['temporizador'] ) * 60 * 60 ), 'mdn_sms_ejecuta_el_temporizador' );
					}
				}
				break;
			case 'processing': //Pedido procesando
				if ( !!array_intersect( array( "todos", "mensaje_pedido" ), $mdnsms_settings['mensajes'] ) && isset( $mdnsms_settings['notificacion'] ) && $mdnsms_settings['notificacion'] == 1 && $notificacion ) {
					if ( !is_array( $telefono_propietario ) ) {
						mdn_sms_envia_sms( $mdnsms_settings, $telefono_propietario, mdn_sms_procesa_variables( $mensaje_pedido, $pedido, $mdnsms_settings['variables'] ) ); //Mensaje para el propietario
					} else {
						foreach( $telefono_propietario as $administrador ) {
							mdn_sms_envia_sms( $mdnsms_settings, $administrador, mdn_sms_procesa_variables( $mensaje_pedido, $pedido, $mdnsms_settings['variables'] ) ); //Mensaje para los propietarios
						}
					}
				}
				if ( !!array_intersect( array( "todos", "mensaje_procesando" ), $mdnsms_settings['mensajes'] ) ) {
					$mensaje = mdn_sms_procesa_variables( $mensaje_procesando, $pedido, $mdnsms_settings['variables'] );
				}
				break;
			case 'completed': //Pedido completado
				if ( !!array_intersect( array( "todos", "mensaje_completado" ), $mdnsms_settings['mensajes'] ) ) {
					$mensaje = mdn_sms_procesa_variables( $mensaje_completado, $pedido, $mdnsms_settings['variables'] );
				}
				break;
			case 'cancelled': //Pedido completado
				if ( !!array_intersect( array( "todos", "mensaje_canceledado" ), $mdnsms_settings['mensajes'] ) ) {
					$mensaje = mdn_sms_procesa_variables( $mensaje_canceledado, $pedido, $mdnsms_settings['variables'] );
				}
				break;
			default: //Pedido con estado personalizado
				$mensaje = mdn_sms_procesa_variables( $mdnsms_settings[$estado], $pedido, $mdnsms_settings['variables'] );
		}

		if ( isset( $mensaje ) && ( !$internacional || ( isset( $mdnsms_settings['internacional'] ) && $mdnsms_settings['internacional'] == 1 ) ) && !$notificacion ) {
			if ( !is_array( $telefono ) ) {
				mdn_sms_envia_sms( $mdnsms_settings, $telefono, $mensaje ); //Mensaje para el teléfono de facturación
			} else {
				foreach( $telefono as $cliente ) {
					mdn_sms_envia_sms( $mdnsms_settings, $cliente, $mensaje ); //Mensaje para los teléfonos recibidos
				}
			}
			if ( $enviar_envio ) {
				mdn_sms_envia_sms( $mdnsms_settings, $telefono_envio, $mensaje ); //Mensaje para el teléfono de envío
			}
		}
	}
	add_action( 'woocommerce_order_status_pending_to_on-hold_notification', 'mdn_sms_procesa_estados', 10 ); //Funciona cuando el pedido es marcado como recibido
	add_action( 'woocommerce_order_status_failed_to_on-hold_notification', 'mdn_sms_procesa_estados', 10 );
	add_action( 'woocommerce_order_status_processing', 'mdn_sms_procesa_estados', 10 ); //Funciona cuando el pedido es marcado como procesando
	add_action( 'woocommerce_order_status_completed', 'mdn_sms_procesa_estados', 10 ); //Funciona cuando el pedido es marcado como completo
	add_action( 'woocommerce_order_status_cancelled', 'mdn_sms_procesa_estados', 10 ); //Funciona cuando el pedido es marcado como completo

	function mdn_sms_notificacion( $pedido ) {
		mdn_sms_procesa_estados( $pedido, true );
	}
	add_action( 'woocommerce_order_status_pending_to_processing_notification', 'mdn_sms_notificacion', 10 ); //Funciona cuando el pedido es marcado directamente como procesando
	
	//Temporizador
	function mdn_sms_temporizador() {
		global $mdnsms_settings;
		
		$pedidos = wc_get_orders( array(
			'limit'			=> -1,
			'date_created'	=> '<' . ( time() - ( absint( $mdnsms_settings['temporizador'] ) * 60 * 60 ) - 1 ),
			'status'		=> 'on-hold',
		) );

		if ( $pedidos ) {
			foreach ( $pedidos as $pedido ) {
				mdn_sms_procesa_estados( is_callable( array( $pedido, 'get_id' ) ) ? $pedido->get_id() : $pedido->id, false );
			}
		}
	}
	add_action( 'mdn_sms_ejecuta_el_temporizador', 'mdn_sms_temporizador' );

	//Envía las notas de cliente por SMS
	function mdn_sms_procesa_notas( $datos ) {
		global $mdnsms_settings, $wpml_activo;
		
		//Comprobamos si se tiene que enviar el mensaje
		if ( isset( $mdnsms_settings['mensajes']) && !array_intersect( array( "todos", "mensaje_nota" ), $mdnsms_settings['mensajes'] ) ) {
			return;
		}
	
		//Pedido
		$numero_de_pedido		= $datos['order_id'];
		$pedido					= new WC_Order( $numero_de_pedido );
		//Recoge datos del formulario de facturación
		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;	
		$campo_envio			= get_post_meta( $numero_de_pedido, $mdnsms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= mdn_sms_procesa_el_telefono( $pedido, $billing_phone, $mdnsms_settings['servicio'] );
		$telefono_envio			= mdn_sms_procesa_el_telefono( $pedido, $campo_envio, $mdnsms_settings['servicio'], false, true );
		$enviar_envio			= ( isset( $mdnsms_settings['envio'] ) && $telefono != $telefono_envio && $mdnsms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;
		//Recoge datos del formulario de facturación
		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;
		$campo_envio			= get_post_meta( $numero_de_pedido, $mdnsms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= mdn_sms_procesa_el_telefono( $pedido, $billing_phone, $mdnsms_settings['servicio'] );
		$telefono_envio			= mdn_sms_procesa_el_telefono( $pedido, $campo_envio, $mdnsms_settings['servicio'], false, true );
		$enviar_envio			= ( $telefono != $telefono_envio && isset( $mdnsms_settings['envio'] ) && $mdnsms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;

		//WPML
		if ( function_exists( 'icl_register_string' ) || !$wpml_activo ) { //Versión anterior a la 3.2
			$mensaje_nota		= ( $wpml_activo ) ? icl_translate( 'mdn_sms', 'mensaje_nota', $mdnsms_settings['mensaje_nota'] ) : $mdnsms_settings['mensaje_nota'];
		} else if ( $wpml_activo ) { //Versión 3.2 o superior
			$mensaje_nota		= apply_filters( 'wpml_translate_single_string', $mdnsms_settings['mensaje_nota'], 'mdn_sms', 'mensaje_nota' );
		}
		
		//Cargamos los proveedores SMS
		include_once( 'includes/admin/proveedores.php' );		
		//Envía el SMS
		if ( !$internacional || ( isset( $mdnsms_settings['internacional'] ) && $mdnsms_settings['internacional'] == 1 ) ) {
			if ( !is_array( $telefono ) ) {
				mdn_sms_envia_sms( $mdnsms_settings, $telefono, mdn_sms_procesa_variables( $mensaje_nota, $pedido, $mdnsms_settings['variables'], wptexturize( $datos['customer_note'] ) ) ); //Mensaje para el teléfono de facturación
			} else {
				foreach( $telefono as $cliente ) {
					mdn_sms_envia_sms( $mdnsms_settings, $cliente, mdn_sms_procesa_variables( $mensaje_nota, $pedido, $mdnsms_settings['variables'], wptexturize( $datos['customer_note'] ) ) ); //Mensaje para los teléfonos recibidos
				}
			}
			if ( $enviar_envio ) {
				mdn_sms_envia_sms( $mdnsms_settings, $telefono_envio, mdn_sms_procesa_variables( $mensaje_nota, $pedido, $mdnsms_settings['variables'], wptexturize( $datos['customer_note'] ) ) ); //Mensaje para el teléfono de envío
			}
		}
	}
	add_action( 'woocommerce_new_customer_note', 'mdn_sms_procesa_notas', 10 );
} else {
	add_action( 'admin_notices', 'mdn_sms_requiere_wc' );
}

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function mdn_sms_requiere_wc() {
	global $mdn_sms;
		
	echo '<div class="error fade" id="message"><h3>' . $mdn_sms['plugin'] . '</h3><h4>' . __( "This plugin require WooCommerce active to run!", 'mdnsms-sms-notifications-for-woocommerce' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_mdn_sms );
}
