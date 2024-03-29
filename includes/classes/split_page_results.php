<?php
/**
 * split_page_results Class.
 *
 * @package classes
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: split_page_results.php 3041 2006-02-15 21:56:45Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Split Page Result Class
 * 
 * An sql paging class, that allows for sql reslt to be shown over a number of pages using  simple navigation system
 * Overhaul scheduled for subsequent release
 *
 * @package classes
 */
class splitPageResults extends base {
  var $sql_query, $number_of_rows, $current_page_number, $number_of_pages, $number_of_rows_per_page, $page_name;

  /* class constructor */
  function splitPageResults($query, $max_rows, $count_key = '*', $page_holder = 'page', $debug = false) {
    global $db;

    $this->sql_query = $query;
    $this->page_name = $page_holder;

    if ($debug) {
      echo 'original_query=' . $query . '<br /><br />';
    }
    if (isset($_GET[$page_holder])) {
      $page = $_GET[$page_holder];
    } elseif (isset($_POST[$page_holder])) {
      $page = $_POST[$page_holder];
    } else {
      $page = '';
    }

    if (empty($page) || !is_numeric($page)) $page = 1;
    $this->current_page_number = $page;

    $this->number_of_rows_per_page = $max_rows;

    $pos_to = strlen($this->sql_query);

    $query_lower = strtolower($this->sql_query);
    $pos_from = strpos($query_lower, ' from', 0);

    $pos_group_by = strpos($query_lower, ' group by', $pos_from);
    if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

    $pos_having = strpos($query_lower, ' having', $pos_from);
    if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

    $pos_order_by = strpos($query_lower, ' order by', $pos_from);
    if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

    if (strpos($query_lower, 'distinct') || strpos($query_lower, 'group by')) {
      $count_string = 'distinct ' . zen_db_input($count_key);
    } else {
      $count_string = zen_db_input($count_key);
    }
    $count_query = "select count(" . $count_string . ") as total " .
    substr($this->sql_query, $pos_from, ($pos_to - $pos_from));
    if ($debug) {
      echo 'count_query=' . $count_query . '<br /><br />';
    }
    $count = $db->Execute($count_query);

    $this->number_of_rows = $count->fields['total'];

    $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);

    if ($this->current_page_number > $this->number_of_pages) {
      $this->current_page_number = $this->number_of_pages;
    }

    $offset = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

    // fix offset error on some versions
    if ($offset < 0) { $offset = 0; }

