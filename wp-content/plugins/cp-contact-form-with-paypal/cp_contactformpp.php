<?php
/*
Plugin Name: CP Contact Form with PayPal
Plugin URI: http://cfpaypal.dwbooster.com
Description: Inserts a contact form into your website and let you connect it to a Paypal payment.
Version: 1.2.11
Author: CodePeople, paypaldev
Author URI: http://cfpaypal.dwbooster.com
License: GPL
*/


/* initialization / install / uninstall functions */


// CP Contact Form with PayPal constants

define('CP_CONTACTFORMPP_DEFAULT_CURRENCY_SYMBOL','$');
define('CP_CONTACTFORMPP_GBP_CURRENCY_SYMBOL',chr(163));
define('CP_CONTACTFORMPP_EUR_CURRENCY_SYMBOL_A','EUR ');
define('CP_CONTACTFORMPP_EUR_CURRENCY_SYMBOL_B',chr(128));

define('CP_CONTACTFORMPP_DEFAULT_DEFER_SCRIPTS_LOADING', (get_option('CP_CFPP_LOAD_SCRIPTS',"0") == "1"?true:false));

define('CP_CONTACTFORMPP_DEFAULT_form_structure', '[[{"name":"email","index":0,"title":"Email","ftype":"femail","userhelp":"","csslayout":"","required":true,"predefined":"","size":"medium"},{"name":"subject","index":1,"title":"Subject","required":true,"ftype":"ftext","userhelp":"","csslayout":"","predefined":"","size":"medium"},{"name":"message","index":2,"size":"large","required":true,"title":"Message","ftype":"ftextarea","userhelp":"","csslayout":"","predefined":""}],[{"title":"","description":"","formlayout":"top_aligned"}]]');

define('CP_CONTACTFORMPP_DEFAULT_fp_subject', 'Contact from the website...');
define('CP_CONTACTFORMPP_DEFAULT_fp_inc_additional_info', 'false');
define('CP_CONTACTFORMPP_DEFAULT_fp_return_page', get_site_url());
define('CP_CONTACTFORMPP_DEFAULT_fp_message', "The following contact message has been sent:\n\n<"."%INFO%".">\n\n");

define('CP_CONTACTFORMPP_DEFAULT_cu_enable_copy_to_user', 'true');
define('CP_CONTACTFORMPP_DEFAULT_cu_user_email_field', '');
define('CP_CONTACTFORMPP_DEFAULT_cu_subject', 'Confirmation: Message received...');
define('CP_CONTACTFORMPP_DEFAULT_cu_message', "Thank you for your message. We will reply you as soon as possible.\n\nThis is a copy of the data sent:\n\n<"."%INFO%".">\n\nBest Regards.");
define('CP_CONTACTFORMPP_DEFAULT_email_format','text');

define('CP_CONTACTFORMPP_DEFAULT_vs_use_validation', 'true');

define('CP_CONTACTFORMPP_DEFAULT_vs_text_is_required', 'This field is required.');
define('CP_CONTACTFORMPP_DEFAULT_vs_text_is_email', 'Please enter a valid email address.');

define('CP_CONTACTFORMPP_DEFAULT_vs_text_datemmddyyyy', 'Please enter a valid date with the format mm/dd/yyyy');
define('CP_CONTACTFORMPP_DEFAULT_vs_text_dateddmmyyyy', 'Please enter a valid date with the format dd/mm/yyyy');
define('CP_CONTACTFORMPP_DEFAULT_vs_text_number', 'Please enter a valid number.');
define('CP_CONTACTFORMPP_DEFAULT_vs_text_digits', 'Please enter only digits.');
define('CP_CONTACTFORMPP_DEFAULT_vs_text_max', 'Please enter a value less than or equal to {0}.');
define('CP_CONTACTFORMPP_DEFAULT_vs_text_min', 'Please enter a value greater than or equal to {0}.');


define('CP_CONTACTFORMPP_DEFAULT_PAYPAL_EXPRESSCREDIT_YES', 'Pay with PayPal Credit');
define('CP_CONTACTFORMPP_DEFAULT_PAYPAL_EXPRESSCREDIT_NO', 'Pay with PayPal Express');

define('CP_CONTACTFORMPP_DEFAULT_cv_enable_captcha', 'true');
define('CP_CONTACTFORMPP_DEFAULT_cv_width', '180');
define('CP_CONTACTFORMPP_DEFAULT_cv_height', '60');
define('CP_CONTACTFORMPP_DEFAULT_cv_chars', '5');
define('CP_CONTACTFORMPP_DEFAULT_cv_font', 'font-1.ttf');
define('CP_CONTACTFORMPP_DEFAULT_cv_min_font_size', '25');
define('CP_CONTACTFORMPP_DEFAULT_cv_max_font_size', '35');
define('CP_CONTACTFORMPP_DEFAULT_cv_noise', '200');
define('CP_CONTACTFORMPP_DEFAULT_cv_noise_length', '4');
define('CP_CONTACTFORMPP_DEFAULT_cv_background', 'ffffff');
define('CP_CONTACTFORMPP_DEFAULT_cv_border', '000000');
define('CP_CONTACTFORMPP_DEFAULT_cv_text_enter_valid_captcha', 'Please enter a valid captcha code.');


define('CP_CONTACTFORMPP_DEFAULT_ENABLE_PAYPAL', 1);
define('CP_CONTACTFORMPP_DEFAULT_PAYPAL_MODE', 'production');
define('CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT', '0');
define('CP_CONTACTFORMPP_DEFAULT_PAYPAL_IDENTIFY_PRICES', '0');
define('CP_CONTACTFORMPP_DEFAULT_PAYPAL_ZERO_PAYMENT', '0');
define('CP_CONTACTFORMPP_DEFAULT_PAYPAL_EMAIL','sample@email.com');
define('CP_CONTACTFORMPP_DEFAULT_PRODUCT_NAME','Reservation');
define('CP_CONTACTFORMPP_DEFAULT_COST','25');
define('CP_CONTACTFORMPP_DEFAULT_CURRENCY','USD');
define('CP_CONTACTFORMPP_DEFAULT_PAYPAL_LANGUAGE','EN');
define('CP_CONTACTFORMPP_STEP2_VRFY', false);

// database
define('CP_CONTACTFORMPP_FORMS_TABLE', 'cp_contact_form_paypal_settings');

define('CP_CONTACTFORMPP_DISCOUNT_CODES_TABLE_NAME_NO_PREFIX', "cp_contact_form_paypal_discount_codes");
define('CP_CONTACTFORMPP_DISCOUNT_CODES_TABLE_NAME', @$wpdb->prefix ."cp_contact_form_paypal_discount_codes");

define('CP_CONTACTFORMPP_POSTS_TABLE_NAME_NO_PREFIX', "cp_contact_form_paypal_posts");
define('CP_CONTACTFORMPP_POSTS_TABLE_NAME', @$wpdb->prefix ."cp_contact_form_paypal_posts");


// end CP Contact Form with PayPal constants

// code initialization, hooks
// -----------------------------------------

register_activation_hook(__FILE__,'cp_contactformpp_install');

add_action( 'plugins_loaded', 'cp_contact_form_paypal_check_posted_data', 11 );

function cpcfwpp_plugin_init() {
   load_plugin_textdomain( 'cpcfwpp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'cpcfwpp_plugin_init');

if ( is_admin() ) {
    add_action('media_buttons', 'set_cp_contactformpp_insert_button', 100);
    add_action('admin_enqueue_scripts', 'set_cp_contactformpp_insert_adminScripts', 1);
    add_action('admin_menu', 'cp_contactformpp_admin_menu');

    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_".$plugin, 'cp_contactformpp_customAdjustmentsLink');
    add_filter("plugin_action_links_".$plugin, 'cp_contactformpp_settingsLink');
    add_filter("plugin_action_links_".$plugin, 'cp_contactformpp_helpLink');

    function cp_contactformpp_admin_menu() {
        add_options_page('CP Contact Form with PayPal Options', 'CP Contact Form with PayPal', 'manage_options', 'cp_contact_form_paypal.php', 'cp_contactformpp_html_post_page' );
        add_menu_page( 'CP Contact Form with PayPal', 'CP Contact Form with PayPal', 'manage_options', 'cp_contact_form_paypal.php', 'cp_contactformpp_html_post_page' );
        
        add_submenu_page( 'cp_contact_form_paypal.php', 'Manage Forms', 'Manage Forms', 'manage_options', "cp_contact_form_paypal.php",  'cp_contactformpp_html_post_page' );
        add_submenu_page( 'cp_contact_form_paypal.php', 'Help: Online demo', 'Help: Online demo', 'manage_options', "cp_contact_form_paypal_demo", 'cp_contactformpp_html_post_page' );       
        add_submenu_page( 'cp_contact_form_paypal.php', 'Help: Documentation', 'Help: Documentation', 'manage_options', "cp_contact_form_paypal_doc", 'cp_contactformpp_html_post_page' );               
        add_submenu_page( 'cp_contact_form_paypal.php', 'Upgrade', 'Upgrade', 'edit_pages', "cp_contact_form_paypal_upgrade", 'cp_contactformpp_html_post_page' );
 

    }
} else { // if not admin
    add_shortcode( 'CP_CONTACT_FORM_PAYPAL', 'cp_contactformpp_filter_content' );
}


// functions
//------------------------------------------

function cp_contactformpp_install($networkwide)  {
	global $wpdb;

	if (function_exists('is_multisite') && is_multisite()) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ($networkwide) {
	                $old_blog = $wpdb->blogid;
			// Get all blog ids
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				_cp_contactformpp_install();
			}
			switch_to_blog($old_blog);
			return;
		}
	}
	_cp_contactformpp_install();
}

