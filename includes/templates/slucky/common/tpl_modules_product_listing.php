<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_product_listing.php 3241 2006-03-22 04:27:27Z ajeh $
 */
 include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING));
?>
<?php
	if($linkMark = strpos($_SERVER['REQUEST_URI'],'?')){
		$cleanUrl = substr($_SERVER['REQUEST_URI'],0,$linkMark);
	}else{
		$cleanUrl = $_SERVER['REQUEST_URI'];
	}
	function cleanSameArg($clean){
		global $_GET,$cleanUrl;
		$newArg = array();
		reset($_GET);
		while (list($key, $value) = each($_GET)) {
			if($key != 'main_page' and $key != 'cPath' and $key != $clean){
				$newArg[] = $key.'='.$value;				
			}
    }
    if(sizeof($newArg)>0){
    	return $cleanUrl.'?'.implode('&',$newArg);
    }else{
    	return $cleanUrl;
    }
	}
	function postfixUrl(){
		global $_SERVER;
		$posbool = strpos($_SERVER['REQUEST_URI'],'?');
		return (is_int($posbool) ? substr($_SERVER['REQUEST_URI'],$posbool) : '');
	}
	?>
<?php 
  if($current_page == 'index'){
  	if ($categories_displayTypes == 1){
	  	echo '<br class="clear">';
  	}
  }?>
<div class="allborder" style="margin-top:5px;">
<?php if ( ($listing_split->number_of_rows > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>
<div class="carybar"></div>
<div class="pagebar border_b white_bg"><span class="fl"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></span><div class="split_pages"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links_version2(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></div></div>
<?php if($current_page == 'index') {?>
	<div class="list_bar">
	  <ul>
	    <li><strong>&nbsp;&nbsp;View:&nbsp;&nbsp;</strong></li>
	    <li class="li1">
	    <?php switch($listTypes){
	              case '2':?>
	      <a href="<?php echo zen_href_link(FILENAME_DEFAULT, zen_get_all_get_params(array('display','cPath')).'cPath=' . $current_category_id.'&display='.$displayTypes.'-1');?>"><span class="list_list">List</span></a>
	      <span class="list_grid">Grid</span>  
	      <a href="<?php echo zen_href_link(FILENAME_DEFAULT, zen_get_all_get_params(array('display','cPath')).'cPath=' . $current_category_id.'&display='.$displayTypes.'-3');?>"><span class="list_gallery">Gallery</span></a>  
	    <?php     break;
	              case '3': ?>
	      <a href="<?php echo zen_href_link(FILENAME_DEFAULT, zen_get_all_get_params(array('display','cPath')).'cPath=' . $current_category_id.'&display='.$displayTypes.'-1');?>"><span class="list_list">List</span></a>
	      <a href="<?php echo zen_href_link(FILENAME_DEFAULT, zen_get_all_get_params(array('display','cPath')).'cPath=' . $current_category_id.'&display='.$displayTypes.'-2');?>"><span class="list_grid">Grid</span></a>
	      <span class="list_gallery">Gallery</span>       
	    <?php     break;
	              default:?>
        <span class="list_list">List</span>
        <a href="<?php echo zen_href_link(FILENAME_DEFAULT, zen_get_all_get_params(array('display','cPath')).'cPath=' . $current_category_id.'&display='.$displayTypes.'-2');?>"><span class="list_grid">Grid</span></a>    
        <a href="<?php echo zen_href_link(FILENAME_DEFAULT, zen_get_all_get_params(array('display','cPath')).'cPath=' . $current_category_id.'&display='.$displayTypes.'-3');?>"><span class="list_gallery">Gallery</span></a>    
      <?php break;
	    }
	    ?>
	    </li>
	    <li class="li3"><strong>Sorted By: </strong><?php echo zen_draw_pull_down_menu('productsort',$productsort, (isset($_GET['productsort']) ? $_GET['productsort'] : ''), 'onchange="changeSort(this,\''.cleanSameArg('productsort').'\');" class="select" rel="dropdown"');?></li>
	    <li class="li3"><strong>Show: </strong> <?php echo zen_draw_pull_down_menu('pagesize',$pagesize, (isset($_GET['pagesize']) ? $_GET['pagesize'] : '20'), 'onchange="changePagesize(this,\''.cleanSameArg('pagesize').'\');" class="select1" rel="dropdown"');?>
	  </ul>
	</div>
	
	<?php
	switch($listTypes){
	    case '2':
	      require($template->get_template_dir('tpl_grid_display.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_grid_display.php');
	    break;
	    case '3':
	      require($template->get_template_dir('tpl_gallery_display.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_gallery_display.php');
	    break;
	    default:
	      require($template->get_template_dir('tpl_tabular_display.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_tabular_display.php');
	    break;
	  }
	}else{
	     require($template->get_template_dir('tpl_tabular_display.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_tabular_display.php');
	}
}else{
	echo '<div class="error_box maxwidth" style="width:500px;">In categories no products</div>';
}
?>

<?php if ( ($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
?>
<div class="pagebar margin_t g_t_c white_bg"><div class="split_pages"><p class="listspan"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links_version2(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></p></div></div>
<?php
  }
?>

<?php
// only show when there is something to submit and enabled
    if ($show_bottom_submit_button == true) {
?>
<div class="pad_10px fr"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"'); ?></div>
<br class="clear" />
<?php
    } // show_bottom_submit_button
?>
</div>

<?php
// if ($show_top_submit_button == true or $show_bottom_submit_button == true or (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 and $show_submit == true and $listing_split->number_of_rows > 0)) {
  if ($show_top_submit_button == true or $show_bottom_submit_button == true) {
?>
</form>
<?php } ?>
