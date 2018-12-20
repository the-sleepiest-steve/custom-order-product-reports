<?php
global $wpdb;

$time = current_time('timestamp');

$report_name = "Daily Waitlist Report " . date("m-d-Y", $time);
$report_file_name = "daily_waitlist_report_".$time[mday]."_".$time[mon]."_".$time[year].".csv";
$report_description = $report_name;


//get array of customer id's on waitlist per product id
$waitlist_arr_query = "
SELECT
post_id,meta_value
FROM
gecp_postmeta
WHERE
gecp_postmeta.meta_key = 'woocommerce_waitlist' AND gecp_postmeta.meta_value != 'a:0:{}' 
            ";

$waitlists = $wpdb->get_results( $waitlist_arr_query );

$csv_data = array();
$csv_data[] = array(
    'PRODUCT NAME',
    'PRODUCT SKU',
    'CUSTOMER DISPLAY NAME',
    'CUSTOMER EMAIL'
);


foreach ($waitlists as $waitlist){

    //grab each id and create functional wc product object
    //use functions from wc api to get information
    $product_id = $waitlist->post_id;
    $product = wc_get_product($product_id);
    $product_type = $product->product_type;
    if($product_type === 'simple' || 'variation'){
        $sku = $product->get_sku();
        $title = $product->get_title();
        $customer_ids = $waitlist->meta_value;

        //to unserialize... use base64_decode to avoid corruption if values contain " ' : or ;
        $customer_ids = unserialize($customer_ids);

        foreach($customer_ids as $customer_id){

            $user = get_user_by( 'id', $customer_id );
            $user_name = $user->display_name;
            $user_email = $user->user_email;

            $csv_data[] = array(
                $title,
                $sku,
                $user_name,
                $user_email
            );
        }
    }

}
$send_to = array(
    'steven.christensen@gmail.com'
);

if($csv_data){
    wp_mail($send_to, $report_name, $report_description, '', array( brics_report_array_to_csv( $csv_data, $report_file_name) ) );
} else {
    wp_mail("steven.christensen@gmail.com", $report_name, ' No Customers on Waitlists ' . "\n\n" . date("m-d-Y H:i:s"));
}