function _cp_contactformpp_install() {
    global $wpdb;

    define('CP_CONTACTFORMPP_DEFAULT_fp_from_email', get_the_author_meta('user_email', get_current_user_id()) );
    define('CP_CONTACTFORMPP_DEFAULT_fp_destination_emails', CP_CONTACTFORMPP_DEFAULT_fp_from_email);

    $table_name = $wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE;

    $sql = "CREATE TABLE ".$wpdb->prefix.CP_CONTACTFORMPP_POSTS_TABLE_NAME_NO_PREFIX." (
         id mediumint(9) NOT NULL AUTO_INCREMENT,
         formid INT NOT NULL,
         time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
         ipaddr VARCHAR(250) DEFAULT '' NOT NULL,
         notifyto VARCHAR(250) DEFAULT '' NOT NULL,
         data mediumtext,
         paypal_post mediumtext,
         posted_data mediumtext,
         paid INT DEFAULT 0 NOT NULL,
         UNIQUE KEY id (id)
         );";
    $wpdb->query($sql);

    $sql = "CREATE TABLE ".$wpdb->prefix.CP_CONTACTFORMPP_DISCOUNT_CODES_TABLE_NAME_NO_PREFIX." (
         id mediumint(9) NOT NULL AUTO_INCREMENT,
         form_id mediumint(9) NOT NULL DEFAULT 1,
         code VARCHAR(250) DEFAULT '' NOT NULL,
         discount VARCHAR(250) DEFAULT '' NOT NULL,
         expires datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
         availability int(10) unsigned NOT NULL DEFAULT 0,
         used int(10) unsigned NOT NULL DEFAULT 0,
         UNIQUE KEY id (id)
         );";
    $wpdb->query($sql);


    $sql = "CREATE TABLE $table_name (
         id mediumint(9) NOT NULL AUTO_INCREMENT,

         form_name VARCHAR(250) DEFAULT '' NOT NULL,

         form_structure mediumtext,

         fp_from_email VARCHAR(250) DEFAULT '' NOT NULL,
         fp_destination_emails text,
         fp_subject VARCHAR(250) DEFAULT '' NOT NULL,
         fp_inc_additional_info VARCHAR(20) DEFAULT '' NOT NULL,
         fp_return_page VARCHAR(250) DEFAULT '' NOT NULL,
         fp_error_page VARCHAR(250) DEFAULT '' NOT NULL,
         fp_message text,
         fp_emailformat VARCHAR(15) DEFAULT '' NOT NULL,

         cu_enable_copy_to_user VARCHAR(10) DEFAULT '' NOT NULL,
         cu_user_email_field VARCHAR(250) DEFAULT '' NOT NULL,
         cu_subject VARCHAR(250) DEFAULT '' NOT NULL,
         cu_message text,
         cp_emailformat VARCHAR(10) DEFAULT '' NOT NULL,

         vs_use_validation VARCHAR(10) DEFAULT '' NOT NULL,
         vs_text_is_required VARCHAR(200) DEFAULT '' NOT NULL,
         vs_text_is_email VARCHAR(250) DEFAULT '' NOT NULL,
         vs_text_datemmddyyyy VARCHAR(250) DEFAULT '' NOT NULL,
         vs_text_dateddmmyyyy VARCHAR(250) DEFAULT '' NOT NULL,
         vs_text_number VARCHAR(250) DEFAULT '' NOT NULL,
         vs_text_digits VARCHAR(250) DEFAULT '' NOT NULL,
         vs_text_max VARCHAR(250) DEFAULT '' NOT NULL,
         vs_text_min VARCHAR(250) DEFAULT '' NOT NULL,
         vs_text_submitbtn VARCHAR(250) DEFAULT '' NOT NULL,

         enable_paypal varchar(10) DEFAULT '' NOT NULL,
         
         paypalexpress_api_username varchar(250) DEFAULT '' NOT NULL,
         paypalexpress_api_password varchar(250) DEFAULT '' NOT NULL,
         paypalexpress_api_signature varchar(250) DEFAULT '' NOT NULL,
         enable_paypal_expresscredit_yes varchar(250) DEFAULT '' NOT NULL,
         enable_paypal_expresscredit_no varchar(250) DEFAULT '' NOT NULL,
         
         paypal_notiemails varchar(10) DEFAULT '' NOT NULL,
         paypal_email varchar(255) DEFAULT '' NOT NULL ,         
         request_cost varchar(255) DEFAULT '' NOT NULL ,
         paypal_price_field varchar(255) DEFAULT '' NOT NULL ,
         request_taxes varchar(20) DEFAULT '' NOT NULL ,
         request_address varchar(20) DEFAULT '' NOT NULL ,
         donationlayout varchar(20) DEFAULT '' NOT NULL ,
         paypal_product_name varchar(255) DEFAULT '' NOT NULL,
         currency varchar(10) DEFAULT '' NOT NULL,
         paypal_language varchar(10) DEFAULT '' NOT NULL,
         paypal_mode varchar(20) DEFAULT '' NOT NULL ,
         paypal_recurrent varchar(20) DEFAULT '' NOT NULL ,
         paypal_recurrent_fp varchar(20) DEFAULT '' NOT NULL ,
         paypal_recurrent_times varchar(20) DEFAULT '' NOT NULL ,
         paypal_recurrent_setup varchar(20) DEFAULT '' NOT NULL ,
         paypal_identify_prices varchar(20) DEFAULT '' NOT NULL ,
         paypal_zero_payment varchar(10) DEFAULT '' NOT NULL ,
         
         script_load_method varchar(10) DEFAULT '' NOT NULL ,

         cv_enable_captcha VARCHAR(20) DEFAULT '' NOT NULL,
         cv_width VARCHAR(20) DEFAULT '' NOT NULL,
         cv_height VARCHAR(20) DEFAULT '' NOT NULL,
         cv_chars VARCHAR(20) DEFAULT '' NOT NULL,
         cv_font VARCHAR(20) DEFAULT '' NOT NULL,
         cv_min_font_size VARCHAR(20) DEFAULT '' NOT NULL,
         cv_max_font_size VARCHAR(20) DEFAULT '' NOT NULL,
         cv_noise VARCHAR(20) DEFAULT '' NOT NULL,
         cv_noise_length VARCHAR(20) DEFAULT '' NOT NULL,
         cv_background VARCHAR(20) DEFAULT '' NOT NULL,
         cv_border VARCHAR(20) DEFAULT '' NOT NULL,
         cv_text_enter_valid_captcha VARCHAR(200) DEFAULT '' NOT NULL,

         UNIQUE KEY id (id)
         );";
    $wpdb->query($sql);

    $count = $wpdb->get_var(  "SELECT COUNT(id) FROM ".$table_name  );
    if (!$count)
    {
        $wpdb->insert( $table_name, array( 'id' => 1,
                                      'form_name' => 'Form 1',

                                      'form_structure' => cp_contactformpp_get_option('form_structure', CP_CONTACTFORMPP_DEFAULT_form_structure),

                                      'fp_from_email' => cp_contactformpp_get_option('fp_from_email', CP_CONTACTFORMPP_DEFAULT_fp_from_email),
                                      'fp_destination_emails' => cp_contactformpp_get_option('fp_destination_emails', CP_CONTACTFORMPP_DEFAULT_fp_destination_emails),
                                      'fp_subject' => cp_contactformpp_get_option('fp_subject', CP_CONTACTFORMPP_DEFAULT_fp_subject),
                                      'fp_inc_additional_info' => cp_contactformpp_get_option('fp_inc_additional_info', CP_CONTACTFORMPP_DEFAULT_fp_inc_additional_info),
                                      'fp_return_page' => cp_contactformpp_get_option('fp_return_page', CP_CONTACTFORMPP_DEFAULT_fp_return_page),
                                      'fp_error_page' => cp_contactformpp_get_option('fp_error_page', ''),
                                      'fp_message' => cp_contactformpp_get_option('fp_message', CP_CONTACTFORMPP_DEFAULT_fp_message),
                                      'fp_emailformat' => cp_contactformpp_get_option('fp_emailformat', CP_CONTACTFORMPP_DEFAULT_email_format),

                                      'cu_enable_copy_to_user' => cp_contactformpp_get_option('cu_enable_copy_to_user', CP_CONTACTFORMPP_DEFAULT_cu_enable_copy_to_user),
                                      'cu_user_email_field' => cp_contactformpp_get_option('cu_user_email_field', CP_CONTACTFORMPP_DEFAULT_cu_user_email_field),
                                      'cu_subject' => cp_contactformpp_get_option('cu_subject', CP_CONTACTFORMPP_DEFAULT_cu_subject),
                                      'cu_message' => cp_contactformpp_get_option('cu_message', CP_CONTACTFORMPP_DEFAULT_cu_message),
                                      'cp_emailformat' => cp_contactformpp_get_option('cp_emailformat', CP_CONTACTFORMPP_DEFAULT_email_format),

                                      'vs_use_validation' => cp_contactformpp_get_option('vs_use_validation', CP_CONTACTFORMPP_DEFAULT_vs_use_validation),
                                      'vs_text_is_required' => cp_contactformpp_get_option('vs_text_is_required', CP_CONTACTFORMPP_DEFAULT_vs_text_is_required),
                                      'vs_text_is_email' => cp_contactformpp_get_option('vs_text_is_email', CP_CONTACTFORMPP_DEFAULT_vs_text_is_email),
                                      'vs_text_datemmddyyyy' => cp_contactformpp_get_option('vs_text_datemmddyyyy', CP_CONTACTFORMPP_DEFAULT_vs_text_datemmddyyyy),
                                      'vs_text_dateddmmyyyy' => cp_contactformpp_get_option('vs_text_dateddmmyyyy', CP_CONTACTFORMPP_DEFAULT_vs_text_dateddmmyyyy),
                                      'vs_text_number' => cp_contactformpp_get_option('vs_text_number', CP_CONTACTFORMPP_DEFAULT_vs_text_number),
                                      'vs_text_digits' => cp_contactformpp_get_option('vs_text_digits', CP_CONTACTFORMPP_DEFAULT_vs_text_digits),
                                      'vs_text_max' => cp_contactformpp_get_option('vs_text_max', CP_CONTACTFORMPP_DEFAULT_vs_text_max),
                                      'vs_text_min' => cp_contactformpp_get_option('vs_text_min', CP_CONTACTFORMPP_DEFAULT_vs_text_min),
                                      'vs_text_submitbtn' => cp_contactformpp_get_option('vs_text_submitbtn', 'Submit'),
                                      
                                      'script_load_method' => cp_contactformpp_get_option('script_load_method', '0'),

                                      'enable_paypal' => cp_contactformpp_get_option('enable_paypal', CP_CONTACTFORMPP_DEFAULT_ENABLE_PAYPAL),
                                      
                                      'enable_paypal_expresscredit_yes' => cp_contactformpp_get_option('enable_paypal_expresscredit_yes', CP_CONTACTFORMPP_DEFAULT_PAYPAL_EXPRESSCREDIT_YES),
                                      'enable_paypal_expresscredit_no' => cp_contactformpp_get_option('enable_paypal_expresscredit_no', CP_CONTACTFORMPP_DEFAULT_PAYPAL_EXPRESSCREDIT_NO),
                                      
                                      'paypal_notiemails' => cp_contactformpp_get_option('paypal_notiemails', '0'),
                                      'paypal_email' => cp_contactformpp_get_option('paypal_email', CP_CONTACTFORMPP_DEFAULT_PAYPAL_EMAIL),
                                      'request_cost' => cp_contactformpp_get_option('request_cost', CP_CONTACTFORMPP_DEFAULT_COST),
                                      'paypal_price_field' => cp_contactformpp_get_option('paypal_price_field', ''),
                                      'request_taxes' => cp_contactformpp_get_option('request_taxes', '0'),                                      
                                      'request_address' => cp_contactformpp_get_option('request_address', '0'),                                      
                                      'donationlayout' => cp_contactformpp_get_option('donationlayout', ''),
                                      'paypal_product_name' => cp_contactformpp_get_option('paypal_product_name', CP_CONTACTFORMPP_DEFAULT_PRODUCT_NAME),
                                      'currency' => cp_contactformpp_get_option('currency', CP_CONTACTFORMPP_DEFAULT_CURRENCY),
                                      'paypal_language' => cp_contactformpp_get_option('paypal_language', CP_CONTACTFORMPP_DEFAULT_PAYPAL_LANGUAGE),
                                      'paypal_mode' => cp_contactformpp_get_option('paypal_mode', CP_CONTACTFORMPP_DEFAULT_PAYPAL_MODE),
                                      'paypal_recurrent' => cp_contactformpp_get_option('paypal_recurrent', CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT),
                                      'paypal_recurrent_fp' => cp_contactformpp_get_option('paypal_recurrent_fp', CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT),
                                      'paypal_recurrent_times' => cp_contactformpp_get_option('paypal_recurrent_times', '0'),
                                      'paypal_recurrent_setup' => cp_contactformpp_get_option('paypal_recurrent_setup', '0'),
                                      'paypal_identify_prices' => cp_contactformpp_get_option('paypal_identify_prices', CP_CONTACTFORMPP_DEFAULT_PAYPAL_IDENTIFY_PRICES),
                                      'paypal_zero_payment' => cp_contactformpp_get_option('paypal_zero_payment', CP_CONTACTFORMPP_DEFAULT_PAYPAL_ZERO_PAYMENT),

                                      'cv_enable_captcha' => cp_contactformpp_get_option('cv_enable_captcha', CP_CONTACTFORMPP_DEFAULT_cv_enable_captcha),
                                      'cv_width' => cp_contactformpp_get_option('cv_width', CP_CONTACTFORMPP_DEFAULT_cv_width),
                                      'cv_height' => cp_contactformpp_get_option('cv_height', CP_CONTACTFORMPP_DEFAULT_cv_height),
                                      'cv_chars' => cp_contactformpp_get_option('cv_chars', CP_CONTACTFORMPP_DEFAULT_cv_chars),
                                      'cv_font' => cp_contactformpp_get_option('cv_font', CP_CONTACTFORMPP_DEFAULT_cv_font),
                                      'cv_min_font_size' => cp_contactformpp_get_option('cv_min_font_size', CP_CONTACTFORMPP_DEFAULT_cv_min_font_size),
                                      'cv_max_font_size' => cp_contactformpp_get_option('cv_max_font_size', CP_CONTACTFORMPP_DEFAULT_cv_max_font_size),
                                      'cv_noise' => cp_contactformpp_get_option('cv_noise', CP_CONTACTFORMPP_DEFAULT_cv_noise),
                                      'cv_noise_length' => cp_contactformpp_get_option('cv_noise_length', CP_CONTACTFORMPP_DEFAULT_cv_noise_length),
                                      'cv_background' => cp_contactformpp_get_option('cv_background', CP_CONTACTFORMPP_DEFAULT_cv_background),
                                      'cv_border' => cp_contactformpp_get_option('cv_border', CP_CONTACTFORMPP_DEFAULT_cv_border),
                                      'cv_text_enter_valid_captcha' => cp_contactformpp_get_option('cv_text_enter_valid_captcha', CP_CONTACTFORMPP_DEFAULT_cv_text_enter_valid_captcha)
                                     )
                      );
    }

}


