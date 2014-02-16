<?php
/*
Plugin Name: Aragon-eRH RSS
Plugin URI: http://infinity88.ca
Description: This plugin takes the rss field available from your Oracle Aragon-eRH and convert it to a list you can use in a widget or a post.
Version: 1.0
Author: Omar Mir
Author URI: http://infinity88.ca
License: GPL3
*/

/**
 * Created by JetBrains PhpStorm.
 * User: Omar
 * Date: 11/12/13
 * Time: 11:01 PM
 */

include(sprintf("%s/lib/core.php", dirname(__FILE__)));

include(sprintf("%s/lib/aerh-widget.php", dirname(__FILE__)));

if(!class_exists('Aragon_eRH_RSS')) {

    class Aragon_eRH_RSS {

        private static $Aragon_eRH_db_version = '0.5';
        private static $core;

        public function __construct() {
            self::$core = new Aragon_eRH();

            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'add_menu'));
            add_action('init', array($this, 'aerh_script_enqueuer'));
            add_action('init', array($this, 'aerh_endpoint'));

            add_action('wp_ajax_Aragon_eRH_Sync', array($this, 'Aragon_eRH_Sync'));
            add_action('wp_ajax_Aragon_eRH_Bulk_URL', array($this, 'Aragon_eRH_Bulk_URL'));
            add_action('wp_ajax_Aragon_eRH_Get_Table', array($this, 'Aragon_eRH_Get_Table'));

            add_action('wp_ajax_nopriv_Aragon_eRH_Sync', array($this, 'must_login'));
            add_action('wp_ajax_nopriv_Aragon_eRH_Get_Table', array($this, 'must_login'));

            add_action('widgets_init', create_function('', 'return register_widget("Aragon_eRH_Widget");'));

            add_shortcode('Aragon-eRH', array($this, 'register_shortcode'));

            add_action( 'parse_query', array($this, 'aerh_parse_query' ));
        }

        public static function activate() {
            self::plugin_update_db_check();

            self::aerh_endpoint();

            flush_rewrite_rules(false);
        }

        public static function deactivate() {
            flush_rewrite_rules(false);
        }

        public function admin_init() {
            $this->init_settings();
        }

        public function init_settings() {
            register_setting('Aragon_eRH_RSS-group', 'Aragon_eRH_setting_rss-url');
            register_setting('Aragon_eRH_RSS-group', 'Aragon_eRH_setting_rss-bulk-url');
        }

        public function add_menu()
        {
            add_options_page('Aragon-eRH RSS Settings', 'Aragon-eRH', 'manage_options', 'Aragon_eRH', array(&$this, 'plugin_settings_page'));
        }

        public function plugin_settings_page() {
            if(!current_user_can('manage_options'))
            {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        }

        public function register_shortcode($atts) {
            self::$core->do_aerh_shortcode($atts);
        }

        public function create_data_table() {
            global $wpdb;

            $table_name = $wpdb->prefix . "Aragon_eRH_jobs";

            $sql =    "CREATE TABLE $table_name (
                      id mediumint(9) NOT NULL AUTO_INCREMENT,
                      url VARCHAR(255) DEFAULT '' NOT NULL,
                      title varchar(150) DEFAULT '' NOT NULL,
                      pubDate varchar(50) DEFAULT '' NOT NULL,
                      description text NOT NULL,
                      UNIQUE KEY id (id)
                      );";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            update_option( "Aragon_eRH_db_version", self::$Aragon_eRH_db_version );

        }

        public function plugin_update_db_check() {
            if (get_site_option( 'Aragon_eRH_db_version' ) != self::$Aragon_eRH_db_version) {
                self::create_data_table();
            }
        }

        public function Aragon_eRH_Sync() {
            if ( !wp_verify_nonce( $_REQUEST['nonce'], "Aragon_eRH_Sync_nonce")) {
                exit("No naughty business please");
            }
            self::$core->aerh_xml_parser();
        }

        public function Aragon_eRH_Bulk_URL() {
            if ( !wp_verify_nonce( $_REQUEST['nonce'], "Aragon_eRH_Bulk_nonce")) {
                exit("No naughty business please");
            }
            self::$core->aerh_create_bulk_url();
        }

        public function Aragon_eRH_Get_Table() {
            self::$core->aerh_get_table();
        }

        public function aerh_script_enqueuer() {
            wp_register_script( 'jquery.scrollbox', WP_PLUGIN_URL.'/aragon-erh-rss/js/jquery.scrollbox.js', array('jquery') );
            wp_register_script( 'aerh_script', WP_PLUGIN_URL.'/aragon-erh-rss/js/aerh.js', array('jquery') );
            wp_localize_script( 'aerh_script', 'aerhAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'ajaxnonce' => wp_create_nonce('Aragon_eRH_Sync')));

            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery.scrollbox' );
            wp_enqueue_script( 'aerh_script' );

        }

        public function must_login() {
            echo "You must log in to sync";
            die();
        }

        public function aerh_endpoint(){
            add_rewrite_endpoint( 'aerh', EP_ROOT );

        }

        public function aerh_parse_query( $query ){
            if( isset( $query->query_vars['aerh'] ) ){
                self::$core->aerh_xml_parser_bulk($query->query_vars['aerh']);
                exit;
            }
        }


    }



    /* Runs when plugin is activated */
    register_activation_hook(__FILE__, array('Aragon_eRH_RSS', 'activate'));

    /* Runs on plugin deactivation*/
    register_deactivation_hook(__FILE__, array('Aragon_eRH_RSS', 'deactivate'));

    $wp_plugin_template = new Aragon_eRH_RSS();

    // Add a link to the settings page onto the plugin page
    if(isset($wp_plugin_template)) {
        // Add the settings link to the plugins page
        function plugin_settings_link($links) {
            $settings_link = '<a href="options-general.php?page=Aragon_eRH_RSS">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
    }

}