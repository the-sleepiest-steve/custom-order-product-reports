<?php
global $wpdb;

$send_to = array(

				);



$time = current_time('timestamp');

$report_name = "Sales Report " . date("m-d-Y", $time); 
$report_file_name = "sales_report_".date("m_d_Y", $time).".csv";
$report_description = $report_name;


$order_statuses = array(
	'wc-processing',
	'wc-completed'
);

$today = date('Y-m-d', $time);

$query = "
			SELECT ID FROM $wpdb->posts 
			WHERE post_type = 'shop_order'
			AND post_status IN ('" . implode("','", $order_statuses) . "')
			AND post_date BETWEEN '{$today}  00:00:00' AND '{$today} 23:59:59'
         ";

$order_ids = $wpdb->get_col( $query );

if ( $order_ids ) {
	
	$csv_data = array();

	$csv_data[] = array(
		'Order #',
		//'Items',
		//'SKUs',
		'Date',
		'Subtotal'
	);

	$total = 0;
	foreach ($order_ids as $order_id) {
		$order = new WC_Order( $order_id );		

		$subtotal = $order->get_total();
		$total += $subtotal;

		$order_items = $order->get_items();
		$skus = "";
		if ( $order_items ) {
			foreach ($order_items as $item) {
				$product_variation_id = $item['variation_id'];

				// Check if product has variation.
				if ($product_variation_id) { 
					$product = new WC_Product($item['variation_id']);
				} else {
					$product = new WC_Product($item['product_id']);
				}

				// Get SKU
				$sku = $product->get_sku();
				$skus .= $sku."\n";
			}
		}

		$csv_data[] = array(
			$order->id,
			//sizeof($order->get_items()),
			//$skus,
			$order->order_date,
			$subtotal
		);
		
	}

	$csv_data[] = array(
		'',		
		'Total',
		$total
	);	

	wp_mail($send_to, $report_name, $report_description, '', array( brics_report_array_to_csv( $csv_data, $report_file_name) ) );
} else {
	wp_mail("stevenlevi.christensen@gmail.com", $report_name, ' No sales. Query: ' . $query. "\n\n" . date("m-d-Y H:i:s"));
}