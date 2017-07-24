<?php

class Shortcoder_Admin{
    
    private static $pagehook = 'settings_page_shortcoder';
    
    public static function init(){
        
        // Add menu
        add_action( 'admin_menu', array( __class__, 'add_menu' ) );
        
        // Enqueue the scripts and styles
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        
        // Register the action for admin ajax features
        add_action( 'wp_ajax_sc_admin_ajax', array( __CLASS__, 'admin_ajax' ) );
        
        // Register action links
        add_filter( 'plugin_action_links_' . SC_BASE_NAME, array( __CLASS__, 'action_links' ) );
        
        // Add Quick Tag button to the editor
        add_action( 'admin_footer', array( __class__, 'add_qt_button' ) );
        
        // Add TinyMCE button
        add_action( 'admin_init', array( __class__, 'register_mce' ) );
        
    }
    
    public static function add_menu(){
        
        add_options_page( 'Shortcoder', 'Shortcoder', 'manage_options', 'shortcoder', array( __class__, 'admin_page' ) );
        
    }
    
    public static function enqueue_scripts( $hook ){
        
        if( $hook == self::$pagehook ){
            
            wp_enqueue_style( 'sc-admin-css', SC_ADMIN_URL . '/css/style.css', array(), SC_VERSION );
            
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'sc-admin-js', SC_ADMIN_URL . '/js/script.js', array( 'jquery' ), SC_VERSION );
        
        } 
    }
    
    public static function admin_page(){
        
        echo '<div class="wrap">';
        echo '<div class="head_wrap">';
        echo '<h1 class="sc_title">Shortcoder <span class="title-count">' . SC_VERSION . '</span></h1>';
        self::top_sharebar();
        self::print_notice();
        echo '</div>';
        
        echo '<div id="content">';
        
        $g = self::clean_get();
        
        if( !isset( $g[ 'action' ] ) ){
            $g[ 'action' ] = 'list';
        }
        
        if( $g[ 'action' ] == 'list' ){
            self::list_shortcodes();
        }
        
        if( $g[ 'action' ] == 'edit' ){
            self::edit_shortcode();
        }
        
        if( $g[ 'action' ] == 'new' ){
            self::new_shortcode();
        }
        
        echo '</div>';
        
        self::page_bottom();
        
        echo '</div>';
        
    }
    
    public static function list_shortcodes(){
        
        $shortcodes = Shortcoder::list_all();
        
        echo '<h3 class="page_title">' . __( 'List of shortcodes created', 'shortcoder' );
        echo '<a href="' . self::get_link(array( 'action' => 'new' )) . '" class="button button-primary sc_new_btn"><span class="dashicons dashicons-plus"></span> ' . __( 'Create a new shortcode', 'shortcoder' ) . '</a>';
        echo '</h3>';
        
        echo '<ul class="sc_list" data-empty="' . __( 'No shortcodes are created. Go ahead create one !', 'shortcoder' ) . '">';
        foreach( $shortcodes as $name => $data ){
            
            $data = wp_parse_args( $data, Shortcoder::defaults() );
            
            $link = self::get_link(array(
                'action' => 'edit',
                'name' => $name
            ));
            
            $delete_link = self::get_link(array(
                'action' => 'sc_admin_ajax',
                'do' => 'delete',
                'name' => $name,
                '_wpnonce' => wp_create_nonce( 'sc_delete_nonce' )
            ), 'admin-ajax.php' );
            
            $disabled_text = ( $data[ 'disabled' ] == '1' ) ? '<small class="disabled_text">' . __( 'Temporarily disabled', 'shortcoder' ) . '</small>' : '';
            
            echo '<li>';
            echo '<a href="' . $link . '" class="sc_link" title="' . __( 'Edit shortcode', 'shortcoder' ) . '">' . $name . $disabled_text . '</a>';
            echo '<span class="sc_controls">';
            echo '<a href="#" class="sc_copy" title="' . __( 'Copy shortcode', 'shortcoder' ) . '"><span class="dashicons dashicons-editor-code"></span></a>';
            echo '<a href="' . $delete_link . '" class="sc_delete" title="' . __( 'Delete', 'shortcoder' ) . '"><span class="dashicons dashicons-trash"></span></a>';
            echo '</span>';
            
            echo '<input type="text" value="' . self::get_shortcode( $name ) . '" class="sc_copy_box" readonly="readonly" title="' . __( 'Copy shortcode', 'shortcoder' ) . '" />';
            
            echo '</li>';
            
        }
        echo '</ul>';
            
        
        
    }
    
    public static function new_shortcode(){
        self::edit_shortcode( 'new' );
    }
    
    public static function edit_shortcode( $action = 'edit' ){
        
        self::save_shortcode();
        
        $shortcodes = Shortcoder::list_all();
        $g = self::clean_get();
        
        $page_title = __( 'New shortcode', 'shortcoder' );
        $action_btn = __( 'Create shortcode', 'shortcoder' );
        $sc_name = '';
        $values = array();
        
        if( $action == 'edit' ){
            
            $page_title = __( 'Edit shortcode', 'shortcoder' );
            $action_btn = __( 'Save settings', 'shortcoder' );
            
            if( !( isset( $g[ 'name' ] ) && array_key_exists( $g[ 'name' ], $shortcodes ) ) ){
                echo '<p align="center">' . __( 'Invalid shortcode or Shortcode does not exist !' ) . '</p>';
                return false;
            }
            
            $sc_name = $g[ 'name' ];
            $values = $shortcodes[ $sc_name ];
            
        }
        
        $values = wp_parse_args( $values, Shortcoder::defaults() );
        
        echo '<h3 class="page_title">' . $page_title;
        echo '<a href="' . self::get_link() . '" class="button sc_back_btn"><span class="dashicons dashicons-arrow-left-alt2"></span> ' . __( 'Back', 'shortcoder' ) . '</a>';
        echo '</h3>';
        
        echo '<form method="post">';
        
        echo '<div class="sc_section">';
        echo '<label for="sc_name">' . __( 'Name', 'shortcoder' ) . '</label>';
        echo '<div class="sc_name_wrap"><input type="text" id="sc_name" name="sc_name" value="' . esc_attr( $sc_name ) . '" class="widefat" required="required" ' . ( ( $action == 'edit' ) ? 'readonly="readonly"' : 'placeholder="' . __( 'Enter a name for the shortcode, case sensitive', 'shortcoder' ) . '"' ) . ' pattern="[a-zA-z0-9 \-]+" title="' . __( 'Allowed characters A to Z, a to z, 0 to 9, hyphens, underscores and space', 'shortcoder' ) . '" />';
        echo ( $action == 'edit' ) ? '<div class="copy_shortcode">Your shortcode is - <strong contenteditable>' . self::get_shortcode( $sc_name ) . '</strong></div>' : '';
        echo '</div></div>';
        
        echo '<div class="sc_section">';
        echo '<label for="sc_content">' . __( 'Shortcode content', 'shortcoder' ) . '</label>';
        wp_editor( $values[ 'content' ], 'sc_content', array( 'wpautop'=> false, 'textarea_rows'=> 12 ) );
        echo '</div>';
        
        echo '<h4>' . __( 'Settings', 'shortcoder' ) . '</h4>';
        echo '<div class="sc_section">';
        echo '<label><input type="checkbox" name="sc_disable" value="1" ' . checked( $values[ 'disabled' ], '1', false ) . '/> ' . __( 'Temporarily disable this shortcode', 'shortcoder' ) . '</label>';
        echo '<label><input type="checkbox" name="sc_hide_admin" value="1" ' . checked( $values[ 'hide_admin' ], '1', false ) . '/> ' . __( 'Disable this Shortcode for administrators' ) . '</label>';
        echo '</div>';
        
        $device_options = array(
            'all' => __( 'On both desktop and mobile devices', 'shortcoder' ),
            'mobile_only' => __( 'On mobile devices alone', 'shortcoder' ),
            'desktop_only' => __( 'On desktops alone', 'shortcoder' )
        );
        
        echo '<h4>' . __( 'Visibility', 'shortcoder' ) . '</h4>';
        echo '<div class="sc_section">';
        echo '<label>' . __( 'Show this shortcode', 'shortcoder' );
        echo '<select name="sc_devices">';
        foreach( $device_options as $id => $name ){
            echo '<option value="' . $id . '" ' . selected( $values[ 'devices' ], $id ) . '>' . $name . '</option>';
        }
        echo '</select></label>';
        echo '</div>';
        
        wp_nonce_field( 'sc_edit_nonce' );
        
        echo '<footer class="page_footer">';
        echo '<button class="button button-primary">' . $action_btn . '</button>';
        
        if( $action == 'edit' ){
            $delete_link = self::get_link(array(
                'action' => 'sc_admin_ajax',
                'do' => 'delete',
                'name' => $sc_name,
                '_wpnonce' => wp_create_nonce( 'sc_delete_nonce' )
            ), 'admin-ajax.php' );
            echo '<a href="' . $delete_link . '" class="button sc_delete_ep" title="' . __( 'Delete', 'shortcoder' ) . '"><span class="dashicons dashicons-trash"></span></a>';
        }
        
        echo '</footer>';
        
        echo '</form>';
        
        $sc_wp_params = Shortcoder::wp_params_list();
        
        echo '<ul class="params_wrap">';
        echo '<li>' . __( 'WordPress information', 'shortcoder' ) . '<ul class="wp_params">';
        foreach( $sc_wp_params as $id => $name ){
            echo '<li data-id="' . $id . '">' . $name . '</li>';
        }
        echo '</ul></li>';
        echo '<li>' . __( 'Custom parameter', 'shortcoder' ) . '<ul>';
        echo '<li class="cp_form"><h4>' . __( 'Enter custom parameter name', 'shortcoder' ) . '</h4>';
                echo '<input type="text" class="cp_box" pattern="[a-zA-Z0-9]+"/> <button class="button cp_btn">' . __( 'Insert parameter', 'shortcoder' ) . '</button><p class="cp_info"><small>' . __( 'Only alphabets and numbers allowed. Custom parameters are case insensitive', 'shortcoder' ) . '</small></p></li>';
        echo '</ul></li>';
        echo '</ul>';
    }
    
    public static function save_shortcode(){
        
        if( $_POST && check_admin_referer( 'sc_edit_nonce' ) ){
            
            $p = wp_parse_args( self::clean_post(), array(
                'sc_name' => '',
                'sc_content' => '',
                'sc_disable' => 0,
                'sc_hide_admin' => 0,
                'sc_devices' => 'all',
            ));
            
            if( !trim( $p[ 'sc_name' ] ) ){
                self::print_notice( 0 );
                return false;
            }
            
            $shortcodes = Shortcoder::list_all();
            $name = self::clean_name( $p[ 'sc_name' ] );
            $values = array(
                'content' => $p[ 'sc_content' ],
                'disabled' => $p[ 'sc_disable' ],
                'hide_admin' => $p[ 'sc_hide_admin' ],
                'devices' => $p[ 'sc_devices' ]
            );
            
            if( array_key_exists( $name, $shortcodes ) ){
                self::print_notice( 2 );
            }else{
                self::print_notice( 1 );
            }
            
            $shortcodes[ $name ] = $values;
            
            update_option( 'shortcoder_data', $shortcodes );
            
            /*
            wp_safe_redirect( self::get_link( array(
                'action' => 'edit',
                'name' => urlencode( $name ),
                'msg' => ( $todo == 'new' ) ? 1 : 2
            )));*/
        }
        
    }
    
    public static function delete_shortcode( $name ){
        
        $shortcodes = Shortcoder::list_all();
        
        if( array_key_exists( $name, $shortcodes ) ){
            unset( $shortcodes[ $name ] );
            update_option( 'shortcoder_data', $shortcodes );
            return true;
        }else{
            return false;
        }
        
    }
    
    public static function get_link( $params = array(), $page = 'options-general.php' ){
        
        $params[ 'page' ] = 'shortcoder';
        return add_query_arg( $params, admin_url( $page ) );
        
    }
    
    public static function get_shortcode( $name = '' ){
        return esc_attr( '[sc name="' . $name . '"]' );
    }
    
    public static function admin_ajax(){
        
        $g = self::clean_get();
        
        if( $g[ 'do' ] == 'delete' && isset( $g[ 'name' ] ) && check_admin_referer( 'sc_delete_nonce' ) ){
            if( self::delete_shortcode( $g[ 'name' ] ) ){
                echo 'DELETED';
            }else{
                echo 'FAILED';
            }
        }
        
        if( $g[ 'do' ] == 'insert_shortcode' ){
            include_once( 'sc-insert.php' );
        }
        
        die(0);
    }
    
    public static function add_qt_button(){
        
        $screen = get_current_screen();
        if( self::$pagehook == $screen->id )
            return;
        
        echo '
        <script>
        window.onload = function(){
            if( typeof QTags === "function" ){
                QTags.addButton( "QT_sc_insert", "Shortcoder", sc_show_insert );
            }
        }
        function sc_show_insert(){
            tb_show( "Insert a Shortcode", "' . admin_url( 'admin-ajax.php?action=sc_admin_ajax&do=insert_shortcode&TB_iframe=true' ) . '" );
        }
        </script>';
    }
    
    public static function register_mce(){
        add_filter( 'mce_buttons', array( __class__, 'register_mce_button' ) );
        add_filter( 'mce_external_plugins', array( __class__, 'register_mce_js' ) );
    }
    
    public static function register_mce_button( $buttons ){
        
        if( self::is_sc_admin() )
            return $buttons;
        
        array_push( $buttons, 'separator', 'shortcoder' );
        return $buttons;
    }
    
    public static function register_mce_js( $plugins ){
        
        if( self::is_sc_admin() )
            return $plugins;
        
        $plugins[ 'shortcoder' ] =  SC_ADMIN_URL . '/js/tinymce/editor_plugin.js';
        return $plugins;
    }
    
    public static function page_bottom(){
        
        echo '<div class="coffee_box">
        <div class="coffee_amt_wrap">
        <p><select class="coffee_amt">
            <option value="2">$2</option>
            <option value="3">$3</option>
            <option value="4">$4</option>
            <option value="5" selected="selected">$5</option>
            <option value="6">$6</option>
            <option value="7">$7</option>
            <option value="8">$8</option>
            <option value="9">$9</option>
            <option value="10">$10</option>
            <option value="11">$11</option>
            <option value="12">$12</option>
            <option value="">Custom</option>
        </select></p>
        <a class="button button-primary buy_coffee_btn" href="https://www.paypal.me/vaakash/5" data-link="https://www.paypal.me/vaakash/" target="_blank">Buy me a coffee !</a>
        </div>
        <h2>Buy me a coffee !</h2>
        <p>Thank you for using Shortcoder. If you found the plugin useful buy me a coffee ! Your donation will motivate and make me happy for all the efforts. You can donate via PayPal.</p>';
        echo '</div>';
        
        echo '<p class="credits_box"><img src="' . SC_ADMIN_URL . '/images/aw.png" /> Created by <a href="https://goo.gl/aHKnsM" target="_blank">Aakash Chakravarthy</a> - Follow me on <a href="https://twitter.com/vaakash", target="_blank">Twitter</a>, <a href="https://fb.com/aakashweb" target="_blank">Facebook</a>, <a href="https://www.linkedin.com/in/vaakash/" target="_blank">LinkedIn</a>. Check out <a href="https://goo.gl/OAxx4l" target="_blank">my other works</a>.
        
        <a href="https://goo.gl/ltvnIE" class="rate_link" target="_blank">Rate <span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span> if you like Shortcoder</a>
        
        </p>';
        
    }
    
    public static function top_sharebar(){
        echo '
        <div class="top_sharebar">
        
        <a href="https://goo.gl/r8Qr7Y" class="help_link" target="_blank" title="Help"><span class="dashicons dashicons-editor-help"></span></a>
        <a href="https://goo.gl/URfxp2" class="help_link" target="_blank" title="Report issue"><span class="dashicons dashicons-flag"></span></a>
        
        <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
        <a class="a2a_button_twitter"></a>
        <a class="a2a_button_facebook"></a>
        <a class="a2a_button_google_plus"></a>
        <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
        </div>
        
        <div class="admin_sb share_text">Share &amp; talk about<br/> Shortcoder</div>
        
        </div>';
        
        echo '
        <script>
        var a2a_config = a2a_config || {};
        a2a_config.linkname = "Shortcoder - A WordPress plugin to create custom Shortcodes for HTML, Javascript snippets !";
        a2a_config.linkurl = "https://www.aakashweb.com/wordpress-plugins/shortcoder/";
        a2a_config.templates = {
            twitter: "Check out ${title} ${link} - @vaakash"
        };
        </script>
        <script async src="https://static.addtoany.com/menu/page.js"></script>
        ';
    }
    
    public static function action_links( $links ){
        array_unshift( $links, '<a href="https://goo.gl/qMF3iE" target="_blank">Donate</a>' );
        array_unshift( $links, '<a href="'. esc_url( admin_url( 'options-general.php?page=shortcoder' ) ) .'">⚙️ Settings</a>' );
        return $links;
    }
    
    public static function print_notice( $id = '' ){
        
        $g = self::clean_get();
        $type = 'success';
        $msg = '';
        
        if( $id == '' ){
            if( !isset( $g[ 'msg' ] ) ){
                return false;
            }
            $id = $g[ 'msg' ];
        }
        
        if( $id == 0 ){
            $msg = __( 'Shortcode name is empty. Cannot save settings !', 'shortcoder' );
            $type = 'error';
        }
        
        if( $id == 1 ){
            $msg = __( 'Shortcode created successfully', 'shortcoder' );
        }
        
        if( $id == 2 ){
            $msg = __( 'Shortcode updated successfully', 'shortcoder' );
        }
        
        if( $id == 3 ){
            $msg = __( 'Shortcode deleted successfully', 'shortcoder' );
        }
        
        if( $msg != '' ){
            echo '<div class="notice notice-' . $type . ' is-dismissible"><p>' . $msg . '</p></div>';
        }
    }
    
    public static function clean_name( $name = '' ){
        
        return trim( preg_replace('/[^0-9a-zA-Z\- _]/', '', $name ) );
        
    }
    
    public static function clean_get(){
        
        foreach( $_GET as $k=>$v ){
            $_GET[$k] = sanitize_text_field( $v );
        }

        return $_GET;
    }
    
    public static function clean_post(){
        
        return stripslashes_deep( $_POST );
        
    }
    
    public static function is_sc_admin(){
        
        if( !function_exists( 'get_current_screen' ) )
            return false;
        
        $screen = get_current_screen();
        if( self::$pagehook == $screen->id ){
            return true;
        }else{
            return false;
        }
        
    }
    
}

Shortcoder_Admin::init();

?>