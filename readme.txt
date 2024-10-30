=== MDN Bulk SMS Notifications for WooCommerce ===
Contributors: MDN sms, MDN.COM.BD , Reham
Donate link: https://mdn.com.bd/fund/
Requires at least: 3.8
Tested up to: 5.4
Stable tag: 2.17.1
WC requires at least: 2.1
WC tested up to: 4.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add to your WooCommerce store SMS notifications to your customers when order status changed.

== Description ==
**IMPORTANTE: *MDN Bulk SMS Notifications for WooCommerce* requiere WooCommerce 2.1.0 o superior.**

**mdnSMS SMS Notifications for WooCommerce** add to your WooCommerce store the possibility of send SMS notifications to customer every time the order status changed. Also notifies the owner, if you desired, when the store have a new order.

To use this Plugins you need to create an account with MDN.COM.BD and buy SMS pack.

= Features =
* Support multiple SMS gateways:
 *  Non-masking sms
 *  BrandSMS
 
* Possibility to inform the owner or owners the store about new orders.
* Possibility to send, or not, international SMS.
* Possibility to notify to shipping phone number, if it’s different from the billing phone number.
* 100% compatible with [WPML]
* Support for custom order statuses.
* Support for custom order numbers from [WooCommerce Sequential Order Numbers Pro](http://www.woothemes.com/products/sequential-order-numbers-pro/) plugin.
* Automatically inset the international prefix number, if it’s necessary, to the customer’s phone number.
* Also notified by SMS the customer notes.
* All messages are customizable.
* You can choose which messages to send.
* You can timer every X hours the message for on-hold orders.
* Supports a large number of variables to personalize our messages: %id%, %order_key%, %billing_first_name%, %billing_last_name%, %billing_company%, %billing_address_1%, %billing_address_2%, %billing_city%, %billing_postcode%, %billing_country%, %billing_state%, %billing_email%, %billing_phone%, %shipping_first_name%, %shipping_last_name%, %shipping_company%, %shipping_address_1%, %shipping_address_2%, %shipping_city%, %shipping_postcode%, %shipping_country%, %shipping_state%, %shipping_method%, %shipping_method_title%, %payment_method%, %payment_method_title%, %order_discount%, %cart_discount%, %order_tax%, %order_shipping%, %order_shipping_tax%, %order_total%, %status%, %prices_include_tax%, %tax_display_cart%, %display_totals_ex_tax%, %display_cart_ex_tax%, %order_date%, %modified_date%, %customer_message%, %customer_note%, %post_status%, %shop_name%, %order_product% and %note%.
* You can add your own custom variables.
* Has *mdn_sms_message* filter to facilitate the customization of SMS messages from third-party plugins.
* Has *mdn_sms_message_return* filter to facilitate the customization of messages once they have been encoded from third-party plugins.
* Has *mdn_sms_send_message* filter to prevent sending the SMS messages from third-party plugins.
* Has *mdn_sms_phone_process* and *apg_sms_phone_return* filters to facilitate the phone number process from third-party plugins.
* Possibility to notify multiple phone numbers via filter *apg_sms_phone_return*.
* Once setup is fully automated.

= Translations =
* Español
* English
* French

= Integrated SMS Gateways =
* Supported SMS Gateways:
*MDN SMS Gateway
== Installation ==
1. Manual Installation
 * Download the ZIP file of the plugin to your computer and extract 
 * Upload the folder mdnSMS to wp-content/plugins
 * Login to your WP admin dashboard and activate the plugin "mdnSMS"
 * Enter your API credentials from Settings menu of the plugin and click on Save
2. Automatic Installation
 * Login to your WP dashboard.
 * Go to Plugins=>Add New and search mdnSMS.
 * Click on the install button once the plugin is found.
 * Activate the plugin and insert API credentials
==Support==
*+8801790614055
*help@mdn.com.bd
*facebook: m.Me/MdreamNetwork
*https://MDN.COM.BD
*https://MDNLimited.xyz
 
== Frequently asked questions ==
= How do you set? =
To configure the plugin simply add the data provided by each SMS gateway, which vary based on it.

Also have to add the mobile phone number that’s linked to the account. 

It should specify whether we want, or not, to receive SMS notifications for each new order in the store and if we want, or not, send international SMS.

Finally it must be customized, if desired, the messages to be sent by SMS.



== Changelog ==

* Added messages customization.
= 0.5 =
* Added support for BulkSMS.
= 0.4 =
* Small improvements in the code and fixed errata in translations.
= 0.3.1 =
* Fixed small bug in the code.
= 0.3 =
* Added support for customer notes.
= 0.2 =
* Improved code that checks the international code.
= 0.1 =
* Initial version.