function cp_contactformpp_filter_content($atts) {
    global $wpdb;
    extract( shortcode_atts( array(
		'id' => '',
	), $atts ) );
    ob_start();
    cp_contactformpp_get_public_form(intval($id));
    $buffered_contents = ob_get_contents();
    ob_end_clean();
    return $buffered_contents;
}

$CP_CFPP_global_form_count_number = 0;
$CP_CPP_global_form_count = "_".$CP_CFPP_global_form_count_number;

function cp_contactformpp_get_public_form($id) {
    global $wpdb;
    global $CP_CPP_global_form_count;
    global $CP_CFPP_global_form_count_number;
    $CP_CFPP_global_form_count_number++;
    $CP_CPP_global_form_count = "_".$CP_CFPP_global_form_count_number;  
    if (!defined('CP_AUTH_INCLUDE')) define('CP_AUTH_INCLUDE', true);

    if ($id != '')
        $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE." WHERE id=".$id );
    else
        $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE );
    if ($id == '') $id = $myrows[0]->id;
    if (CP_CONTACTFORMPP_DEFAULT_DEFER_SCRIPTS_LOADING)
    {
        wp_deregister_script('query-stringify');
        wp_register_script('query-stringify', plugins_url('/js/jQuery.stringify.js', __FILE__));
        
        wp_deregister_script('cp_contactformpp_validate_script');
        wp_register_script('cp_contactformpp_validate_script', plugins_url('/js/jquery.validate.js', __FILE__));
        
        wp_enqueue_script( 'cp_contactformpp_buikder_script',
        plugins_url('/js/fbuilder.jquery.js', __FILE__),array("jquery","jquery-ui-core","jquery-ui-datepicker","jquery-ui-widget","jquery-ui-position","jquery-ui-tooltip","query-stringify","cp_contactformpp_validate_script"), false, true );
        
        
        wp_localize_script('cp_contactformpp_buikder_script', 'cp_contactformpp_fbuilder_config'.$CP_CPP_global_form_count, array('obj'  	=>
        '{"pub":true,"identifier":"'.$CP_CPP_global_form_count.'","messages": {
        	                	"required": "'.str_replace(array('"'),array('\\"'),__(cp_contactformpp_get_option('vs_text_is_required', CP_CONTACTFORMPP_DEFAULT_vs_text_is_required,$id),'cpcfwpp')).'",
        	                	"email": "'.str_replace(array('"'),array('\\"'),__(cp_contactformpp_get_option('vs_text_is_email', CP_CONTACTFORMPP_DEFAULT_vs_text_is_email,$id),'cpcfwpp')).'",
        	                	"datemmddyyyy": "'.str_replace(array('"'),array('\\"'),__(cp_contactformpp_get_option('vs_text_datemmddyyyy', CP_CONTACTFORMPP_DEFAULT_vs_text_datemmddyyyy,$id),'cpcfwpp')).'",
        	                	"dateddmmyyyy": "'.str_replace(array('"'),array('\\"'),__(cp_contactformpp_get_option('vs_text_dateddmmyyyy', CP_CONTACTFORMPP_DEFAULT_vs_text_dateddmmyyyy,$id),'cpcfwpp')).'",
        	                	"number": "'.str_replace(array('"'),array('\\"'),__(cp_contactformpp_get_option('vs_text_number', CP_CONTACTFORMPP_DEFAULT_vs_text_number,$id),'cpcfwpp')).'",
        	                	"digits": "'.str_replace(array('"'),array('\\"'),__(cp_contactformpp_get_option('vs_text_digits', CP_CONTACTFORMPP_DEFAULT_vs_text_digits,$id),'cpcfwpp')).'",
        	                	"max": "'.str_replace(array('"'),array('\\"'),__(cp_contactformpp_get_option('vs_text_max', CP_CONTACTFORMPP_DEFAULT_vs_text_max,$id),'cpcfwpp')).'",
        	                	"min": "'.str_replace(array('"'),array('\\"'),__(cp_contactformpp_get_option('vs_text_min', CP_CONTACTFORMPP_DEFAULT_vs_text_min,$id),'cpcfwpp')).'"
        	                }}'
        ));
    }  
    else
    {
        wp_enqueue_script( "jquery" );
        wp_enqueue_script( "jquery-ui-core" );
        wp_enqueue_script( "jquery-ui-datepicker" );
    }  
    $codes = $wpdb->get_results( 'SELECT * FROM '.CP_CONTACTFORMPP_DISCOUNT_CODES_TABLE_NAME.' WHERE `form_id`='.$id);

    $button_label = cp_contactformpp_get_option('vs_text_submitbtn', 'Submit',$id);
    $button_label = ($button_label==''?'Submit':$button_label);
    @include dirname( __FILE__ ) . '/cp_contactformpp_public_int.inc.php';
    if (!CP_CONTACTFORMPP_DEFAULT_DEFER_SCRIPTS_LOADING) {
            $prefix_ui = '';
            if (@file_exists(dirname( __FILE__ ).'/../../../wp-includes/js/jquery/ui/jquery.ui.core.min.js'))
                $prefix_ui = 'jquery.ui.';         
?>
<?php $plugin_url = plugins_url('', __FILE__); ?>
<script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/jquery.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'core.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'datepicker.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'widget.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'position.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'tooltip.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo plugins_url('js/jQuery.stringify.js', __FILE__); ?>'></script>
<script type='text/javascript' src='<?php echo plugins_url('js/jquery.validate.js', __FILE__); ?>'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var cp_contactformpp_fbuilder_config<?php echo $CP_CPP_global_form_count; ?> = {"obj":"{\"pub\":true,\"identifier\":\"<?php echo $CP_CPP_global_form_count; ?>\",\"messages\": {\n    \t                \t\"required\": \"This field is required.\",\n    \t                \t\"email\": \"Please enter a valid email address.\",\n    \t                \t\"datemmddyyyy\": \"Please enter a valid date with this format(mm\/dd\/yyyy)\",\n    \t                \t\"dateddmmyyyy\": \"Please enter a valid date with this format(dd\/mm\/yyyy)\",\n    \t                \t\"number\": \"Please enter a valid number.\",\n    \t                \t\"digits\": \"Please enter only digits.\",\n    \t                \t\"max\": \"Please enter a value less than or equal to {0}.\",\n    \t                \t\"min\": \"Please enter a value greater than or equal to {0}.\"\n    \t                }}"};
/* ]]> */
</script>
<script type='text/javascript' src='<?php echo plugins_url('js/fbuilder.jquery.js', __FILE__); ?>'></script>
<?php    
    }
}


