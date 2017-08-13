<?php
/**
 * beat_deal header_php.php
 *
 * @package page
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 6912 2007-09-02 02:23:45Z drbyte $
 */

  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
  $breadcrumb->add(NAVBAR_TITLE);
// display order dropdown
  $productsort = array();
  $nsort = array('Bestselling','Item Name','Price(Low to high)','Price(High to low)','New Arrival');
  
  for ($i=1; $i<6; $i++) {
     $productsort[] = array('id' => sprintf('%2d', $i), 'text' =>$nsort[$i-1]  );
  }
  $pagesize = array();
  $pagesize[] = array('id'=>36,'text'=>36);
  $pagesize[] = array('id'=>72,'text'=>72);
  
  if(isset($_GET['productsort']) && (int)$_GET['productsort'] > 0){
    switch ($_GET['productsort']){
      case 2:
        $product_sort = " order by pd.products_name ";
        break;
      case 3:
        $product_sort = " order by p.products_price";
        break;
      case 4:
        $product_sort = " order by p.products_price DESC";
        break;
      case 5:
        $product_sort = " order by p.products_date_added DESC";
        break;
      default:
        $product_sort = " order by p.products_ordered DESC";
    }
  }
  
  //print_r($_SERVER['QUERY_STRING']);
  $display = isset($_GET['display'])? $_GET['display']: '1';
  if(isset($_GET['display'])){
    $display = $_GET['display'];
  }else{
    $display = 1;
  }
  if(isset($_GET['listtypes'])){
    $listTypes = $_GET['listtypes'];
  }else{
    $listTypes = 1;
  }
  if(isset($_GET['display'])){
  //addBy showq@qq.com
   $displayTypes = substr($_GET['display'],0,strlen($_GET['display']) - (is_numeric(substr($_GET['display'],-1,1)) ? 2 : 0));
   switch ($display){
    case '2':
      $displayOrder = ' and p.`product_is_wholesale` = 1';  
      break;
    case '3':
      $displayOrder = ' and p.`product_is_always_free_shipping` = 1';
      break;
    default:
      $displayOrder = '';
   }
  }
  if (isset($_GET['min_price']) && isset($_GET['max_price'])){
    $pricefilter = "and p.`products_price` >= ".$_GET['min_price'] ." and p.`products_price` <= " . $_GET['max_price'];
  }
  $beat_deal_array = array();
// display limits
//  $display_limit = zen_get_beat_deal_timelimit();
  $display_limit = zen_get_new_date_range();

  $best_deal_query_raw = "SELECT p.products_id, p.products_type, pd.products_name, p.products_image, p.products_price,
                                    p.products_tax_class_id, p.products_date_added, p.products_model,
                                    p.products_quantity, p.products_weight, p.product_is_call,
                                    p.product_is_always_free_shipping, p.products_qty_box_status,
                                    p.master_categories_id,p.product_is_wholesale,p.product_wholesale_min,p.products_price_sample,
                                    p.products_price_retail,p.products_quantity,p.products_quantity_order_min,p.products_status
                             FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd,".TABLE_FREESHIPPING . " s 
                             WHERE p.products_status = 1
                             AND p.products_id = pd.products_id
							 AND p.products_id = s.products_id and s.status = 1
                             AND pd.language_id = :languageID " .$displayOrder. $display_limit . $product_sort .$pricefilter ;

   
  $best_deal_query_raw = $db->bindVars($best_deal_query_raw, ':languageID', $_SESSION['languages_id'], 'integer');
  $best_deal_split = new splitPageResults($best_deal_query_raw, (isset($_GET['pagesize']) ? $_GET['pagesize'] : 36));

//check to see if we are in normal mode ... not showcase, not maintenance, etc
  $show_submit = zen_run_normal();
  
  

  $best_deal = $db->Execute($best_deal_split->sql_query);
  if($best_deal->RecordCount()>0){
    $row = 0;
    while (!$best_deal->EOF) {
      if ($best_deal->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) {
        $list_box_contents[$row]['products_image'] = '';
      } else {
        $list_box_contents[$row]['products_image'] = $best_deal->fields['products_image'] ;
      }
      $list_box_contents[$row]['products_name'] = $best_deal->fields['products_name'];
      $list_box_contents[$row]['products_description'] = zen_trunc_string(zen_clean_html(stripslashes(zen_get_products_description($best_deal->fields['products_id'], $_SESSION['languages_id']))), PRODUCT_LIST_DESCRIPTION);
      $list_box_contents[$row]['products_price'] = zen_get_products_base_price($best_deal->fields['products_id']);
      $list_box_contents[$row]['actual_price'] = $currencies->display_price(zen_get_products_actual_price($best_deal->fields['products_id']),zen_get_tax_rate($product_check->fields['products_tax_class_id']));
      $list_box_contents[$row]['products_status']=$best_deal->fields['products_status'];
      if ($best_deal->fields['product_is_always_free_shipping'] == 0) {
        $list_box_contents[$row]['product_is_always_free_shipping'] = '';
      } else {
        $list_box_contents[$row]['product_is_always_free_shipping'] = '<span class="free_shipping"></span>';
      }
      
      $list_box_contents[$row]['products_quantity_order_min'] = $best_deal->fields['products_quantity_order_min'];
      $list_box_contents[$row]['products_id'] = $best_deal->fields['products_id'];
      $list_box_contents[$row]['products_quantity'] = $best_deal->fields['products_quantity'];
      
      $list_box_contents[$row]['products_price_retail'] = $currencies->display_price($best_deal->fields['products_price_retail'],zen_get_tax_rate($product_check->fields['products_tax_class_id']));
      $list_box_contents[$row]['products_price_sample'] = $currencies->display_price($best_deal->fields['products_price_sample'],zen_get_tax_rate($product_check->fields['products_tax_class_id']));
      $list_box_contents[$row]['product_is_wholesale'] = $best_deal->fields['product_is_wholesale'];
      $list_box_contents[$row]['product_wholesale_min'] = $best_deal->fields['product_wholesale_min'];
      $best_deal->MoveNext();
      $row++;
    }
  }
  
?>