<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_search_header.php 4143 2007-11-15 17:40:59Z numinix $
 */
                           
  $categories_array[] = array('id' => '', 'text' =>'All Categories'); 
                                $categories_query = "select c.categories_id, cd.categories_name, c.categories_status from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd  where c.categories_status='1' and parent_id = '0' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by sort_order, cd.categories_name"; 
                                $categories = $db->Execute($categories_query); 
                                while (!$categories->EOF) {
	                                $categories_array[] = array('id' => $categories->fields['categories_id'], 'text' =>$categories->fields['categories_name']); $categories->MoveNext();
	                              }
	                               
  $content = '<div class="search_bar fl" style="margin-bottom: 10px;">';
  $content .= zen_draw_form('quick_find_header', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get','id = "quick_find_header" onsubmit="advance_search_submit();return false;"');
  $content .= zen_draw_hidden_field('main_page',FILENAME_ADVANCED_SEARCH_RESULT);
  $content .= zen_draw_hidden_field('inc_subcat', '1', 'style="display: none"'); 
  $content .= zen_draw_hidden_field('search_in_description', '1') . zen_hide_session_id();
	$content .= '<ul id="search_con" class="use_nav_bg"><b></b><li>';
 
		$content .= '<input type="text" onblur="if (this.value == \'\') this.value = \'Enter search keywords here\';" onfocus="if (this.value == \'Enter search keywords here\') this.value = \'\';" value="Enter search keywords here" id="keyword" class="input" name="keyword"></li><li><a class="btn_search" onclick="if($(\'keyword\').value==\'Enter search keywords here\'){alert(\'Please submit the keyword!\');}else{document.quick_find_header.submit();}return false;" href="javascript:void(0);"></a></li></ul>'; 
	
	if($_SESSION['cart']->count_contents()>0){
  $content .='<ul id="shoping_con"><li style="padding-left: 50px;padding-top: 10px;""><a  class="weee45" href="'.zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL') .'" rel="nofollow"><span> Cart '. $_SESSION['cart']->count_contents() .' Item(s)</span></a></li></ul>';
	}else{
  $content .='<ul id="shoping_con" ><li style="padding-left: 50px;padding-top: 10px;""><a  class="weee45" href="'.zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL') .'"  rel="nofollow"><span>'.HEADER_TITLE_SHOPPING_CART.'</span></a></li></ul>';
	}
  $content .= '</form></div>';
?>