    $this->sql_query .= " limit " . $offset . ", " . $this->number_of_rows_per_page;
  }

  /* class functions */

  // display split-page-number-links
  function display_links($max_page_links, $parameters = '') {
    global $request_type;

    $display_links_string = '';

    $class = '';

    if (zen_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

    // previous button - not displayed on first page
    if ($this->current_page_number > 1) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' "><span class="prev_page">' . PREVNEXT_BUTTON_PREV . '</span></a>';

    // check if number_of_pages > $max_page_links
    $cur_window_num = intval($this->current_page_number / $max_page_links);
    if ($this->current_page_number % $max_page_links) $cur_window_num++;

    $max_window_num = intval($this->number_of_pages / $max_page_links);
    if ($this->number_of_pages % $max_page_links) $max_window_num++;

    // previous window of pages
    if ($cur_window_num > 1) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . (($cur_window_num - 1) * $max_page_links), $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>';

    // page nn button
    for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
      if ($jump_to_page == $this->current_page_number) {
        $display_links_string .= '&nbsp;<strong class="current">' . $jump_to_page . '</strong>';
      } else {
        $display_links_string .= '&nbsp;<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . $jump_to_page, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' "><span>' . $jump_to_page . '</span></a>';
      }
    }

    // next window of pages
    if ($cur_window_num < $max_window_num) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . (($cur_window_num) * $max_page_links + 1), $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>&nbsp;';

    // next button
    if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . 'page=' . ($this->current_page_number + 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' "><span class="next_page">' . PREVNEXT_BUTTON_NEXT . '</span></a>';

    if ($display_links_string == '&nbsp;<strong class="current">1</strong>&nbsp;') {
      return '&nbsp;';
    } else {
      return $this->current_page_number . '/'. $this->number_of_pages .' '.$display_links_string;
    }
  }
  
  
// display split-page-number-links version 2.0
  function display_links_version2($max_page_links, $parameters = '') {
    global $request_type;
    $display_links_string = '';
    $class = '';

    if (zen_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

    
    // check if number_of_pages > $max_page_links
    $cur_window_num = intval($this->current_page_number / $max_page_links);
    if ($this->current_page_number % $max_page_links) $cur_window_num++;

    $max_window_num = intval($this->number_of_pages / $max_page_links);
    if ($this->number_of_pages % $max_page_links) $max_window_num++; 
	
	//$display_links_string .=$this->number_of_pages.'/'.$max_window_num;
	
    // previous window of pages
    if ($cur_window_num > 1) 
    {
    	$previous_window_cur = 
    	$display_links_string .= '<p class="b"><a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=1', $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . ' "><img border="0" src="/images/pre0.gif"></a></p>';    	
    }
    else 
    {
    	$display_links_string .= '<p class="b"><img border="0" src="/images/pre.gif"></p>';
    }
    
    // previous button - not displayed on first page
    if ($this->current_page_number > 1) 
    {
    	$display_links_string .= '<p class="b"><a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' class="pp"><img border="0" src="/images/pre_10.gif"></a></p>';   
    }
    else 
    {
    	$display_links_string .= '<p class="b"><img border="0" src="/images/pre_1.gif"></p>';
    }
 
    for($i=1;$i <= $this->number_of_pages;$i++){
			$pageSplit[] = array('id' => $i,'text'=>$i);
	}
	$display_links_string .='<p class="listspan">';
    // page nn button
    $half_none_cur_window_num = intval(($max_page_links-1)/2);
    $half_none_cur_window_num = $half_none_cur_window_num==0?1:$half_none_cur_window_num ;
    $left_start_at_page=0;
    if ( $this->current_page_number<$half_none_cur_window_num)
    {
    	$left_start_at_page = 1;
    }
    elseif (($this->number_of_pages- $this->current_page_number)<=$half_none_cur_window_num)
    {
    	$left_start_at_page = $this->current_page_number-($max_page_links-($this->number_of_pages- $this->current_page_number));
    }
    else 
    {
    	$left_start_at_page =  $this->current_page_number-$half_none_cur_window_num-1;
    }
    $left_start_at_page = $left_start_at_page<0?1:$left_start_at_page;
    for ($jump_to_page = $left_start_at_page; ($jump_to_page <= $left_start_at_page+$max_page_links) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {   
      if ($jump_to_page == $this->current_page_number) {
        $display_links_string .= '<strong class="r">' . $jump_to_page . '</strong>&nbsp;&nbsp;';
      } else {
        $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . $jump_to_page, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a>';
      }
    }  
    $display_links_string .='</p>';
    // next button
    if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) 
    {
    	$display_links_string .= '<p class="b"><a href="' . zen_href_link($_GET['main_page'], $parameters . 'page=' . ($this->current_page_number + 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' "><img border="0" src="/images/next0.gif"></a></p>';
    }
    else 
    {
    	$display_links_string .= '<p class="b"><img border="0" src="/images/next.gif"></p>';
    }
    
    // next window of pages
    if ($cur_window_num < $max_window_num) 
    {
    	$display_links_string .= '<p class="b"><a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . $this->number_of_pages, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . ' "><img border="0" src="/images/next_10.gif"></a></p>';
    }
    else 
    {
    	$display_links_string .= '<p class="b"><img border="0" src="/images/next_1.gif"></p>';
    }

    // go to the page
    $display_links_string .= '<p>&nbsp;Go To&nbsp;';
    $display_links_string .= zen_draw_pull_down_menu('page',$pageSplit,isset($_GET['page'])?$_GET['page']:'','onchange="changePage(this,\''.cleanSameArg('page').'\');" class="select1" rel="dropdown"').'</p>';
    
    if ($display_links_string == '&nbsp;<strong class="current">1</strong>&nbsp;') {
      return '&nbsp;';
    } else {
      return $this->current_page_number . '/'. $this->number_of_pages .' '.$display_links_string;
    }
  }

  // display number of total products found
  function display_count($text_output) {
    $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
    if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

    $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

    if ($to_num == 0) {
      $from_num = 0;
    } else {
      $from_num++;
    }

    if ($to_num <= 1) {
      // don't show count when 1
      return '';
    } else {
      return sprintf($text_output, $from_num, $to_num, $this->number_of_rows);
    }
  }
	
  function display_ProductList(){
  	
  }

	function display_drop_down($max_page_links, $parameters = ''){
		
	 global $request_type;

    $display_links_string = '';

    $class = '';

    if (zen_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

    // previous button - not displayed on first page
    if ($this->current_page_number > 1){
    	$display_links_string .= '<li><a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . 1, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, 1) . ' "><span class="first_page"></span></a></li>';
    	$display_links_string .= '<li><a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' "><span class="prev_page">' . PREVNEXT_BUTTON_PREV . '</span></a></li>';
    }

    // check if number_of_pages > $max_page_links
    $cur_window_num = intval($this->current_page_number / $max_page_links);
    if ($this->current_page_number % $max_page_links) $cur_window_num++;

    $max_window_num = intval($this->number_of_pages / $max_page_links);
    if ($this->number_of_pages % $max_page_links) $max_window_num++;

    // previous window of pages
    if ($cur_window_num > 1){
    	$display_links_string .= '<li><a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . (($cur_window_num - 1) * $max_page_links), $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a></li>';
    }
		$display_links_string .= '<li>&nbsp;|&nbsp;';
		for($i=1;$i <= $this->number_of_pages;$i++){
			$pageSplit[] = array('id' => $i,'text'=>$i);
		}
		$display_links_string .= PRODUCTS_LISTING_PAGE_TEXT.' '.zen_draw_pull_down_menu('page',$pageSplit,isset($_GET['page'])?$_GET['page']:'','onchange="changePage(this,\''.cleanSameArg('page').'\');" class="select1" rel="dropdown"');
    // page nn button
		$display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . $this->number_of_pages, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $this->number_of_pages) . ' "><span>' . $this->number_of_pages . '</span></a>';
		
		$display_links_string .= '&nbsp;|&nbsp;</li>';
    // next window of pages


    // next button
    if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)){
    	$display_links_string .= '<li><a href="' . zen_href_link($_GET['main_page'], $parameters . 'page=' . ($this->current_page_number + 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' "><span class="next_page">' . PREVNEXT_BUTTON_NEXT . '</span></a></li>';
    	$display_links_string .= '<li><a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . $this->number_of_pages, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $this->number_of_pages) . ' "><span class="last_page"></span></a></li>';
    }
    if ($display_links_string == '&nbsp;<strong class="current">1</strong>&nbsp;&nbsp;&nbsp;') {
      return '&nbsp;';
    } else {
      return $display_links_string;
    }
  }
	
  function no_current_display_links($max_page_links, $parameters = '') {
    global $request_type,$current_page;
    $display_links_string = '';

    $class = '';

    if (zen_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

    // previous button - not displayed on first page
    //if ($this->current_page_number > 1) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' "><span class="prev_page">' . PREVNEXT_BUTTON_PREV . '</span></a>';

    // check if number_of_pages > $max_page_links
    $cur_window_num = intval($this->current_page_number / $max_page_links);
    if ($this->current_page_number % $max_page_links) $cur_window_num++;

    $max_window_num = intval($this->number_of_pages / $max_page_links);
    if ($this->number_of_pages % $max_page_links) $max_window_num++;

    // previous window of pages
    //if ($cur_window_num > 1) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . (($cur_window_num - 1) * $max_page_links), $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>';

    // page nn button
    for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
      if ($jump_to_page == $this->current_page_number) {
        $display_links_string .= '&nbsp;<a href="'. HTTP_SERVER.DIR_WS_CATALOG.$current_page.'/'.$_GET['letter'].'/'.$jump_to_page.'.html" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' "><span class="current">' . $jump_to_page . '</span></a>';
      } else {
        $display_links_string .= '&nbsp;<a href="'. HTTP_SERVER.DIR_WS_CATALOG.$current_page.'/'.$_GET['letter'].'/'.$jump_to_page.'.html" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' "><span>' . $jump_to_page . '</span></a>';
      }
    }

    // next window of pages
    //if ($cur_window_num < $max_window_num) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . (($cur_window_num) * $max_page_links + 1), $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>&nbsp;';

    // next button
    //if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . 'page=' . ($this->current_page_number + 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' "><span class="next_page">' . PREVNEXT_BUTTON_NEXT . '</span></a>';

    if ($display_links_string == '&nbsp;<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . $jump_to_page, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' "><span class="current">1</span></a>&nbsp;') {
      return '&nbsp;';
    } else {
      return $display_links_string;
    }
  }
}
?>