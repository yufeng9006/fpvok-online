<?php
/**
 * categories sidebox - prepares content for the main categories sidebox
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: categories.php 2718 2005-12-28 06:42:39Z drbyte $
 */
		
    $row = 0;
    $priceListArray = array();
    $priceList = array();

// don't build a tree when no categories
    if(isset($list_box_contents2)) reset($list_box_contents2);
    $totalNum = sizeof($list_box_contents2);
    for ($i = 0;$i < $totalNum; $i++){
    	$priceListArray[] = $list_box_contents2[$i]['products_price'];
    }
    sort($priceListArray);
    echo '<div class="margin_t bg_box_gray pad_bottom big">';
    //echo '<h3 id="" class="allborder in_1em line_30px gray_box">Narrow By</h3>';
    echo '<h4 class="leftBoxBar gray_box margin_t">Price</h4>';
    echo '<ul class="price_box fl">';
    $priceListOutString = '';
    if ($totalNum <= 5){
    	$priceListRowNum = 1;
    }elseif ( 5 < $totalNum and $totalNum <= 15){
    	$priceListRowNum = 2;  	
    }elseif (15 < $totalNum and $totalNum <= 25){
    	$priceListRowNum = 3;
    }elseif (25 < $totalNum and $totalNum <= 35){
    	$priceListRowNum = 4;
    }elseif ($totalNum >= 35){
      $priceListRowNum = 5;
    }
    
    for ($i=0; $i<$priceListRowNum; $i++){
     echo '<li>';
     echo '<a href="'.cleanSameArg2('min_price','max_price').'&min_price='.floor($priceListArray[$totalNum*$i/$priceListRowNum]).'&max_price='.floor($priceListArray[$totalNum*($i+1)/$priceListRowNum-1]).'">';
     echo $currencies->display_price($priceListArray[$totalNum*$i/$priceListRowNum],zen_get_tax_rate($_GET['products_tax_class_id'])).' - '.$currencies->display_price($priceListArray[$totalNum*($i+1)/$priceListRowNum-1],zen_get_tax_rate($_GET['products_tax_class_id']));    
     echo '</a>';
     echo '</li>';
    }
    echo '<li class="manual">'.zen_draw_form('priceSearch', cleanSameArg2('min_price','max_price'),'get','onsubmit="return(fmChk(this))"').'<div class="leftPrice">'.zen_draw_input_field('min_price', '', ' size="5" id="min_price" chkname="Min Price" chkrule="nnull"').' - </div><div class="rightPrice">'.zen_draw_input_field('max_price', '', ' size="5" id="max_price" chkname="Max Price" chkrule="nnull"').'</div><div class="priceSearchButton">'.zen_image_submit(BUTTON_IMAGE_SEND, 'Go').'</div><div class="clear"></div></form></li>';
    echo '</ul>';
    echo '</div>';
?>
<div class="clear"></div>