function cp_contactformpp_settingsLink($links) {
    $settings_link = '<a href="admin.php?page=cp_contact_form_paypal.php">'.__('Settings').'</a>';
	array_unshift($links, $settings_link);
	return $links;
}


function cp_contactformpp_helpLink($links) {
    $help_link = '<a href="https://wordpress.org/support/plugin/cp-contact-form-with-paypal#new-post">'.__('Help').'</a>';
	array_unshift($links, $help_link);
	return $links;
}


function cp_contactformpp_customAdjustmentsLink($links) {
    $customAdjustments_link = '<a href="http://cfpaypal.dwbooster.com/download">'.__('Upgrade To Premium').'</a>';
	array_unshift($links, $customAdjustments_link);
	return $links;
}


function set_cp_contactformpp_insert_button() {
    print '<a href="javascript:cp_contactformpp_insertForm();" title="'.__('Insert CP Contact Form with PayPal').'"><img hspace="5" src="'.plugins_url('/images/cp_form.gif', __FILE__).'" alt="'.__('Insert CP Contact Form with PayPal').'" /></a>';
}


function cp_contactformpp_html_post_page() {    
    if (isset($_GET["cal"]) && $_GET["cal"] != '')
    {
        $_GET["cal"] = intval($_GET["cal"]);
        if (isset($_GET["list"]) && $_GET["list"] == '1')
            @include_once dirname( __FILE__ ) . '/cp_contactformpp_admin_int_message_list.inc.php';
        else
            @include_once dirname( __FILE__ ) . '/cp_contactformpp_admin_int.php';
    }
    else
    {                
        if (isset($_GET["page"]) &&$_GET["page"] == 'cp_contact_form_paypal_upgrade')
        {
            echo("Redirecting to upgrade page...<script type='text/javascript'>document.location='http://cfpaypal.dwbooster.com/download';</script>");
            exit;
        } 
        else if (isset($_GET["page"]) &&$_GET["page"] == 'cp_contact_form_paypal_demo')
        {
            echo("Redirecting to demo page...<script type='text/javascript'>document.location='http://cfpaypal.dwbooster.com/home#demos';</script>");
            exit;
        } 
        else if (isset($_GET["page"]) &&$_GET["page"] == 'cp_contact_form_paypal_doc')
        {
            echo("Redirecting to demo page...<script type='text/javascript'>document.location='http://cfpaypal.dwbooster.com/documentation?ref=plugin';</script>");
            exit;
        }         
        else
            @include_once dirname( __FILE__ ) . '/cp_contactformpp_admin_int_list.inc.php';
    }
}


function set_cp_contactformpp_insert_adminScripts($hook) {
    if (isset($_GET["page"]) && $_GET["page"] == "cp_contact_form_paypal.php")
    {
        wp_deregister_script('query-stringify');
        wp_register_script('query-stringify', plugins_url('/js/jQuery.stringify.js', __FILE__));
        wp_enqueue_script( 'cp_contactformpp_buikder_script', plugins_url('/js/fbuilder.jquery.js', __FILE__),array("jquery","jquery-ui-core","jquery-ui-sortable","jquery-ui-tabs","jquery-ui-droppable","jquery-ui-button","jquery-ui-datepicker","query-stringify") );
        wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    }

    if( 'post.php' != $hook  && 'post-new.php' != $hook )
        return;
    wp_enqueue_script( 'cp_contactformpp_script', plugins_url('/cp_contactformpp_scripts.js', __FILE__) );
}


function cp_contactformpp_get_site_url($admin = false)
{
    $blog = get_current_blog_id();
    if( $admin ) 
        $url = get_admin_url( $blog );	
    else 
        $url = get_home_url( $blog );	

    $url = parse_url($url);
    $url = rtrim(@$url["path"],"/");
    return $url;
}

function cp_contactformpp_get_FULL_site_url($admin = false)
{
    $url = cp_contactformpp_get_site_url($admin);
    $pos = strpos($url, "://");    
    if ($pos === false)
        $url = 'http://'.$_SERVER["HTTP_HOST"].$url;
    return $url;
}

function cp_contactformpp_cleanJSON($str)
{
    $str = str_replace('&qquot;','"',$str);
    $str = str_replace('&qquote;','"',$str);
    $str = str_replace('	',' ',$str);
    $str = str_replace("\n",'\n',$str);
    $str = str_replace("\r",'',$str);
    return $str;
}


function cp_contactformpp_load_discount_codes() {
    global $wpdb;

    if ( ! current_user_can('edit_pages') ) // prevent loading coupons from outside admin area
    {
        echo 'No enough privilegies to load this content.';
        exit;
    }

    if (!defined('CP_CONTACTFORMPP_ID'))
        define ('CP_CONTACTFORMPP_ID',intval($_GET["dex_item"]));

    if (isset($_GET["add"]) && $_GET["add"] == "1")
        $wpdb->insert( CP_CONTACTFORMPP_DISCOUNT_CODES_TABLE_NAME, array('form_id' => CP_CONTACTFORMPP_ID,
                                                                         'code' => esc_sql($_GET["code"]),
                                                                         'discount' => $_GET["discount"],
                                                                         'availability' => $_GET["discounttype"],
                                                                         'expires' => esc_sql($_GET["expires"]),
                                                                         ));

    if (isset($_GET["delete"]) && $_GET["delete"] == "1")
        $wpdb->query( $wpdb->prepare( "DELETE FROM ".CP_CONTACTFORMPP_DISCOUNT_CODES_TABLE_NAME." WHERE id = %d", $_GET["code"] ));

    $codes = $wpdb->get_results( 'SELECT * FROM '.CP_CONTACTFORMPP_DISCOUNT_CODES_TABLE_NAME.' WHERE `form_id`='.CP_CONTACTFORMPP_ID);
    if (count ($codes))
    {
        echo '<table>';
        echo '<tr>';
        echo '  <th style="padding:2px;background-color: #cccccc;font-weight:bold;">Coupon</th>';
        echo '  <th style="padding:2px;background-color: #cccccc;font-weight:bold;">Discount</th>';
        echo '  <th style="padding:2px;background-color: #cccccc;font-weight:bold;">Type</th>';
        echo '  <th style="padding:2px;background-color: #cccccc;font-weight:bold;">Valid until</th>';
        echo '  <th style="padding:2px;background-color: #cccccc;font-weight:bold;">Options</th>';
        echo '</tr>';
        foreach ($codes as $value)
        {
           echo '<tr>';
           echo '<td>'.$value->code.'</td>';
           echo '<td>'.$value->discount.'</td>';
           echo '<td>'.($value->availability==1?"Fixed Value":"Percent").'</td>';
           echo '<td>'.substr($value->expires,0,10).'</td>';
           echo '<td>[<a href="javascript:dex_delete_coupon('.$value->id.')">Delete</a>]</td>';
           echo '</tr>';
        }
        echo '</table>';
    }
    else
        echo 'No discount codes listed for this form yet.';
    exit;
}


