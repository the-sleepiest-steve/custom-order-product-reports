<?php
global $wpdb;

$time = current_time('timestamp');

$report_name = "Daily Inventory Report " . date("m-d-Y", $time);
$report_file_name = "daily_inventory_report_".$time[mday]."_".$time[mon]."_".$time[year].".csv";
$report_description = $report_name;

//get variants and parent products id

$query = "
    SELECT ID FROM gecp_posts
    WHERE post_type IN ('product','product_variation')
    AND post_status = 'publish'
            ";
//get array of product ID's and Product Variation ID's
$product_ids = $wpdb->get_col( $query );
$csv_data = array();
$csv_data[] = array(
    'SKU',
    'TITLE',
    'STOCK',
    'STOCK STATUS'
);
$products = new WC_Product_Factory();
foreach ($product_ids as $product_id){
    //grab each id and create functional wc product object
    //use functions from wc api to get information
    $product = $products->get_product($product_id);

    $sku = $product->get_sku();
    $title = $product->get_title();
    $stock = $product->get_stock_quantity();
    if($stock > 0){
        $stock_status = 'In Stock';
    } else {
        $stock_status = 'Out Of Stock';
    }




    $csv_data[] = array(
        $sku,
        $title,
        $stock,
        $stock_status
    );
}

$send_to = array(
    'stevenlevi.christensen@gmail.com',
);


if($csv_data){
    wp_mail($send_to, $report_name, $report_description, '', array( brics_report_array_to_csv( $csv_data, $report_file_name) ) );
} else {
    wp_mail("steven.christensen@vitalbgs.com", $report_name, ' No inventory. Error? Query: ' . $query. "\n\n" . date("m-d-Y H:i:s"));
}