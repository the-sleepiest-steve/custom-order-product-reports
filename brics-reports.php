<?php
/*
Plugin Name: Reports for BRIC'S 
Plugin URI:
Description: Different BRIC'S reports. Created as CRON job.
Version: 1.0
Author: Steven Christensen
Author URI:
*/

function brics_report_array_to_csv( $data, $filename ) {

    $plugin_dir = untrailingslashit( dirname( __FILE__ ) );

    $generated_reports_path = $plugin_dir . "/generated_reports";
    if ( !file_exists($generated_reports_path) ) {
        mkdir( $generated_reports_path,"755" );
    }

    $report_file_path = $generated_reports_path . "/" . $filename;
    // Open temp file pointer
    if (!$fp = fopen( $report_file_path, 'w+')) {
        return FALSE;
    }

    // Loop data and write to file pointer
    foreach ($data as $line) {
        fputcsv($fp, $line);
    }

    fclose( $fp );

    return $report_file_path;
}

function brics_report_init() {
	
	$plugin_dir = untrailingslashit( dirname( __FILE__ ) );

	$allowed_reports = array(
		'daily_orders',
		'daily_excel_sales',
        'daily_inventory'
	);

	if ( isset( $_GET['cron_brics_report'] ) && in_array( $_GET['cron_brics_report'], $allowed_reports ) ) {
		
		include $plugin_dir . "/reports/" . $_GET['cron_brics_report'] . ".php";

		die();
	}
	
}
add_action( 'wp_loaded', 'brics_report_init' );