function cp_contact_form_paypal_check_posted_data() {

    global $wpdb;

	if ( isset( $_GET['cp_contactformpp_ipncheck'] ) && $_GET['cp_contactformpp_ipncheck'] != '' )
		cp_contactformpp_check_IPN_verification();
 
    if ( isset( $_GET['cp_contactformpp_ipncheckexpress'] ) && $_GET['cp_contactformpp_ipncheckexpress'] != '' )
		cp_contactformpp_check_IPN_verification_express();		
		

    if (isset( $_GET['cp_contactformpp_encodingfix'] ) && $_GET['cp_contactformpp_encodingfix'] == '1')
    {		
        $wpdb->query('alter table '.CP_CONTACTFORMPP_DISCOUNT_CODES_TABLE_NAME.' convert to character set utf8 collate utf8_unicode_ci;');
        $wpdb->query('alter table '.CP_CONTACTFORMPP_FORMS_TABLE.' convert to character set utf8 collate utf8_unicode_ci;');
        $wpdb->query('alter table '.CP_CONTACTFORMPP_POSTS_TABLE_NAME.' convert to character set utf8 collate utf8_unicode_ci;'); 
        echo 'Ok, encoding fixed.';
        exit;		
    }    

    if(isset($_GET) && array_key_exists('cp_contact_form_paypal_post',$_GET)) {
        if ($_GET["cp_contact_form_paypal_post"] == 'loadcoupons')
            cp_contactformpp_load_discount_codes();            
    }
    
    if (isset( $_GET['cp_contactformpp'] ) && $_GET['cp_contactformpp'] == 'captcha' )
    {
        @include_once dirname( __FILE__ ) . '/captcha/captcha.php';            
        exit;        
    }        

    if (isset( $_GET['cp_contactformpp_csv'] ) && is_admin() )
    {
        cp_contactformpp_export_csv();
        return;
    }
    
    if (isset( $_GET['script_load_method'] ) )
    {
        cp_contactformpp_update_script_method();
        return;
    }    

    if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['cp_contactformpp_post_options'] ) && is_admin() )
    {
        cp_contactformpp_save_options();
        return;
    }

	if ( 'POST' != $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['cp_contactformpp_pform_process'] ) )
	    if ( 'GET' != $_SERVER['REQUEST_METHOD'] || !isset( $_GET['hdcaptcha_cp_contact_form_paypal_post'] ) )
		    return;

    if (isset($_POST["cp_contactformpp_id"])) define("CP_CONTACTFORMPP_ID",intval($_POST["cp_contactformpp_id"]));

    if (function_exists('session_start')) @session_start();
    if (isset($_GET["ps"])) $sequence = $_GET["ps"]; else if (isset($_POST["cp_pform_psequence"])) $sequence = $_POST["cp_pform_psequence"];
    if (!isset($_GET['hdcaptcha_cp_contact_form_paypal_post']) || $_GET['hdcaptcha_cp_contact_form_paypal_post'] == '') $_GET['hdcaptcha_cp_contact_form_paypal_post'] = @$_POST['hdcaptcha_cp_contact_form_paypal_post'];
    if (
           (cp_contactformpp_get_option('cv_enable_captcha', CP_CONTACTFORMPP_DEFAULT_cv_enable_captcha) != 'false') &&
           ( (strtolower($_GET['hdcaptcha_cp_contact_form_paypal_post']) != strtolower($_SESSION['rand_code'.$sequence])) ||
             ($_SESSION['rand_code'.$sequence] == '')
           )
           &&
           ( (md5(strtolower($_GET['hdcaptcha_cp_contact_form_paypal_post'])) != @$_COOKIE['rand_code'.$sequence]) ||
             ($_COOKIE['rand_code'.$sequence] == '')
           )
       )
    {
        $_SESSION['rand_code'.$sequence] = '';
        setCookie('rand_code'.$sequence, '', time()+36000,"/");        
        echo 'captchafailed';
        exit;
    }


	// if this isn't the real post (it was the captcha verification) then echo ok and exit
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['cp_contactformpp_pform_process'] ) )
	{
	    echo 'ok';
        exit;
	}

    //if (get_magic_quotes_gpc())
        foreach ($_POST as $item => $value)
            if (!is_array($value))
                $_POST[$item] = stripcslashes($value);

	// get base price
    $price = cp_contactformpp_get_option('request_cost', CP_CONTACTFORMPP_DEFAULT_COST);
    $price = trim(str_replace(',','', str_replace(CP_CONTACTFORMPP_DEFAULT_CURRENCY_SYMBOL,'', 
                                     str_replace(CP_CONTACTFORMPP_GBP_CURRENCY_SYMBOL,'', 
                                     str_replace(CP_CONTACTFORMPP_EUR_CURRENCY_SYMBOL_A, '',
                                     str_replace(CP_CONTACTFORMPP_EUR_CURRENCY_SYMBOL_B,'', $price )))) ));     
    $added_cost = @$_POST[cp_contactformpp_get_option('paypal_price_field', '').$sequence];
    if (!is_numeric($added_cost))
        $added_cost = 0;
    $price += $added_cost;    
    $taxes = trim(str_replace("%","",cp_contactformpp_get_option('request_taxes', '0')));

    // get form info
    //---------------------------
    $identify_prices = cp_contactformpp_get_option('paypal_identify_prices',CP_CONTACTFORMPP_DEFAULT_PAYPAL_IDENTIFY_PRICES);    
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    $form_data = json_decode(cp_contactformpp_cleanJSON(cp_contactformpp_get_option('form_structure', CP_CONTACTFORMPP_DEFAULT_form_structure)));
    $fields = array();
    foreach ($form_data[0] as $item)
        $fields[$item->name] = $item->title;                

    // calculate discounts if any
    //---------------------------
    $discount_note = "";
    $coupon = false;
    $codes = array();

    // grab posted data
    //---------------------------
    $buffer = "";
    foreach ($_POST as $item => $value)
        if (isset($fields[str_replace($sequence,'',$item)]))
        {
            $buffer .= $fields[str_replace($sequence,'',$item)] . ": ". (is_array($value)?(implode(", ",$value)):($value)) . "\n\n";
            $params[str_replace($sequence,'',$item)] = $value;
        }

    $buffer_A = $buffer;

    $paypal_product_name = cp_contactformpp_get_option('paypal_product_name', CP_CONTACTFORMPP_DEFAULT_PRODUCT_NAME).$discount_note;
    $params["PayPal Product Name"] = $paypal_product_name; 
    $params["Cost"] = $price;
    $params["taxamount"] = round($price * ($taxes/100),2);
    $params["Costtax"] = $price + $params["taxamount"];
    
    $current_user = wp_get_current_user();
    $params["user_login"] = $current_user->user_login;
    $params["user_id"] = $current_user->ID;
    $params["user_email"] = $current_user->user_email;
    $params["user_firstname"] = $current_user->user_firstname; 
    $params["user_lastname"] = $current_user->user_lastname; 
    $params["display_name"] = $current_user->display_name;     
    
    cp_contactformpp_add_field_verify(CP_CONTACTFORMPP_POSTS_TABLE_NAME,'posted_data');

    $_SESSION['rand_code'.$sequence] = '';
    setCookie('rand_code'.$sequence, '', time()+36000,"/");

    // insert into database
    //---------------------------
    $wpdb->query("ALTER TABLE ".CP_CONTACTFORMPP_POSTS_TABLE_NAME." CHANGE `ipaddr` `ipaddr` VARCHAR(250)");
    $to = cp_contactformpp_get_option('cu_user_email_field', CP_CONTACTFORMPP_DEFAULT_cu_user_email_field).$sequence;
    $rows_affected = $wpdb->insert( CP_CONTACTFORMPP_POSTS_TABLE_NAME, array( 'formid' => CP_CONTACTFORMPP_ID,
                                                                        'time' => current_time('mysql'),
                                                                        'ipaddr' => $_SERVER['REMOTE_ADDR'],
                                                                        'notifyto' => (@$_POST[$to]?@$_POST[$to]:''),
                                                                        'paypal_post' => serialize($params),
                                                                        'posted_data' => serialize($params),
                                                                        'data' =>$buffer_A
                                                                         ),
                                                                         array ('%d','%s','%s','%s','%s','%s','%s')  );
    if (!$rows_affected)
    {         
        echo 'Error saving data! Please try again.';
        echo '<br /><br />Error debug information: '.mysql_error();
        echo '<br /><br />If the error persists please be sure you are using the latest version and in that case contact support service at http://cfpaypal.dwbooster.com/contact-us?debug=db';
        exit;
    }

    $myrows = $wpdb->get_results( "SELECT MAX(id) as max_id FROM ".CP_CONTACTFORMPP_POSTS_TABLE_NAME );


 	// save data here
    $item_number = $myrows[0]->max_id;
    
    $paypal_recurrent = cp_contactformpp_get_option('paypal_recurrent_setup','0');
    
    $error_page = cp_contactformpp_get_option('fp_error_page', '');
    if ($error_page == '')
        $error_page = $_POST["cp_ref_page"];

    if (cp_contactformpp_get_option('enable_paypal', '') == '100' || cp_contactformpp_get_option('enable_paypal', '') == '101' )        
    {
         @include_once dirname( __FILE__ ) . '/cp_paypal.express.php';

         try 
         {                
             $ppexp = new DEXBCCF_PayPalEXPC();
             $ppexp->mode = cp_contactformpp_get_option('paypal_mode',CP_CONTACTFORMPP_DEFAULT_PAYPAL_MODE);        
             $ppexp->API_UserName =cp_contactformpp_get_option('paypalexpress_api_username','');        
             $ppexp->API_Password = cp_contactformpp_get_option('paypalexpress_api_password','');
             $ppexp->API_Signature = cp_contactformpp_get_option('paypalexpress_api_signature','');
             $ppexp->currency = strtoupper(cp_contactformpp_get_option('currency', CP_CONTACTFORMPP_DEFAULT_CURRENCY));
             $ppexp->lang = cp_contactformpp_get_option('paypal_language', CP_CONTACTFORMPP_DEFAULT_PAYPAL_LANGUAGE);

             $products = array();
             $products[0]['ItemName'] = $paypal_product_name; //Item Name
		     $products[0]['ItemPrice'] = $price; //Item Price
		     $products[0]['ItemNumber'] = $item_number; //Item Number
		     $products[0]['ItemDesc'] = $paypal_product_name; //Item Desc
		     $products[0]['ItemQty']	= 1; // Item Quantity
		     
		     $charges = array(); //Other important variables like tax, shipping cost
		     $charges['TotalTaxAmount'] = $params["taxamount"];  //Sum of tax for all items in this order. 
		     $charges['HandalingCost'] = 0;  //Handling cost for this order.
		     $charges['InsuranceCost'] = 0;  //shipping insurance cost for this order.
		     $charges['ShippinDiscount'] = 0; //Shipping discount for this order. Specify this as negative number.
		     $charges['ShippinCost'] = 0; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.
             
             $okurl = cp_contactformpp_get_FULL_site_url().'/?cp_contactformpp_ipncheckexpress='.$item_number;
             
             $ppexp->SetExpressCheckOut($products, $charges, $okurl, $error_page, (cp_contactformpp_get_option('request_address','0') != '1' ? '1' :'2' ), (cp_contactformpp_get_option('enable_paypal', '') == '101') );
             exit;
             
         } catch (Exception $e) {
             echo "Error: ".$e->getMessage();
         }         
         
         exit;
    }
            
    if (cp_contactformpp_get_option('paypal_mode',CP_CONTACTFORMPP_DEFAULT_PAYPAL_MODE) == "sandbox")                                                                                  
        $ppurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    else
        $ppurl = 'https://www.paypal.com/cgi-bin/webscr';
    $recurrent = cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT);    
    $recurrent_fp = cp_contactformpp_get_option('paypal_recurrent_fp',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT);
