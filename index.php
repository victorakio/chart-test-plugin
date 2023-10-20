<?php
/*
Plugin Name: Test Plugin
Description: A test plugin to test wpscripts workflow
Version: 0.1.0
Author: Victor Akio
Author URI: https://github.com/victorakio
Plugin URI: https://github.com/victorakio
License: GPLv2 or later
Text Domain: testplugin
Requires PHP: 7.4
*/

if ( ! defined("ABSPATH") ) exit; // Prevents direct access

class MyCustomGraphWidget {
  public function __construct() {
    add_action( 'wp_dashboard_setup', array( $this, 'mcgw_add_dashboard_widget' ) );
    add_action( 'activate_test/index.php', array( $this, 'mcgw_create_table' ) );
    add_action( 'rest_api_init', array( $this, 'mcgw_create_custom_route' ) );
  }

  /**
   * Add a new dashboard widget.
   */
  public function mcgw_add_dashboard_widget() {
    wp_register_script( 'mcgw.js', plugin_dir_url( __FILE__ ) . 'build/index.js', array( 'wp-element' ), '1.0', true );

    wp_register_style( 'mcgw.css', plugin_dir_url( __FILE__ ) . 'build/index.css' );

    wp_add_dashboard_widget( 'dashboard_widget', 'Example Dashboard Widget', array( $this, 'mcgw_render_widget' ) );
  }

  /**
   * Output the contents of the dashboard widget
   */
  public function mcgw_render_widget() { 
    if ( is_admin() ) {
      wp_enqueue_script( 'mcgw.js' );
      wp_enqueue_style( 'mcgw.css' );
    } 
  ?>
    <div id="app"></div>
  <?php }

  public function mcgw_create_table() {
    global $wpdb;

    $collate = $wpdb->get_charset_collate();
		$table_name   = $wpdb->prefix . 'mcgw_statistics';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta("CREATE TABLE {$table_name} (
      id bigint(20) unsigned NOT NULL auto_increment,
      page_title varchar(255) NOT NULL,
      total_visits int(20) NOT NULL default 0,
      visits_last_week int(20) NOT NULL default 0,
      visits_last_half_month int(20) NOT NULL default 0,
      visits_last_month int(20) NOT NULL default 0,
      PRIMARY KEY  (id)
    ) $collate;");
  }

  public function mcgw_create_custom_route() {
    register_rest_route( 'mcgw/v1', 'statistics', array(
      'methods' => WP_REST_SERVER::READABLE,
      'callback' => array( $this, 'mcgw_statistics_route_results' ),
      '_wpnonce' => [
        'required' => true
      ]
    ) );
  }

  public function mcgw_statistics_route_results(WP_REST_Request $request) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mcgw_statistics';

    $period = $request->get_param( 'period' );

    switch ($period) {
      case 'visits_last_week':
        $statistics_query = $wpdb->prepare( "SELECT id, page_title, total_visits, visits_last_week FROM {$table_name}" );
        break;
      case 'visits_last_half_month':
        $statistics_query = $wpdb->prepare( "SELECT id, page_title, total_visits, visits_last_half_month FROM {$table_name}" );
        break;
      case 'visits_last_month':
        $statistics_query = $wpdb->prepare( "SELECT id, page_title, total_visits, visits_last_month FROM {$table_name}" );
        break;
      default:
        $statistics_query = $wpdb->prepare( "SELECT id, page_title, total_visits, visits_last_week FROM {$table_name}" );
        break;
    }

    $statistics = $wpdb->get_results( $statistics_query );

    $convertedStatistics = [];

    foreach ( $statistics as $statistic) {
      array_push( $convertedStatistics, (object)[
        "id" => intval( $statistic->id ),
        "page_title" => $statistic->page_title,
        "total_visits" => intval( $statistic->total_visits ),
        $period => intval( $statistic->{$period} )
      ] );
    }

    return $convertedStatistics;
  }
}

$myCustomGraphWidget = new MyCustomGraphWidget();