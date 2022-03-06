<?php
/**
 * Plugin Name: NGI Natural Gas Price
 * Description: Add Shortcode [ngp] to your post to have it update the price of selected pricing point
 * Author: Bill Dafeldecker
 */

function ngi_ngp_price_shortcodes_init() {
  add_shortcode('ngp', 'ngi_ngp_price');  
  
}
add_action('init', 'ngi_ngp_price_shortcodes_init');

/**
 * [ngp] returns the company's current price change.
 * @return string HTML span with the price change and pricing point
*/
function ngi_ngp_price($atts = [], $content = NULL) {
  if($content != NULL) {
    $request = wp_remote_get('https://api.ngidata.com/dailyPriceTicker.json');
    if(!is_wp_error($request)) {
      $body = wp_remote_retrieve_body($request);
      $data = json_decode($body);
      if(!empty($data)) {
        foreach($data->data as $company_info) {
          if(strtolower($company_info->{'Pricing Point'}) === trim(strtolower(strip_tags($content)))) {
            $contentHtml = '<span>'.$content.'</span> ';
            $price_color = 'black';
            $price_sign = '';
            if(floatval($company_info->Change) > 0) {
              $price_color = 'green';
              $price_sign = '+';
            }
            elseif(floatval($company_info->Change) < 0) {
              $price_color = 'red';
            }
            $priceHTML = '(<span style="color:'.esc_html__($price_color).';">'.
            esc_html__($price_sign.$company_info->Change).'</span>)';
            return $contentHtml.$priceHTML;
          }
        }
      }
    }
  }
  return $content;
}