?>
<html>
<head><title>Redirecting to Paypal...</title></head>
<body>
<form action="<?php echo $ppurl; ?>" name="ppform3" method="post">
<input type="hidden" name="business" value="<?php echo trim(cp_contactformpp_get_option('paypal_email', CP_CONTACTFORMPP_DEFAULT_PAYPAL_EMAIL)); ?>" />
<input type="hidden" name="item_name" value="<?php echo $paypal_product_name; ?>" />
<input type="hidden" name="item_number" value="<?php echo $item_number; ?>" />
<?php if (cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '0' || cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '') { ?>
<input type="hidden" name="cmd" value="<?php if (cp_contactformpp_get_option('donationlayout','') == '1') echo '_donations'; else echo '_xclick'; ?>" />
<input type="hidden" name="bn" value="NetFactorSL_SI_Custom" />
<input type="hidden" name="amount" value="<?php echo floatval($price); ?>" />
<?php } else { ?>
<?php if ($paypal_recurrent != '' && $recurrent_fp != '' && $recurrent_fp != '0') { ?>
<input type="hidden" name="a1" value="<?php echo $paypal_recurrent; ?>">
<input type="hidden" name="p1" value="<?php echo ($recurrent_fp=='0.4'?'1':$recurrent_fp); ?>">
<input type="hidden" name="t1" value="<?php echo ($recurrent_fp=='0.4'?'W':'M'); ?>">
<?php } ?>
<?php $selnum = (cp_contactformpp_get_option('paypal_recurrent_times','0')); ?>
<?php if ($selnum != '0') { ?>
<input type="hidden" name="srt" value="<?php echo $selnum; ?>">
<?php } ?>
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="cmd" value="_xclick-subscriptions">
<input type="hidden" name="bn" value="NetFactorSL_SI_Custom">
<input type="hidden" name="a3" value="<?php echo $price; ?>">
<input type="hidden" name="p3" value="<?php echo ($recurrent=='0.4'?'1':$recurrent); ?>">
<input type="hidden" name="t3" value="<?php echo ($recurrent=='0.4'?'W':'M'); ?>">
<input type="hidden" name="src" value="1">
<input type="hidden" name="sra" value="1">
<?php } ?>
<?php if ($taxes != '0' && $taxes != '') { ?>
<input type="hidden" name="tax_rate"  value="<?php echo $taxes; ?>" />
<?php } ?>
<input type="hidden" name="page_style" value="Primary" />
<input type="hidden" name="charset" value="utf-8">
<input type="hidden" name="no_shipping" value="<?php if (cp_contactformpp_get_option('request_address','0') != '1') echo '1'; else echo '2'; ?>" />
<input type="hidden" name="return" value="<?php echo esc_url(cp_contactformpp_get_option('fp_return_page', CP_CONTACTFORMPP_DEFAULT_fp_return_page)); ?>">
<input type="hidden" name="cancel_return" value="<?php echo esc_url($error_page); ?>" />
<input type="hidden" name="currency_code" value="<?php echo cp_contactformpp_clean_currency(cp_contactformpp_get_option('currency', CP_CONTACTFORMPP_DEFAULT_CURRENCY)); ?>" />
<input type="hidden" name="lc" value="<?php echo trim(cp_contactformpp_get_option('paypal_language', CP_CONTACTFORMPP_DEFAULT_PAYPAL_LANGUAGE)); ?>" />
<input type="hidden" name="notify_url" value="<?php echo cp_contactformpp_get_FULL_site_url(); ?>/?cp_contactformpp_ipncheck=<?php echo $item_number; ?>" />
</form>
<script type="text/javascript">
document.ppform3.submit();
</script>
</body>
</html>
<?php
        exit();   
}

function cp_contactformpp_clean_currency($currency)
{
	$currency = trim(strtoupper($currency));
	if ($currency == 'GPB')
		return 'GBP';
	else if ($currency == 'CDN')
		return 'CAD';
	else if ($currency == '$')
		return 'USD';
	else if ($currency == 'MXP')
		return 'MXN';
	else
		return $currency;
}

function cp_contactformpp_add_field_verify ($table, $field, $type = "text") 
{
    global $wpdb;
    $results = $wpdb->get_results("SHOW columns FROM `".$table."` where field='".$field."'");    
    if (!count($results))
    {               
        $sql = "ALTER TABLE  `".$table."` ADD `".$field."` ".$type; 
        $wpdb->query($sql);
    }
}

function cp_contactformpp_check_upload($uploadfiles) {
    $filetmp = $uploadfiles['tmp_name'];
    //clean filename and extract extension
    $filename = $uploadfiles['name'];
    // get file info
    $filetype = wp_check_filetype( basename( $filename ), null );

    if ( in_array ($filetype["ext"],array("php","asp","aspx","cgi","pl","perl","exe","cmd","js","msi")) )
        return false;
    else
        return true;
}

function cp_contactformpp_check_IPN_verification() {
    global $wpdb;

    $_GET["itemnumber"] = $_GET['cp_contactformpp_ipncheck'];

    $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];
    $payment_type = $_POST['payment_type'];

    if (CP_CONTACTFORMPP_STEP2_VRFY)
    {
	    if ($payment_status != 'Completed' && $payment_type != 'echeck')
	        return;
        
	    if ($payment_type == 'echeck' && $payment_status != 'Pending')
	        return;
    }
	$str = '';
    if ($_POST["first_name"]) $str .= 'Buyer: '.$_POST["first_name"]." ".$_POST["last_name"]."\n";
    if ($_POST["payer_email"]) $str .= 'Payer email: '.$_POST["payer_email"]."\n";
	if ($_POST["residence_country"]) $str .= 'Country code: '.$_POST["residence_country"]."\n";
	if ($_POST["payer_status"]) $str .= 'Payer status: '.$_POST["payer_status"]."\n";
	if ($_POST["protection_eligibility"]) $str .= 'Protection eligibility: '.$_POST["protection_eligibility"]."\n";

	if ($_POST["item_name"]) $str .= 'Item: '.$_POST["item_name"]."\n";
	if ($_POST["payment_gross"])
	     $str .= 'Payment: '.$_POST["payment_gross"]." ".$_POST["mc_currency"]." (Fee: ".$_POST["payment_fee"].")"."\n";
	else if ($_POST["mc_gross"])
	     $str .= 'Payment: '.$_POST["mc_gross"]." ".$_POST["mc_currency"]." (Fee: ".$_POST["mc_fee"].")"."\n";
	if ($_POST["payment_date"]) $str .= 'Payment date: '.$_POST["payment_date"];
	if ($_POST["payment_type"]) $str .= 'Payment type/status: '.$_POST["payment_type"]."/".$_POST["payment_status"]."\n";
	if ($_POST["business"]) $str .= 'Business: '.$_POST["business"]."\n";
	if ($_POST["receiver_email"]) $str .= 'Receiver email: '.$_POST["receiver_email"]."\n";

    $myrows = $wpdb->get_results( "SELECT * FROM ".CP_CONTACTFORMPP_POSTS_TABLE_NAME." WHERE id=".intval($_GET['itemnumber']));
    $params = unserialize($myrows[0]->posted_data);

    if ($myrows[0]->paid == 0)
    {
        $wpdb->query("UPDATE ".CP_CONTACTFORMPP_POSTS_TABLE_NAME." SET paid=1,paypal_post='".esc_sql($str)."' WHERE id=".intval($_GET['itemnumber']));
        cp_contactformpp_process_ready_to_go_reservation($_GET["itemnumber"], $payer_email, $params);
        echo 'OK - processed';
    }
    else
        echo 'OK - already processed';
    
    exit();
}


function cp_contactformpp_check_IPN_verification_express() {
    global $wpdb;

    $itemnumber = intval($_GET['cp_contactformpp_ipncheckexpress']);
    $myrows = $wpdb->get_results( "SELECT * FROM ".CP_CONTACTFORMPP_POSTS_TABLE_NAME." WHERE id=".intval($itemnumber));
    $params = unserialize($myrows[0]->posted_data);

    if (!defined('CP_CONTACTFORMPP_ID'))
        define ('CP_CONTACTFORMPP_ID',$myrows[0]->formid);    
        
	try 
	{	
        $products = array();
        $products[0]['ItemName'] = $params["PayPal Product Name"]; //Item Name
	    $products[0]['ItemPrice'] = $params["Cost"]; //Item Price
	    $products[0]['ItemNumber'] = $itemnumber; //Item Number
	    $products[0]['ItemDesc'] = $params["PayPal Product Name"]; //Item Number
	    $products[0]['ItemQty']	= 1; // Item Quantity
	    
	    $charges = array(); //Other important variables like tax, shipping cost
	    $charges['TotalTaxAmount'] = $params["taxamount"];  //Sum of tax for all items in this order. 
	    $charges['HandalingCost'] = 0;  //Handling cost for this order.
	    $charges['InsuranceCost'] = 0;  //shipping insurance cost for this order.
	    $charges['ShippinDiscount'] = 0; //Shipping discount for this order. Specify this as negative number.
	    $charges['ShippinCost'] = 0; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.		        			    
	    
	    @include_once dirname( __FILE__ ) . '/cp_paypal.express.php';
        $ppexp = new DEXBCCF_PayPalEXPC();
        $ppexp->mode = cp_contactformpp_get_option('paypal_mode',CP_CONTACTFORMPP_DEFAULT_PAYPAL_MODE);        
        $ppexp->API_UserName = cp_contactformpp_get_option('paypalexpress_api_username','');        
        $ppexp->API_Password = cp_contactformpp_get_option('paypalexpress_api_password','');
        $ppexp->API_Signature = cp_contactformpp_get_option('paypalexpress_api_signature','');
        $ppexp->currency = strtoupper(cp_contactformpp_get_option('currency', CP_CONTACTFORMPP_DEFAULT_CURRENCY));
        $ppexp->lang = cp_contactformpp_get_option('paypal_language', CP_CONTACTFORMPP_DEFAULT_PAYPAL_LANGUAGE);      			    
        $ppexp->DoExpressCheckoutPayment($products, $charges);

	} catch (Exception $e) {
        echo "Error: ".$e->getMessage();
        exit;
    }	    

    if ($myrows[0]->paid == 0)
    {
        $wpdb->query("UPDATE ".CP_CONTACTFORMPP_POSTS_TABLE_NAME." SET paid=1,paypal_post='".esc_sql($str)."' WHERE id=".intval($itemnumber));
        cp_contactformpp_process_ready_to_go_reservation($itemnumber, $payer_email, $params);        
    }
    
    header('Location: '.cp_contactformpp_get_option('fp_return_page', CP_CONTACTFORMPP_DEFAULT_fp_return_page));
    exit();
}

