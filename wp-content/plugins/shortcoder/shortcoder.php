<?php
/*
Plugin Name: Shortcoder
Plugin URI: https://www.aakashweb.com
Description: Shortcoder is a plugin which allows to create a custom shortcode and store HTML, Javascript and other snippets in it. So if that shortcode is used in any post or pages, then the code stored in the shortcode get executed in that place. You can create a shortcode for Youtube videos, adsense ads, buttons and more.
Author: Aakash Chakravarthy
Version: 4.0.2
Author URI: https://www.aakashweb.com/
*/

define( 'SC_VERSION', '4.0.2');
define( 'SC_PATH', plugin_dir_path( __FILE__ ) ); // All have trailing slash
define( 'SC_URL', plugin_dir_url( __FILE__ ) );
define( 'SC_ADMIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) . 'admin' ) );
define( 'SC_BASE_NAME', plugin_basename( __FILE__ ) );

class Shortcoder{
    
    public static function init(){
        
        add_action( 'plugins_loaded', array( __class__, 'load_text_domain' ) );
        
        register_activation_hook( __FILE__, array( __class__, 'on_activate' ) );
        
        add_filter( 'the_content', array( __class__, 'wp_44_workaround' ), 5 );
        
        // Register the shortcode
        add_shortcode( 'sc', array( __class__, 'execute_shortcode' ) );
        
        // Include the required
        self::includes();
        
    }
    
    public static function list_all(){
        
        $shortcodes = get_option( 'shortcoder_data' );
        
        return empty( $shortcodes ) ? array() : $shortcodes;
        
    }
    
    public static function includes(){
        
        include_once( SC_PATH . 'includes/metadata.php' );
        include_once( SC_PATH . 'admin/sc-admin.php' );
        
    }
    
    public static function execute_shortcode( $atts, $content ) { 
        
        $shortcodes = self::list_all();
        
        if( empty( $shortcodes ) ){
            return '';
        }
        
        // Get the Shortcode name
        if(isset($atts[0])){
            $sc_name = str_replace(array('"', "'", ":"), '', $atts[0]);
            unset($atts[0]);
        }else{
            // Old version with "name" param support
            if(array_key_exists("name", $atts)){
                $tVal = $atts['name'];
                if(array_key_exists($tVal, $shortcodes)){
                    $sc_name = $tVal;
                    unset($atts['name']);
                }
            }
        }
        
        if(!isset($sc_name)){
            return '';
        }
        
        // Check whether shortcoder can execute
        if( self::check_conditions( $sc_name ) ){
        
            $sc_content_final = '';
        
            // If SC has parameters, then replace it
            if( !empty( $atts ) ){
                
                $keys = array();
                $values = array();
                $i = 0;
        
                // Seperate key and value from atts
                foreach( $atts as $k => $v ){
                    if( $k !== 0 ){
                        $keys[$i] = "%%" . $k . "%%";
                        $values[$i] = $v;
                    }
                    $i++;
                }
                
                // Replace the params
                $sc_content = $shortcodes[ $sc_name ][ 'content' ]; 
                $sc_content_rep1 = str_ireplace( $keys, $values, $sc_content );
                $sc_content_final = preg_replace( '/%%[^%\s]+%%/', '', $sc_content_rep1 );
                
            }
            else{
            
                // If the SC has no params, then replace the %%vars%%
                $sc_content = $shortcodes[ $sc_name ][ 'content' ]; 
                $sc_content_final = preg_replace( '/%%[^%\s]+%%/', '', $sc_content );
                
            }
            
            $sc_content_final = self::replace_wp_params( $sc_content_final );
            return '<!-- Start shortcoder -->' . do_shortcode( $sc_content_final ) . '<!-- End shortcoder v' . SC_VERSION . '-->';
            
        }else{
            return '<!-- Shortcoder conditions not met -->';
        }
    }
    
    public static function check_conditions( $name ){
        
        $shortcodes = self::list_all();
        
        if( array_key_exists( $name, $shortcodes ) ){
            
            $sc = wp_parse_args( $shortcodes[ $name ], self::defaults() );
            
            $devices = $sc[ 'devices' ];
            if( $devices == 'mobile_only' && !wp_is_mobile() ){
                return false;
            }
            
            if( $devices == 'desktop_only' && wp_is_mobile() ){
                return false;
            }
            
            if( $sc[ 'disabled' ] == 0 ){
                if( current_user_can( 'level_10' ) && $sc[ 'hide_admin' ] == 1 ){
                    return false;
                }else{
                    return true;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
        
    }
    
    public static function replace_wp_params( $content ){
        
        $params = self::wp_params_list();
        $metadata = Shortcoder_Metadata::metadata();
        $to_replace = array();
        
        foreach( $params as $id => $name ){
            if( array_key_exists( $id, $metadata ) ){
                $placeholder = '$$' . $id . '$$';
                $to_replace[ $placeholder ] = $metadata[ $id ];
            }
        }
        
        $content = strtr( $content, $to_replace );
        
        return $content;
        
    }
    
    public static function wp_params_list(){
        return apply_filters( 'sc_mod_wp_params', array(
            'url' => 'URL of the post/location',
            'title' => 'Title of the post/location',
            'short_url' => 'Short URL of the post/location',
            
            'post_id' => 'Post ID',
            'post_image' => 'Post featured image URL',
            'post_excerpt' => 'Post excerpt',
            'post_author' => 'Post author',
            'post_date' => 'Post date',
            'post_comments_count' => 'Post comments count'
        ));
    }
    
    public static function on_activate(){
        
        $shortcodes = self::list_all();
        $sc_flags = get_option( 'shortcoder_flags' );
        
        // Move the flag version fix to sc_flags option
        if( isset( $shortcodes[ '_version_fix' ] ) ){
            unset( $shortcodes['_version_fix'] );
            update_option( 'shortcoder_data', $shortcodes );
        }
        
        $sc_flags[ 'version' ] = SC_VERSION;
        update_option( 'shortcoder_flags', $sc_flags );

    }
    
    public static function defaults(){
        return array(
            'content' => '',
            'disabled' => 0,
            'hide_admin' => 0,
            'devices' => 'all'
        );
    }
    
    public static function wp_44_workaround( $content ){
        return str_replace( '[sc:', '[sc name=', $content );
    }
    
    public static function load_text_domain(){
        
        load_plugin_textdomain( 'shortcoder', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
        
    }
    
}

Shortcoder::init();

?>