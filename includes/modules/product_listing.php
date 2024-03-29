<?php
/**
 * product_listing module
 *
 * @package modules
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: product_listing.php 6787 2007-08-24 14:06:33Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$productsort = array();
$nsort = array('Bestselling','Item Name','Price(Low to high)','Price(High to low)','New Arrival');

for ($i=1; $i<6; $i++) {
   $productsort[] = array('id' => sprintf('%2d', $i), 'text' =>$nsort[$i-1]  );
}
$pagesize = array();
$pagesize[] = array('id'=>36,'text'=>36);
$pagesize[] = array('id'=>72,'text'=>72);

//print_r($_SERVER['QUERY_STRING']);
$display = isset($_GET['display'])? $_GET['display']: 'All-1';
if(is_int(strpos($display,'productsort')) || is_int(strpos($display,'pagesize'))){
	$display = substr($display,0,(is_int(strpos($display,'productsort'))? strpos($display,'productsort'): strlen($display)));
	$display = substr($display,0,(is_int(strpos($display,'pagesize')) ? strpos($display,'pagesize'): strlen($display)));
}


$displayTypes = substr($display,0,strlen($display) - (is_numeric(substr($display,-1,1)) ? 2 : 0));
if ($current_page != 'advanced_search_result'){
  $listTypes = isset($listTypes) ? $listTypes : zen_get_categories_listTypes($current_category_id);
}


$show_submit = zen_run_normal();
$listing_split = new splitPageResults($listing_sql, (isset($_GET['pagesize']) ? $_GET['pagesize'] : 36), 'p.products_id', 'page');
$zco_notifier->notify('NOTIFY_MODULE_PRODUCT_LISTING_RESULTCOUNT', $listing_split->number_of_rows);
$how_many = 0;


$zc_col_count_description = 0;
$lc_align = '';
$list_box_contents = array();

//print_r(explode(' ',zen_products_id_in_category($current_category_id)));
if (zen_count_products_in_category($current_category_id) > 0) {
  $listing = $db->Execute($listing_split->sql_query);
  
	$row = 0;
  while (!$listing->EOF) {
		if ($listing->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) {
			$list_box_contents[$row]['products_image'] = '';
		} else {
			$list_box_contents[$row]['products_image'] = $listing->fields['products_image'] ;
		}
		$list_box_contents[$row]['products_name'] = $listing->fields['products_name'];
		$list_box_contents[$row]['master_categories_id'] = $listing->fields['master_categories_id'];
		$list_box_contents[$row]['products_model'] = $listing->fields['products_model'];
		$list_box_contents[$row]['products_small_description'] = $listing->fields['products_small_description'];
		$list_box_contents[$row]['products_description'] = zen_trunc_string(zen_clean_html(stripslashes(zen_get_products_description($listing->fields['products_id'], $_SESSION['languages_id']))),160);
		$list_box_contents[$row]['products_price'] = zen_get_products_base_price($listing->fields['products_id']);
		$list_box_contents[$row]['products_maxprice']=zen_get_products_discount_price_qty($listing->fields['products_id'], 100);
		$list_box_contents[$row]['actual_price'] = $currencies->display_price(zen_get_products_actual_price($listing->fields['products_id']),zen_get_tax_rate($product_check->fields['products_tax_class_id']));
		$list_box_contents[$row]['products_status']=$listing->fields['products_status'];
		$list_box_contents[$row]['products_recommend']=$listing->fields['products_recommend'];
		if ($listing->fields['product_is_always_free_shipping'] == 0) {
			$list_box_contents[$row]['product_is_always_free_shipping'] = '';
		} else {
			$list_box_contents[$row]['product_is_always_free_shipping'] = '<span class="free_shipping"></span>';
		}
		
		$list_box_contents[$row]['products_quantity_order_min'] = $listing->fields['products_quantity_order_min'];
		$list_box_contents[$row]['products_id'] = $listing->fields['products_id'];
		$list_box_contents[$row]['products_quantity'] = $listing->fields['products_quantity'];
		
		$list_box_contents[$row]['products_price_retail'] = $currencies->display_price($listing->fields['products_price_retail'],zen_get_tax_rate($product_check->fields['products_tax_class_id']));
    $list_box_contents[$row]['products_price_sample'] = $currencies->display_price($listing->fields['products_price_sample'],zen_get_tax_rate($product_check->fields['products_tax_class_id']));
		$list_box_contents[$row]['product_is_wholesale'] = $listing->fields['product_is_wholesale'];
		$list_box_contents[$row]['product_wholesale_min'] = $listing->fields['product_wholesale_min'];
		$list_box_contents[$row]['featured_id'] = $listing->fields['featured_id'];
    	$listing->MoveNext();
		$row++;
  }
  $error_categories = false;
}else{
  $list_box_contents = array();
  $error_categories = true;
}



if (($how_many > 0 and $show_submit == true and $listing_split->number_of_rows > 0) and (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 1 or  PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 3) ) {
  $show_top_submit_button = true;
} else {
  $show_top_submit_button = false;
}
if (($how_many > 0 and $show_submit == true and $listing_split->number_of_rows > 0) and (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART >= 2) ) {
  $show_bottom_submit_button = true;
} else {
  $show_bottom_submit_button = false;
}



  if ($how_many > 0 && PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 and $show_submit == true and $listing_split->number_of_rows > 0) {
  // bof: multiple products
    echo zen_draw_form('multiple_products_cart_quantity', zen_href_link(FILENAME_DEFAULT, zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product'), 'post', 'enctype="multipart/form-data"');
  }

?>