function cp_contactformpp_process_ready_to_go_reservation($itemnumber, $payer_email = "", $params = array())
{

   global $wpdb;
    $itemnumber = intval($itemnumber);
   
    if (!defined('CP_CONTACTFORMPP_DEFAULT_fp_from_email'))  define('CP_CONTACTFORMPP_DEFAULT_fp_from_email', get_the_author_meta('user_email', get_current_user_id()) );
    if (!defined('CP_CONTACTFORMPP_DEFAULT_fp_destination_emails')) define('CP_CONTACTFORMPP_DEFAULT_fp_destination_emails', CP_CONTACTFORMPP_DEFAULT_fp_from_email);

   $myrows = $wpdb->get_results( "SELECT * FROM ".CP_CONTACTFORMPP_POSTS_TABLE_NAME." WHERE id=".$itemnumber );

   $mycalendarrows = $wpdb->get_results( 'SELECT * FROM '. $wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE .' WHERE `id`='.$myrows[0]->formid);

   if (!defined('CP_CONTACTFORMPP_ID'))
        define ('CP_CONTACTFORMPP_ID',$myrows[0]->formid);

    $buffer_A = $myrows[0]->data;
    $buffer = $buffer_A;

    if ('true' == cp_contactformpp_get_option('fp_inc_additional_info', CP_CONTACTFORMPP_DEFAULT_fp_inc_additional_info))
    {
        $buffer .="ADDITIONAL INFORMATION\n"
              ."*********************************\n"
              ."IP: ".$myrows[0]->ipaddr."\n"
              ."Server Time:  ".date("Y-m-d H:i:s")."\n";
    }

    // 1- Send email
    //---------------------------
    $attachments = array();
    if ('html' == cp_contactformpp_get_option('fp_emailformat', CP_CONTACTFORMPP_DEFAULT_email_format))
        $message = str_replace('<'.'%INFO%'.'>',str_replace("\n","<br />",str_replace('<','&lt;',$buffer)),cp_contactformpp_get_option('fp_message', CP_CONTACTFORMPP_DEFAULT_fp_message));
    else    
        $message = str_replace('<'.'%INFO%'.'>',$buffer,cp_contactformpp_get_option('fp_message', CP_CONTACTFORMPP_DEFAULT_fp_message));    
    foreach ($params as $item => $value)
    {
        $message = str_replace('<'.'%'.$item.'%'.'>',(is_array($value)?(implode(", ",$value)):($value)),$message);
        if (strpos($item,"_link"))
            $attachments[] = $value;
    }
       
    $message = str_replace('<'.'%itemnumber%'.'>',$itemnumber,$message);    
    $subject = cp_contactformpp_get_option('fp_subject', CP_CONTACTFORMPP_DEFAULT_fp_subject);
    $from = cp_contactformpp_get_option('fp_from_email', CP_CONTACTFORMPP_DEFAULT_fp_from_email);
    $to = explode(",",cp_contactformpp_get_option('fp_destination_emails', CP_CONTACTFORMPP_DEFAULT_fp_destination_emails));
    if ('html' == cp_contactformpp_get_option('fp_emailformat', CP_CONTACTFORMPP_DEFAULT_email_format)) $content_type = "Content-Type: text/html; charset=utf-8\n"; else $content_type = "Content-Type: text/plain; charset=utf-8\n";
    $replyto = $myrows[0]->notifyto;
    
    if (!strpos($from,">"))
       $from = '"'.$from.'" <'.$from.'>';
           
    foreach ($to as $item)
        if (trim($item) != '')
        {
            wp_mail(trim($item), $subject, $message,
                "From: ".$from."\r\n".
                ($replyto!=''?"Reply-To: \"$replyto\" <".$replyto.">\r\n":'').
                $content_type.
                "X-Mailer: PHP/" . phpversion(), $attachments);
        }

    // 2- Send copy to user
    //---------------------------
    $to = cp_contactformpp_get_option('cu_user_email_field', CP_CONTACTFORMPP_DEFAULT_cu_user_email_field);
    $_POST[$to] = $myrows[0]->notifyto;
    if ((trim($_POST[$to]) != '' || $payer_email != '') && 'true' == cp_contactformpp_get_option('cu_enable_copy_to_user', CP_CONTACTFORMPP_DEFAULT_cu_enable_copy_to_user))
    {
        if ('html' == cp_contactformpp_get_option('cu_emailformat', CP_CONTACTFORMPP_DEFAULT_email_format))
            $message = str_replace('<'.'%INFO%'.'>',str_replace("\n","<br />",str_replace('<','&lt;',$buffer_A)).'</pre>',cp_contactformpp_get_option('cu_message', CP_CONTACTFORMPP_DEFAULT_cu_message));
        else    
            $message = str_replace('<'.'%INFO%'.'>',$buffer_A,cp_contactformpp_get_option('cu_message', CP_CONTACTFORMPP_DEFAULT_cu_message));
        foreach ($params as $item => $value)
            $message = str_replace('<'.'%'.$item.'%'.'>',(is_array($value)?(implode(", ",$value)):($value)),$message);
        $message = str_replace('<'.'%itemnumber%'.'>',$itemnumber,$message);        
        $subject = cp_contactformpp_get_option('cu_subject', CP_CONTACTFORMPP_DEFAULT_cu_subject);
        if ('html' == cp_contactformpp_get_option('cu_emailformat', CP_CONTACTFORMPP_DEFAULT_email_format)) $content_type = "Content-Type: text/html; charset=utf-8\n"; else $content_type = "Content-Type: text/plain; charset=utf-8\n";
        if ($_POST[$to] != '')
            wp_mail(trim($_POST[$to]), $subject, $message,
                    "From: ".$from."\r\n".
                    $content_type.
                    "X-Mailer: PHP/" . phpversion());
        if (strtolower($_POST[$to]) != strtolower($payer_email) && $payer_email != '')
            wp_mail(trim($payer_email), $subject, $message,
                    "From: ".$from."\r\n".
                    $content_type.
                    "X-Mailer: PHP/" . phpversion());
    }

}

function cp_contactformpp_get_field_name ($fieldid, $form) 
{
    if (is_array($form))
        foreach($form as $item)
            if ($item->name == $fieldid)
                return $item->title;
    return $fieldid;
}

function cp_contactformpp_export_csv ()
{
    if (!is_admin())
        return;
    global $wpdb;
    
    if (!defined('CP_CONTACTFORMPP_ID'))
        define ('CP_CONTACTFORMPP_ID',intval($_GET["cal"]));
    
    $form_data = json_decode(cp_contactformpp_cleanJSON(cp_contactformpp_get_option('form_structure', CP_CONTACTFORMPP_DEFAULT_form_structure)));
    
    $cond = '';
    if ($_GET["search"] != '') $cond .= " AND (data like '%".esc_sql($_GET["search"])."%' OR paypal_post LIKE '%".esc_sql($_GET["search"])."%')";
    if ($_GET["dfrom"] != '') $cond .= " AND (`time` >= '".esc_sql($_GET["dfrom"])."')";
    if ($_GET["dto"] != '') $cond .= " AND (`time` <= '".esc_sql($_GET["dto"])." 23:59:59')";
    if (CP_CONTACTFORMPP_ID != 0) $cond .= " AND formid=".CP_CONTACTFORMPP_ID;
    
    $events = $wpdb->get_results( "SELECT * FROM ".CP_CONTACTFORMPP_POSTS_TABLE_NAME." WHERE 1=1 ".$cond." ORDER BY `time` DESC" );
    
    $fields = array("Form ID", "Time", "IP Address", "email", "Paid");
    $values = array();
    foreach ($events as $item)
    {
        $value = array($item->formid, $item->time, $item->ipaddr, $item->notifyto, ($item->paid?"Yes":"No"));
        $data = array();
        if ($item->posted_data)
            $data = unserialize($item->posted_data);
        else if (!$item->paid)
            $data = unserialize($item->paypal_post);
            
        $end = count($fields); 
        for ($i=0; $i<$end; $i++) 
            if (isset($data[$fields[$i]]) ){
                $value[$i] = $data[$fields[$i]];
                unset($data[$fields[$i]]);
            }    
        
        foreach ($data as $k => $d)    
        {
           $fields[] = $k;
           $value[] = $d;
        }        
        $values[] = $value;        
    }    
    
    
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=export".date("Y-m-d").".csv");  
    
    $end = count($fields); 
    for ($i=0; $i<$end; $i++)
        echo '"'.str_replace('"','""', cp_contactformpp_get_field_name($fields[$i],@$form_data[0])).'",';
    echo "\n";
    foreach ($values as $item)    
    {        
        for ($i=0; $i<$end; $i++)
        {
            if (!isset($item[$i])) 
                $item[$i] = '';
            if (is_array($item[$i]))    
                $item[$i] = implode($item[$i],',');                
            echo '"'.str_replace('"','""', $item[$i]).'",';
        }    
        echo "\n";
    }
    
    exit;    
}

