<?php
/**
 * functions_categories.php
 *
 * @package functions
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: functions_categories.php 6424 2007-05-31 05:59:21Z ajeh $
 */

////
// Generate a path to categories
  function zen_get_path($current_category_id = '') {
    global $cPath_array, $db;

    if (zen_not_null($current_category_id)) {
      $cp_size = sizeof($cPath_array);
      if ($cp_size == 0) {
        $cPath_new = $current_category_id;
      } else {
        $cPath_new = '';
        $last_category_query = "select parent_id
                                from " . TABLE_CATEGORIES . "
                                where categories_id = '" . (int)$cPath_array[($cp_size-1)] . "'";

        $last_category = $db->Execute($last_category_query);

        $current_category_query = "select parent_id
                                   from " . TABLE_CATEGORIES . "
                                   where categories_id = '" . (int)$current_category_id . "'";

        $current_category = $db->Execute($current_category_query);

        if ($last_category->fields['parent_id'] == $current_category->fields['parent_id']) {
          for ($i=0; $i<($cp_size-1); $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        } else {
          for ($i=0; $i<$cp_size; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        }
        $cPath_new .= '_' . $current_category_id;

        if (substr($cPath_new, 0, 1) == '_') {
          $cPath_new = substr($cPath_new, 1);
        }
      }
    } else {
      $cPath_new = implode('_', $cPath_array);
    }

    return 'cPath=' . $cPath_new;
  }


    function zen_get_catalog_parent($current_category_id) {
    global $db;
    $subcategories_query = "select parent_id,categories_id
                            from " . TABLE_CATEGORIES . "
                            where categories_id = '" . (int)$current_category_id . "'";

    $subcategories = $db->Execute($subcategories_query);
         if($subcategories->fields['parent_id']==0){
		 return $subcategories->fields['categories_id'];
		 }else{
		 return zen_get_catalog_parent($subcategories->fields['parent_id']);
		 }
     }
////
// Return the number of products in a category
// TABLES: products, products_to_categories, categories
  function zen_count_products_in_category($category_id, $include_inactive = false) {
    global $db;
    $products_count = 0;
    if ($include_inactive == true) {
      $products_query = "select count(*) as total
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id
                         and p2c.categories_id = '" . (int)$category_id . "'";

    } else {
      $products_query = "select count(*) as total
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id
                         and p.products_status = '1'
                         and p2c.categories_id = '" . (int)$category_id . "'";

    }
    $products = $db->Execute($products_query);
    $products_count += $products->fields['total'];

    $child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = '" . (int)$category_id . "'";

    $child_categories = $db->Execute($child_categories_query);

    if ($child_categories->RecordCount() > 0) {
      while (!$child_categories->EOF) {
        $products_count += zen_count_products_in_category($child_categories->fields['categories_id'], $include_inactive);
        $child_categories->MoveNext();
      }
    }

    return $products_count;
  }
  
  function zen_get_product_category_id ($products_id) {
	  global $db;
      $products_query = "select p2c.categories_id
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id
                         and p.products_id = '" . (int)$products_id . "'";
						 
	  $category_id = $db->Execute($products_query);		
	  
	  return $category_id->fields['categories_id'];
						 
	  
  }

////
// Return the number of products in a category
// TABLES: products, products_to_categories, categories
  function zen_products_id_in_category($category_id, $include_inactive = false) {
    global $db;
    if ($include_inactive == true) {
      $products_query = "select p.products_id
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id
                         and p2c.categories_id = '" . (int)$category_id . "'";

    } else {
      $products_query = "select p.products_id
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id
                         and p.products_status = '1'
                         and p2c.categories_id = '" . (int)$category_id . "'";

    }
    	$products = $db->Execute($products_query);
    while (!$products->EOF){
    	$products_id .= ' '.$products->fields['products_id'];
     	$products->MoveNext();
    }
    $child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = '" . (int)$category_id . "'";

    $child_categories = $db->Execute($child_categories_query);

    if ($child_categories->RecordCount() > 0) {
      while (!$child_categories->EOF) {
        $products_id .= zen_products_id_in_category($child_categories->fields['categories_id'], $include_inactive);
        $child_categories->MoveNext();
      }
    }
    return trim($products_id, " ");
  }
////
// Return true if the category has subcategories
// TABLES: categories
  function zen_has_category_subcategories($category_id) {
    global $db;
    $child_category_query = "select count(*) as count
                             from " . TABLE_CATEGORIES . "
                             where parent_id = '" . (int)$category_id . "'";

    $child_category = $db->Execute($child_category_query);

    if ($child_category->fields['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }

////
  function zen_get_categories($categories_array = '', $parent_id = '0', $indent = '', $status_setting = '') {
    global $db;

    if (!is_array($categories_array)) $categories_array = array();

    // show based on status
    if ($status_setting != '') {
      $zc_status = " c.categories_status='" . (int)$status_setting . "' and ";
    } else {
      $zc_status = '';
    }

    $categories_query = "select c.categories_id, cd.categories_name, c.categories_status
                         from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                         where " . $zc_status . "
                         parent_id = '" . (int)$parent_id . "'
                         and c.categories_id = cd.categories_id
                         and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                         order by sort_order, cd.categories_name";

    $categories = $db->Execute($categories_query);

    while (!$categories->EOF) {
      $categories_array[] = array('id' => $categories->fields['categories_id'],
                                  'text' => $indent . $categories->fields['categories_name']);

      if ($categories->fields['categories_id'] != $parent_id) {
        $categories_array = zen_get_categories($categories_array, $categories->fields['categories_id'], $indent . '&nbsp;&nbsp;', '1');
      }
      $categories->MoveNext();
    }

    return $categories_array;
  }

////
// Return all subcategory IDs
// TABLES: categories
  function zen_get_subcategories(&$subcategories_array, $parent_id = 0) {
    global $db;
    $subcategories_query = "select categories_id
                            from " . TABLE_CATEGORIES . "
                            where parent_id = '" . (int)$parent_id . "'";

    $subcategories = $db->Execute($subcategories_query);

    while (!$subcategories->EOF) {
      $subcategories_array[sizeof($subcategories_array)] = $subcategories->fields['categories_id'];
      if ($subcategories->fields['categories_id'] != $parent_id and zen_has_category_subcategories($subcategories->fields['categories_id'])) {
        zen_get_subcategories($subcategories_array, $subcategories->fields['categories_id']);
      }
      $subcategories->MoveNext();
    }
  }
  
///
////
// Return all subcategory IDs,NAMEs
// TABLES: categories,categories_description
//TODO To be resolved: Show the same level and lower-level directory of directories
  function zen_get_subcategoriesarray(&$subcategories_array, $parent_id = 0) {
    global $db;
    $subcategories_query = "SELECT c.`categories_id`,cd.`categories_name`
                            FROM " . TABLE_CATEGORIES . " c ," . TABLE_CATEGORIES_DESCRIPTION." cd
                            WHERE c.`categories_id` = cd.`categories_id` AND c.`parent_id` = '" . (int)$parent_id . "'";

    $subcategories = $db->Execute($subcategories_query);
    
    while (!$subcategories->EOF) {
    	$num = count($subcategories_array);
    	$subcategories_array[$num] = array('categories_id' => $subcategories->fields['categories_id'],
                                        'categories_name' => $subcategories->fields['categories_name']);
      
    	$subcategories->MoveNext();
    }
    $subcategories->Move(0);
    while (!$subcategories->EOF) {
	    if ($subcategories->fields['categories_id'] != $parent_id and zen_has_category_subcategories($subcategories->fields['categories_id'])) {
	      zen_get_subcategoriesarray($subcategories_array, $subcategories->fields['categories_id']);
	    }
	    $subcategories->MoveNext();
    }
  }


////
// Recursively go through the categories and retreive all parent categories IDs
// TABLES: categories
  function zen_get_parent_categories(&$categories, $categories_id) {
    global $db;
    $parent_categories_query = "select parent_id
                                from " . TABLE_CATEGORIES . "
                                where categories_id = '" . (int)$categories_id . "'";

    $parent_categories = $db->Execute($parent_categories_query);

    while (!$parent_categories->EOF) {
      if ($parent_categories->fields['parent_id'] == 0) return true;
      $categories[sizeof($categories)] = $parent_categories->fields['parent_id'];
      if ($parent_categories->fields['parent_id'] != $categories_id) {
        zen_get_parent_categories($categories, $parent_categories->fields['parent_id']);
      }
      $parent_categories->MoveNext();
    }
  }

////
// Construct a category path to the product
// TABLES: products_to_categories
  function zen_get_product_path($products_id) {
    global $db;
    $cPath = '';

    $category_query = "select p2c.categories_id
                       from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                       where p.products_id = '" . (int)$products_id . "'
                       and p.products_status = '1'
                       and p.products_id = p2c.products_id limit 1";

    $category = $db->Execute($category_query);

    if ($category->RecordCount() > 0) {

      $categories = array();
      zen_get_parent_categories($categories, $category->fields['categories_id']);

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);

      if (zen_not_null($cPath)) $cPath .= '_';
      $cPath .= $category->fields['categories_id'];
    }

    return $cPath;
  }

////
// Parse and secure the cPath parameter values
  function zen_parse_category_path($cPath) {
// make sure the category IDs are integers
    $cPath_array = array_map('zen_string_to_int', explode('_', $cPath));

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($cPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($cPath_array[$i], $tmp_array)) {
        $tmp_array[] = $cPath_array[$i];
      }
    }

    return $tmp_array;
  }

  function zen_product_in_category($product_id, $cat_id) {
    global $db;
    $in_cat=false;
    $category_query_raw = "select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                           where products_id = '" . (int)$product_id . "'";

    $category = $db->Execute($category_query_raw);

    while (!$category->EOF) {
      if ($category->fields['categories_id'] == $cat_id) $in_cat = true;
      if (!$in_cat) {
        $parent_categories_query = "select parent_id from " . TABLE_CATEGORIES . "
                                    where categories_id = '" . $category->fields['categories_id'] . "'";

        $parent_categories = $db->Execute($parent_categories_query);
//echo 'cat='.$category->fields['categories_id'].'#'. $cat_id;

        while (!$parent_categories->EOF) {
          if (($parent_categories->fields['parent_id'] !=0) ) {
            if (!$in_cat) $in_cat = zen_product_in_parent_category($product_id, $cat_id, $parent_categories->fields['parent_id']);
          }
          $parent_categories->MoveNext();
        }
      }
      $category->MoveNext();
    }
    return $in_cat;
  }

  function zen_product_in_parent_category($product_id, $cat_id, $parent_cat_id) {
    global $db;
//echo $cat_id . '#' . $parent_cat_id;
    if ($cat_id == $parent_cat_id) {
      $in_cat = true;
    } else {
      $parent_categories_query = "select parent_id from " . TABLE_CATEGORIES . "
                                  where categories_id = '" . (int)$parent_cat_id . "'";

      $parent_categories = $db->Execute($parent_categories_query);

      while (!$parent_categories->EOF) {
        if ($parent_categories->fields['parent_id'] !=0 && !$incat) {
          $in_cat = zen_product_in_parent_category($product_id, $cat_id, $parent_categories->fields['parent_id']);
        }
        $parent_categories->MoveNext();
      }
    }
    return $in_cat;
  }


////
// products with name, model and price pulldown
  function zen_draw_products_pull_down($name, $parameters = '', $exclude = '') {
    global $currencies, $db;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $products = $db->Execute("select p.products_id, pd.products_name, p.products_price
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where p.products_id = pd.products_id
                              and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              order by products_name");

    while (!$products->EOF) {
      if (!in_array($products->fields['products_id'], $exclude)) {
        $display_price = zen_get_products_base_price($products->fields['products_id']);
        $select_string .= '<option value="' . $products->fields['products_id'] . '">' . $products->fields['products_name'] . ' (' . $currencies->format($display_price) . ')</option>';
      }
      $products->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }

////
// product pulldown with attributes
  function zen_draw_products_pull_down_attributes($name, $parameters = '', $exclude = '') {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $new_fields=', p.products_model';

    $products = $db->Execute("select distinct p.products_id, pd.products_name, p.products_price" . $new_fields ."
                              from " . TABLE_PRODUCTS . " p, " .
                                       TABLE_PRODUCTS_DESCRIPTION . " pd, " .
                                       TABLE_PRODUCTS_ATTRIBUTES . " pa " ."
                              where p.products_id= pa.products_id and p.products_id = pd.products_id
                              and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              order by products_name");

    while (!$products->EOF) {
      if (!in_array($products->fields['products_id'], $exclude)) {
        $display_price = zen_get_products_base_price($products->fields['products_id']);
        $select_string .= '<option value="' . $products->fields['products_id'] . '">' . $products->fields['products_name'] . ' (' . TEXT_MODEL . ' ' . $products->fields['products_model'] . ') (' . $currencies->format($display_price) . ')</option>';
      }
      $products->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }


////
// categories pulldown with products
  function zen_draw_products_pull_down_categories($name, $parameters = '', $exclude = '') {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $categories = $db->Execute("select distinct c.categories_id, cd.categories_name " ."
                                from " . TABLE_CATEGORIES . " c, " .
                                         TABLE_CATEGORIES_DESCRIPTION . " cd, " .
                                         TABLE_PRODUCTS_TO_CATEGORIES . " ptoc " ."
                                where ptoc.categories_id = c.categories_id
                                and c.categories_id = cd.categories_id
                                and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                order by categories_name");

    while (!$categories->EOF) {
      if (!in_array($categories->fields['categories_id'], $exclude)) {
        $select_string .= '<option value="' . $categories->fields['categories_id'] . '">' . $categories->fields['categories_name'] . '</option>';
      }
      $categories->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }

////
// categories pulldown with products with attributes
  function zen_draw_products_pull_down_categories_attributes($name, $parameters = '', $exclude = '') {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $categories = $db->Execute("select distinct c.categories_id, cd.categories_name " ."
                                from " . TABLE_CATEGORIES . " c, " .
                                         TABLE_CATEGORIES_DESCRIPTION . " cd, " .
                                         TABLE_PRODUCTS_TO_CATEGORIES . " ptoc, " .
                                         TABLE_PRODUCTS_ATTRIBUTES . " pa " ."
                                where pa.products_id= ptoc.products_id
                                and ptoc.categories_id= c.categories_id
                                and c.categories_id = cd.categories_id
                                and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                order by categories_name");

    while (!$categories->EOF) {
      if (!in_array($categories->fields['categories_id'], $exclude)) {
        $select_string .= '<option value="' . $categories->fields['categories_id'] . '">' . $categories->fields['categories_name'] . '</option>';
      }
      $categories->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }

////
// look up categories product_type
  function zen_get_product_types_to_category($lookup) {
    global $db;

    $lookup = str_replace('cPath=','',$lookup);

    $sql = "select product_type_id from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " where category_id='" . (int)$lookup . "'";
    $look_up = $db->Execute($sql);

    if ($look_up->RecordCount() > 0) {
      return $look_up->fields['product_type_id'];
    } else {
      return false;
    }
  }

//// look up parent categories name
  function zen_get_categories_parent_name($categories_id) {
    global $db;

    $lookup_query = "select parent_id from " . TABLE_CATEGORIES . " where categories_id='" . (int)$categories_id . "'";
    $lookup = $db->Execute($lookup_query);

    $lookup_query = "select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id='" . (int)$lookup->fields['parent_id'] . "'";
    $lookup = $db->Execute($lookup_query);

    return $lookup->fields['categories_name'];
  }
	
//// look up parent categories id
  function zen_get_categories_parent_id($categories_id) {
    global $db;

    $lookup_query = "select parent_id from " . TABLE_CATEGORIES . " where categories_id='" . (int)$categories_id . "'";
    $lookup = $db->Execute($lookup_query);

    return $lookup->fields['parent_id'];
  }
	
	

////
// Get all products_id in a Category and its SubCategories
// use as:
// $my_products_id_list = array();
// $my_products_id_list = zen_get_categories_products_list($categories_id)
  function zen_get_categories_products_list($categories_id, $include_deactivated = false, $include_child = true, $parent_category = '0', $display_limit = '') {
    global $db;
    global $categories_products_id_list;
    $childCatID = str_replace('_', '', substr($categories_id, strrpos($categories_id, '_')));

    $current_cPath = ($parent_category != '0' ? $parent_category . '_' : '') . $categories_id;

    $sql = "select p.products_id
            from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
            where p.products_id = p2c.products_id
            and p2c.categories_id = '" . (int)$childCatID . "'" .
            ($include_deactivated ? " and p.products_status = 1" : "") .
            $display_limit;

    $products = $db->Execute($sql);
    while (!$products->EOF) {
      $categories_products_id_list[$products->fields['products_id']] = $current_cPath;
      $products->MoveNext();
    }

    if ($include_child) {
      $sql = "select categories_id from " . TABLE_CATEGORIES . "
              where parent_id = '" . (int)$childCatID . "'";

      $childs = $db->Execute($sql);
      if ($childs->RecordCount() > 0 ) {
        while (!$childs->EOF) {
          zen_get_categories_products_list($childs->fields['categories_id'], $include_deactivated, $include_child, $current_cPath, $display_limit);
          $childs->MoveNext();
        }
      }
    }
    return $categories_products_id_list;
  }

//// bof: manage master_categories_id vs cPath
  function zen_generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
    global $db;

    if (!is_array($categories_array)) $categories_array = array();

    if ($from == 'product') {
      $categories = $db->Execute("select categories_id
                                  from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                  where products_id = '" . (int)$id . "'");

      while (!$categories->EOF) {
        if ($categories->fields['categories_id'] == '0') {
          $categories_array[$index][] = array('id' => '0', 'text' => TEXT_TOP);
        } else {
          $category = $db->Execute("select cd.categories_name, c.parent_id
                                    from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                    where c.categories_id = '" . (int)$categories->fields['categories_id'] . "'
                                    and c.categories_id = cd.categories_id
                                    and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");

          $categories_array[$index][] = array('id' => $categories->fields['categories_id'], 'text' => $category->fields['categories_name']);
          if ( (zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0') ) $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
          $categories_array[$index] = array_reverse($categories_array[$index]);
        }
        $index++;
        $categories->MoveNext();
      }
    } elseif ($from == 'category') {
      $category = $db->Execute("select cd.categories_name, c.parent_id
                                from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                where c.categories_id = '" . (int)$id . "'
                                and c.categories_id = cd.categories_id
                                and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");

      $categories_array[$index][] = array('id' => $id, 'text' => $category->fields['categories_name']);
      if ( (zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0') ) $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
    }

    return $categories_array;
  }

  function zen_output_generated_category_path($id, $from = 'category') {
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
//        $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
        $calculated_category_path_string = $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;' . $calculated_category_path_string;
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . '<br>';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -4);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
  }

  function zen_get_generated_category_path_ids($id, $from = 'category') {
    global $db;
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['id'] . '_';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -1) . '<br>';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -4);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
  }

  function zen_get_generated_category_path_rev($this_categories_id) {
    $categories = array();
    zen_get_parent_categories($categories, $this_categories_id);

    $categories = array_reverse($categories);

    $categories_imploded = implode('_', $categories);

    if (zen_not_null($categories_imploded)) $categories_imploded .= '_';
    $categories_imploded .= $this_categories_id;

    return $categories_imploded;
  }
//// bof: manage master_categories_id vs cPath
  
  function zen_get_products_ids_categories($categories_id){
  	global $db;
  	$products_ids = array();
  	$result = $db->Execute("SELECT p.`products_id` FROM ".TABLE_PRODUCTS." p, ". TABLE_PRODUCTS_TO_CATEGORIES." ptc 
  	                        WHERE p.`products_id` = ptc.`products_id`
  	                        AND p.`products_status` = 1
  	                        AND ptc.`categories_id` = ".$categories_id);
  	while (!$result->EOF){
  		$products_ids[]= $result->fields['products_id'];
  		$result->MoveNext();
  	}
  	if (zen_has_category_subcategories($categories_id)){
  		$subCategories = $db->Execute("SELECT categories_id FROM ".TABLE_CATEGORIES." WHERE parent_id = ".$categories_id);
  		while (!$subCategories->EOF){
        $products_ids = array_merge($products_ids,zen_get_products_ids_categories($subCategories->fields['categories_id']));
  			$subCategories->MoveNext();
  		}
  	}
  	return $products_ids;
  }
  
  function staticCategoriesList($listIDs,$staticWord){
  	global $db,$currencies;
  	$i = 0;
  	$rows = 1;
  	$listArray = explode(',',$listIDs);
  	$listNum = count($listArray);
  	$listNum = $listNum > 8 ? 8 : $listNum ;
  	echo '<ul class="wholesale">';
  	for ($i ;$i< $listNum ;$i++){
  		$listProducts = zen_get_products_ids_categories($listArray[$i]);
  		if (count($listProducts) > 0){
	  		$result = $db->Execute("SELECT products_image, products_price
	  		          FROM ".TABLE_PRODUCTS." p 
	  		          WHERE p.`products_id` = ".$listProducts[0]);
	  		if (is_int($rows / 4)){
		      echo '<li>';
	  		}else{
		      echo '<li class="border_r_dash">';
	  		}
	  		echo '<a href="'.zen_href_link(FILENAME_DEFAULT,'cPath='.$listArray[$i]).'" class="ih4">';
	  		echo zen_image(DIR_WS_IMAGES.str_replace('s/','l/',$result->fields['products_image']),SEO_COMMON_KEYWORDS . ' ' .zen_get_category_name($listArray[$i],$_SESSION['languages_id']),113,113);
	  		echo '</a>';
	  		echo '<div class="margin_t pad_text">';
	  		echo '<strong><a href="'.zen_href_link(FILENAME_DEFAULT,'cPath='.$listArray[$i]).'" >'.zen_get_category_name($listArray[$i],$_SESSION['languages_id']).'</a></strong>';
	  		echo '<br/>'.zen_clipped_string(zen_get_category_bottom_description($listArray[$i],$_SESSION['languages_id']),' ',150).'<br/>';
	  		echo '<h4 class="red">Price: '.$currencies->display_price($result->fields['products_price'],zen_get_tax_rate($listProducts[0])).'</h4>';
	      echo '</div>';
	  		echo '</li>';
        $rows++;
  		}else{
  			//nothing
  		}
  	}
  	echo '</ul>';

  }
?>