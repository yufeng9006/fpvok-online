<?php
/**
 * featured sidebox - displays a random Featured Product
 *
 * @package templateSystem
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: featured.php 6475 2007-06-08 21:10:33Z ajeh $
 */

// test if box should display
if($this_is_home_page){
  $show_featured= true;
}else{
  $show_featured= false;
}
  if ($show_featured == true) {
  //FEATURED: Added order by f.sort_order to query
  $random_featured_products_query = "select p.products_id, p.products_image, pd.products_name,
                                       p.master_categories_id
                           from (" . TABLE_PRODUCTS . " p
                           left join " . TABLE_FEATURED . " f on p.products_id = f.products_id
                           left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id )
                           where p.products_id = f.products_id
                           and p.products_id = pd.products_id
                           and p.products_status = 1
                           and f.status = 1
                           and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by f.sort_order";

    // randomly select ONE featured product from the list retrieved:
    //$random_featured_product = zen_random_select($random_featured_products_query);
    //FEATURED: Changed ExecuteRandomMulti to Execute
    $random_featured_product = $db->Execute($random_featured_products_query, 15);

    if ($random_featured_product->RecordCount() > 0)  {
      require($template->get_template_dir('tpl_featured.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_featured.php');
      $title =  'Top sales';
      //$title_link = FILENAME_FEATURED_PRODUCTS;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/tpl_box_featured.php');
    }
  }
?>