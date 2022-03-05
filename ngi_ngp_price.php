<?php
/**
 * Plugin Name: NGI Natural Gas Price
 * Description: Add Shortcode [get_ngi_ngp_price company="Company Name"] to your post to have it update the price of selected index
 * Author: Bill Dafeldecker
 */

function ngi_ngp_price_shortcodes_init() {
    add_shortcode('ngi_ngp_price', 'get_ngi_ngp_price');
}
add_action('init', 'ngi_ngp_price_shortcodes_init');

function get_ngi_ngp_price($atts = [], $content = null) {
    $atts = array_change_key_case((array) $atts, CASE_LOWER);
    $ngi_ngp_price_atts = shortcode_atts(
        array(
            'company' => 'Company',
        ), $atts
    );
    if(isset($ngi_ngp_price_atts['company'])){
        $request = wp_remote_get('https://api.ngidata.com/dailyPriceTicker.json');
        if(is_wp_error($request)) {
            // Error getting json
            return null; 
        }
        else {
            $body = wp_remote_retrieve_body($request);
            $data = json_decode($body);
            if(!empty($data)) {
                foreach($data->data as $company => $company_info){
                    if($company === $ngi_ngp_price_atts['company']){
                        $price_color = 'black';
                        $price_sign = '';
                        if(floatval($company_info->Change) > 0){
                            $price_color = 'green';
                            $price_sign = '+';
                        }
                        elseif(floatval($company_info->Change) < 0) {
                            $price_color = 'red';
                        }
                        $o = '(<span style="color:'.esc_html__($price_color).';">'.
                        esc_html__($price_sign.$company_info->Change).'</span>)';
                        return $o;
                    }
                }
            }
        }
    }
    return $content;
}