function cp_contactformpp_translate_json($str)
{
    $form_data = json_decode(cp_contactformpp_cleanJSON($str));        
    
    $form_data[1][0]->title = __($form_data[1][0]->title,'cpcfwpp');   
    $form_data[1][0]->description = __($form_data[1][0]->description,'cpcfwpp');   
            
    for ($i=0; $i < count($form_data[0]); $i++)    
    {
        $form_data[0][$i]->title = __($form_data[0][$i]->title,'cpcfwpp');   
        $form_data[0][$i]->userhelpTooltip = __($form_data[0][$i]->userhelpTooltip,'cpcfwpp'); 
        $form_data[0][$i]->userhelp = __($form_data[0][$i]->userhelp,'cpcfwpp'); 
        if ($form_data[0][$i]->ftype == 'fCommentArea')
            $form_data[0][$i]->userhelp = __($form_data[0][$i]->userhelp,'cpcfwpp');   
        else 
            if ($form_data[0][$i]->ftype == 'fradio' || $form_data[0][$i]->ftype == 'fcheck' || $form_data[0][$i]->ftype == 'fradio')    
            {
                for ($j=0; $j < count($form_data[0][$i]->choices); $j++)  
                    $form_data[0][$i]->choices[$j] = __($form_data[0][$i]->choices[$j],'cpcfwpp'); 
            } 
    }           
    $str = json_encode($form_data);
    return $str;
}


function cp_contactformpp_update_script_method()
{
    global $wpdb;
    if (!is_admin())
        return;    
    update_option( 'CP_CFPP_LOAD_SCRIPTS', ($_GET['script_load_method']=="1"?false:true) );
    echo '<br />Script Loading Method Updated.';
    exit;
}

function cp_contactformpp_save_options()
{
    global $wpdb;
    if (!defined('CP_CONTACTFORMPP_ID'))
        define ('CP_CONTACTFORMPP_ID',intval($_POST["cp_contactformpp_id"]));


    $verify_nonce = wp_verify_nonce( $_POST['rsave'], 'cfwpp_update_actions_post');
    if (!$verify_nonce)
    {
        echo 'Error: Form cannot be authenticated. Please contact our <a href="http://cfpaypal.dwbooster.com/contact-us">support service</a> for verification and solution. Thank you.';
        return;
    }

    // temporal lines to guarantee migration from previous version    
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypalexpress_api_username'," varchar(250) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypalexpress_api_password'," varchar(250) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypalexpress_api_signature'," varchar(250) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'enable_paypal_expresscredit_yes'," varchar(250) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'enable_paypal_expresscredit_no'," varchar(250) NOT NULL default ''");    
    
    
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'fp_emailformat'," varchar(10) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'cu_emailformat'," varchar(10) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypal_notiemails'," varchar(20) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypal_mode'," varchar(20) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypal_recurrent'," varchar(20) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypal_recurrent_fp'," varchar(20) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypal_identify_prices'," varchar(20) NOT NULL default ''");        
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'cp_emailformat'," varchar(10) NOT NULL default ''");       
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'request_taxes'," varchar(20) NOT NULL default ''");       
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'request_address'," varchar(20) NOT NULL default ''");       
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'donationlayout'," varchar(20) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'paypal_price_field'," varchar(250) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE,'vs_text_submitbtn'," varchar(250) NOT NULL default ''");
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE, "fp_error_page", "varchar(250) DEFAULT '' NOT NULL");  
    
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE, "paypal_recurrent_setup", "varchar(20) DEFAULT '' NOT NULL");  
    cp_contactformpp_add_field_verify($wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE, "paypal_recurrent_times"," varchar(10) NOT NULL default ''");

    if (substr_count($_POST['form_structure'],"\\") > 30)
        foreach ($_POST as $item => $value)
          if (!is_array($value))
              $_POST[$item] = stripcslashes($value);

    $data = array(

                  'fp_from_email' => $_POST['fp_from_email'],
                  'fp_destination_emails' => $_POST['fp_destination_emails'],
                  'fp_subject' => $_POST['fp_subject'],
                  'fp_inc_additional_info' => $_POST['fp_inc_additional_info'],
                  'fp_return_page' => $_POST['fp_return_page'],
                  'fp_error_page' => $_POST['fp_error_page'],
                  'fp_message' => $_POST['fp_message'],
                  'fp_emailformat' => $_POST['fp_emailformat'],

                  'cu_enable_copy_to_user' => $_POST['cu_enable_copy_to_user'],
                  'cu_user_email_field' => $_POST['cu_user_email_field'],
                  'cu_subject' => $_POST['cu_subject'],
                  'cu_message' => $_POST['cu_message'],
                  'cu_emailformat' => $_POST['cu_emailformat'],

                  'enable_paypal' => @$_POST["enable_paypal"],
                  
                  'paypalexpress_api_username' => @$_POST["paypalexpress_api_username"],
                  'paypalexpress_api_password' => @$_POST["paypalexpress_api_password"],
                  'paypalexpress_api_signature' => @$_POST["paypalexpress_api_signature"],
                  'enable_paypal_expresscredit_yes' => @$_POST["enable_paypal_expresscredit_yes"],
                  'enable_paypal_expresscredit_no' => @$_POST["enable_paypal_expresscredit_no"],                  
                  
                  'paypal_notiemails' => @$_POST["paypal_notiemails"],
                  'paypal_email' => $_POST["paypal_email"],
                  'request_cost' => $_POST["request_cost"],
                  'paypal_price_field' => @$_POST["paypal_price_field"],
                  'request_taxes' => $_POST["request_taxes"],
                  'request_address' => $_POST["request_address"],
                  'donationlayout' => $_POST["donationlayout"],
                  'paypal_product_name' => $_POST["paypal_product_name"],
                  'currency' => $_POST["currency"],
                  'paypal_language' => $_POST["paypal_language"],
                  'paypal_mode' => $_POST["paypal_mode"],
                  'paypal_recurrent' => $_POST["paypal_recurrent"],
                  'paypal_recurrent_fp' => $_POST["paypal_recurrent_fp"],
                  'paypal_recurrent_setup' => $_POST["paypal_recurrent_setup"],
                  'paypal_recurrent_times' => $_POST["paypal_recurrent_times"],
                  'paypal_identify_prices' => @$_POST["paypal_identify_prices"],
                  'paypal_zero_payment' => $_POST["paypal_zero_payment"],

                  //'vs_use_validation' => $_POST['vs_use_validation'],
                  'vs_text_is_required' => $_POST['vs_text_is_required'],
                  'vs_text_is_email' => $_POST['vs_text_is_email'],
                  'vs_text_datemmddyyyy' => $_POST['vs_text_datemmddyyyy'],
                  'vs_text_dateddmmyyyy' => $_POST['vs_text_dateddmmyyyy'],
                  'vs_text_number' => $_POST['vs_text_number'],
                  'vs_text_digits' => $_POST['vs_text_digits'],
                  'vs_text_max' => $_POST['vs_text_max'],
                  'vs_text_min' => $_POST['vs_text_min'],
                  'vs_text_submitbtn' => $_POST['vs_text_submitbtn'],

                  'cv_enable_captcha' => $_POST['cv_enable_captcha'],
                  'cv_width' => $_POST['cv_width'],
                  'cv_height' => $_POST['cv_height'],
                  'cv_chars' => $_POST['cv_chars'],
                  'cv_font' => $_POST['cv_font'],
                  'cv_min_font_size' => $_POST['cv_min_font_size'],
                  'cv_max_font_size' => $_POST['cv_max_font_size'],
                  'cv_noise' => $_POST['cv_noise'],
                  'cv_noise_length' => $_POST['cv_noise_length'],
                  'cv_background' => str_replace('#','',$_POST['cv_background']),
                  'cv_border' => str_replace('#','',$_POST['cv_border']),
                  'cv_text_enter_valid_captcha' => $_POST['cv_text_enter_valid_captcha']
	);
    $wpdb->update ( $wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE, $data, array( 'id' => CP_CONTACTFORMPP_ID ));

}

// cp_contactformpp_get_option:
$cp_contactformpp_option_buffered_item = false;
$cp_contactformpp_option_buffered_id = -1;

function cp_contactformpp_get_option ($field, $default_value, $id = '')
{
    if (!defined("CP_CONTACTFORMPP_ID"))
    {
        if (!(isset($_GET["itemnumber"]) && intval($_GET["itemnumber"]) != ''))
            define ("CP_CONTACTFORMPP_ID", 1);
    }    
    if ($id == '') 
        $id = CP_CONTACTFORMPP_ID;
    global $wpdb, $cp_contactformpp_option_buffered_item, $cp_contactformpp_option_buffered_id;
    if ($cp_contactformpp_option_buffered_id == $id)
        $value = @$cp_contactformpp_option_buffered_item->$field;
    else
    {
       $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.CP_CONTACTFORMPP_FORMS_TABLE." WHERE id=".$id );
       $value = $myrows[0]->$field;
       $cp_contactformpp_option_buffered_item = $myrows[0];
       $cp_contactformpp_option_buffered_id  = $id;
    }
    if ($value == '' && $cp_contactformpp_option_buffered_item->form_structure == '')
        $value = $default_value;
        
    if ($field == 'paypal_email' && $value == CP_CONTACTFORMPP_DEFAULT_PAYPAL_EMAIL)    
    {
        $current_user = wp_get_current_user();
        $value = $current_user->user_email;
    }        
    return $value;
}



?>