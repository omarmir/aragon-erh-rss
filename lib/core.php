<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Omar
 * Date: 12/12/13
 * Time: 12:39 AM
 * To change this template use File | Settings | File Templates.
 */

class Aragon_eRH {

    public function __construct() {

    }

    public function aerh_xml_parser() {
        check_ajax_referer( 'Aragon_eRH_Sync', 'ajax_nonce' );

        if (true) {
            global $wpdb;
            $complete_url = get_option( 'Aragon_eRH_setting_rss-url', null );
            $table_name = $wpdb->prefix . "Aragon_eRH_jobs";

            $xml = @simplexml_load_file($complete_url);

            if (!$xml) {
                wp_send_json_error( array( 'success' => false, 'error' => 'File not found' ) );
            } else {
                $wpdb->query("TRUNCATE TABLE {$table_name}");
            }

            $jobs = $xml->channel->item;

            $total_records = 0;

            for ($i = 0; $i < count($jobs); $i++) {
                $title = (string) $jobs[$i]->title;
                $pubDate = (string) $jobs[$i]->pubDate;
                $description = (string) $jobs[$i]->description;
                $url = (string) $jobs[$i]->link;

                $rows_affected = $wpdb->insert( $table_name, array( 'title' => $title, 'pubDate' => $pubDate, 'description' => $description, 'url' => $url ) );
                $total_records++;
            }

            $jobs = $this->get_jobs_table();

            $results = array('total_records' => $total_records, 'jobs' => $jobs);

            //$data = json_encode($results);

            wp_send_json_success(array('success' => true, 'results' => $results));
        } else {
            wp_send_json_error( array( 'success' => false, 'error' => 'Not properly referenced.' ) );
        }
    }

    public  function aerh_create_bulk_url() {
        check_ajax_referer( 'Aragon_eRH_Sync', 'ajax_nonce' );
        if (true) {
            $bulk_nonce = wp_create_nonce(rand());
            $bulk_url = add_query_arg('aerh', $bulk_nonce, home_url('/'));
            update_option( 'Aragon_eRH_setting_rss-bulk-url', $bulk_nonce );
            wp_send_json_success(array('success' => true, 'url' => $bulk_url));
        } else {
            wp_send_json_error( array( 'success' => false, 'error' => 'Not properly referenced.' ) );
        }
    }

    public function aerh_xml_parser_bulk($provided_nonce) {
        $bulk_nonce = get_option('Aragon_eRH_setting_rss-bulk-url');

        if (!isset($bulk_nonce) || trim($bulk_nonce)==='') {
            die('Please generate a URL from the settings first.');
        }

        if ($provided_nonce == $bulk_nonce) {
            global $wpdb;
            $complete_url = get_option( 'Aragon_eRH_setting_rss-url', null );
            $table_name = $wpdb->prefix . "Aragon_eRH_jobs";

            $xml = @simplexml_load_file($complete_url);

            if (!$xml) {
                echo('URL not valid.');
            } else {
                $wpdb->query("TRUNCATE TABLE {$table_name}");
            }

            $jobs = $xml->channel->item;

            $total_records = 0;

            for ($i = 0; $i < count($jobs); $i++) {
                $title = (string) $jobs[$i]->title;
                $pubDate = (string) $jobs[$i]->pubDate;
                $description = (string) $jobs[$i]->description;
                $url = (string) $jobs[$i]->link;

                $rows_affected = $wpdb->insert( $table_name, array( 'title' => $title, 'pubDate' => $pubDate, 'description' => $description, 'url' => $url ) );
                $total_records++;
            }
            echo ('Total records synced: ' . $total_records . '<br /><br />');
        } else {
            echo ('Please ensure you use the correct URL to sync. You can find this URL under Settings > Aragon_eRH');
        }
    }

    public function get_jobs_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . "Aragon_eRH_jobs";

        $sql = "SELECT * FROM {$table_name}";

        $jobs = $wpdb->get_results($sql);

        return $jobs;
    }

    public function aerh_get_table() {
        $jobs = $this->get_jobs_table();
        $results = array('total_records' => count($jobs), 'jobs' => $jobs);
        wp_send_json_success(array('success' => true, 'results' => $results));
    }

    function create_list($widget_height, $widget_scroll) {
        if ($widget_scroll) {
            $data_scroll = 'data-scroll="true"';
        } else {
            $data_scroll = 'data-scroll="false"';
        }
        $job_list = '<div ' . $data_scroll . ' class="current-positions-list-wrapper" style="overflow: hidden; height:' . $widget_height. 'px"><ul class="aerh-current-positions">';
        $jobs = $this->get_jobs_table();
        foreach ($jobs as $element) {
            $job_list .= '<li class="aerh-current-positions-item"><a href="' . $element->url . '" target="_blank">' . $element->title . '</a></li>';
        }
        $job_list .= '</ul></div>';

        return $job_list;
    }

    public function do_aerh_shortcode($atts) {
        echo $this->create_list('','');
    }
}