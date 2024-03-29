<?php
/**
 * default_filter.php  for index filters
 *
 * index filter for the default product type
 * show the products of a specified manufacturer
 *
 * @package productTypes
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @todo Need to add/fine-tune ability to override or insert entry-points on a per-product-type basis
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: default_filter.php 6912 2007-09-02 02:23:45Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$o_d =array();
$in_product="";


if(isset($_GET['other']) && strlen($_GET['other'])>0){

$o_list = explode("/",trim($_GET['other']));
$o_id=array();$o_str='';$freeshipping = false;$special = false;

foreach($o_list as $o_lists){
	$o_right = explode("_",$o_lists);
	
	$o_right = $o_right[(count($o_right)-1)];
	if($o_right!='fs' && $o_right!='sps'){
		$o_i_all = explode("v",$o_right);
		$o_id[] = array('v_id'=>$o_i_all[1],
						'i_id'=>substr($o_i_all[0],1));
		$o_d[] =substr($o_i_all[0],1);
		$o_di[] =$o_i_all[1];
		$o_str .=$o_lists.'/';
	}
	if($o_right=='fs'){
		$freeshipping = true;
	}
	if($o_right=='sps'){
		$special = true;
	}	
}

   
	$str=join(",",$o_di);
	$sqls="select at.products_id from products_attributes at where at.options_values_id in(".$str.")  group by at.products_id "; 
	$options_p = $db->Execute($sqls); 
	$in_product="";
	while (!$options_p->EOF) {
	$in_product.=$options_p->fields['products_id'].",";
	$options_p->MoveNext();
	}
	$in_product = trim($in_product,",");
}


  if(strlen($in_product)>0){
	$in_pt = "and p.products_id in (".$in_product.")";
	}else{
	$in_pt = "";
	}

   
  $product_sort = " order by p.products_recommend desc,p.products_id desc";
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
  if(isset($_GET['display'])){
  //addBy showq@qq.com
   $displayTypes = substr($_GET['display'],0,strlen($_GET['display']) - (is_numeric(substr($_GET['display'],-1,1)) ? 2 : 0));
   switch ($displayTypes){
    case 'Wholesale-Only':
      $displayOrder = ' and p.`product_is_wholesale` = 1';  
      break;
    case 'Free-Shipping':
      $displayOrder = ' and p.`product_is_always_free_shipping` = 1';
      break;
    default:
      $displayOrder = '';
   }
  }
  
  if (isset($_GET['min_price']) && isset($_GET['max_price']) && $_GET['min_price'] != '' && $_GET['max_price'] != ''){
  	
  	$priceOrder = ' and '.intval($_GET['min_price']).' <  p.`products_price` and p.`products_price` < '.intval($_GET['max_price']);
  	
  }
  
  if (!isset($select_column_list)) $select_column_list = "";
   // show the products of a specified manufacturer
  if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '' ) {
    if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id'])) {
// We are asked to show only a specific category
      $listing_sql = "select " . $select_column_list . " distinct p.products_id, p.products_model,p.products_type,p.products_status,p.products_price,p.products_quantity,p.product_is_wholesale, p.products_quantity_order_min, p.master_categories_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, pd.products_description, if(s.status = 1, s.specials_new_products_price, NULL) AS specials_new_products_price, IF(s.status = 1, s.specials_new_products_price, p.products_price) as final_price, p.products_sort_order, p.product_is_call, p.product_is_always_free_shipping, p.products_qty_box_status,p.products_recommend from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id ," . TABLE_PRODUCTS . " p left join " . TABLE_FEATURED . " pf on p.products_id = pf.products_id , " .
       TABLE_PRODUCTS_DESCRIPTION . " pd, " .
       TABLE_MANUFACTURERS . " m, " .
       TABLE_PRODUCTS_TO_CATEGORIES . " p2c
       where p.products_status = 1
         and p.manufacturers_id = m.manufacturers_id
         and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'
         and p.products_id = p2c.products_id
		 ".$in_pt ."
         and pd.products_id = p2c.products_id
         and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
         and p2c.categories_id = '" . (int)$_GET['filter_id'] . "'";
		 
    } else {
// We show them all
      $listing_sql = "select " . $select_column_list . " distinct p.products_id, p.products_model,p.products_type,p.products_status,p.products_price,p.products_quantity,p.product_is_wholesale, p.products_quantity_order_min, p.master_categories_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, pd.products_description, IF(s.status = 1, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status = 1, s.specials_new_products_price, p.products_price) as final_price, p.products_sort_order, p.product_is_call, p.product_is_always_free_shipping, p.products_qty_box_status,p.products_recommend from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id ," . TABLE_PRODUCTS . " p left join " . TABLE_FEATURED . " pf on p.products_id = pf.products_id ," .
      TABLE_PRODUCTS_DESCRIPTION . " pd, " .
      TABLE_MANUFACTURERS . " m
      where p.products_status = 1
        and pd.products_id = p.products_id
		".$in_pt ."
        and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
        and p.manufacturers_id = m.manufacturers_id
        and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";
    }
  } else {
// show the products in a given category

//addBy showq@qq.com
		if(zen_has_category_subcategories($current_category_id)){
	  	$product_in_categories_sql = '';
	  	$product_in_categoriesArray = array();
	  	zen_get_subcategories(&$product_in_categoriesArray,$current_category_id);
	  	$product_in_categories_sql = implode(' or p2c.categories_id =',$product_in_categoriesArray);
	  	$product_in_categories_sql = '( p2c.categories_id ='.$product_in_categories_sql.')';			
		}else{
			$product_in_categories_sql = 'p2c.categories_id = ' . (int)$current_category_id;
		}


    if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id'])) {
// We are asked to show only specific category
      $listing_sql = "select distinct p.products_id," . $select_column_list . "  p.products_model,p.products_type,p.products_status,p.products_price,p.products_price_retail,p.products_price_sample,p.product_is_wholesale,p.product_wholesale_min,p.products_quantity, p.products_quantity_order_min, p.master_categories_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, pd.products_description, IF(s.status = 1, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status = 1, s.specials_new_products_price, p.products_price) as final_price, p.products_sort_order, p.product_is_call, p.product_is_always_free_shipping, p.products_qty_box_status,p.products_recommend
      from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id," . TABLE_PRODUCTS . " p left join " . TABLE_FEATURED . " pf on p.products_id = pf.products_id, " .
      TABLE_PRODUCTS_DESCRIPTION . " pd, " .
      TABLE_MANUFACTURERS . " m, " .
      TABLE_PRODUCTS_TO_CATEGORIES . " p2c
      where p.products_status = 1
        and p.manufacturers_id = m.manufacturers_id
        and m.manufacturers_id = '" . (int)$_GET['filter_id'] . "'
        and p.products_id = p2c.products_id
        and pd.products_id = p2c.products_id
		".$in_pt ."
        and pd.language_id = '" . (int)$_SESSION['languages_id']."'". $displayOrder. $priceOrder."
        and " . $product_in_categories_sql;
    } else {
// We show them all
      $listing_sql = "select distinct p.products_id, " . $select_column_list . "  p.products_type,p.products_model,p.products_status,p.products_price,p.products_price_retail,p.products_price_sample,p.product_is_wholesale,p.product_wholesale_min,p.products_quantity, p.products_quantity_order_min, p.master_categories_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, pd.products_description, IF(s.status = 1, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status =1, s.specials_new_products_price, p.products_price) as final_price, p.products_sort_order, p.product_is_call, p.product_is_always_free_shipping, p.products_qty_box_status,p.products_recommend
       from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " .
       TABLE_PRODUCTS . " p left join " . TABLE_FEATURED . " pf on p.products_id = pf.products_id, " .
       TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p2c.products_id = s.products_id
       where p.products_status = 1
         and p.products_id = p2c.products_id
		 ".$in_pt."
         and pd.products_id = p2c.products_id
         and pd.language_id = '" . (int)$_SESSION['languages_id']."'". $displayOrder. $priceOrder."
         and " . $product_in_categories_sql;
    }
  }


// set the default sort order setting from the Admin when not defined by customer
  if (!isset($_GET['sort']) and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
    $_GET['sort'] = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
  }

  if (isset($column_list)) {
      $listing_sql .= $product_sort;
  }
  

// optional Product List Filter
  if (PRODUCT_LIST_FILTER > 0) {
    if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '') {
      $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name
      from " . TABLE_PRODUCTS . " p, " .
      TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .
      TABLE_CATEGORIES . " c, " .
      TABLE_CATEGORIES_DESCRIPTION . " cd
      where p.products_status = 1
        and p.products_id = p2c.products_id
        and p2c.categories_id = c.categories_id
        and p2c.categories_id = cd.categories_id
        and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
        and p.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'
      order by cd.categories_name";
    } else {
      $filterlist_sql= "select distinct m.manufacturers_id as id, m.manufacturers_name as name
      from " . TABLE_PRODUCTS . " p, " .
      TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .
      TABLE_MANUFACTURERS . " m
      where p.products_status = 1
        and p.manufacturers_id = m.manufacturers_id
        and p.products_id = p2c.products_id
        and p2c.categories_id = '" . (int)$current_category_id . "'
      order by m.manufacturers_name";
    }
    $do_filter_list = false;
    $filterlist = $db->Execute($filterlist_sql);
    if ($filterlist->RecordCount() > 1) {
        $do_filter_list = true;
      if (isset($_GET['manufacturers_id'])) {
        $getoption_set =  true;
        $get_option_variable = 'manufacturers_id';
        $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
      } else {
        $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
      }
      while (!$filterlist->EOF) {
        $options[] = array('id' => $filterlist->fields['id'], 'text' => $filterlist->fields['name']);
        $filterlist->MoveNext();
      }
    }
  }

// Get the right image for the top-right
  $image = DIR_WS_TEMPLATE_IMAGES . 'table_background_list.gif';
  if (isset($_GET['manufacturers_id'])) {
    $sql = "select manufacturers_image
              from   " . TABLE_MANUFACTURERS . "
              where      manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";

    $image_name = $db->Execute($sql);
    $image = $image_name->fields['manufacturers_image'];

  } elseif ($current_category_id) {

    $sql = "select categories_image from " . TABLE_CATEGORIES . "
            where  categories_id = '" . (int)$current_category_id . "'";

    $image_name = $db->Execute($sql);
    $image = $image_name->fields['categories_image'];
  }
?>