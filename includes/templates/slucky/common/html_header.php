<?php
/**
 * Common Template
 *
 * outputs the html header. i,e, everything that comes before the \</head\> tag <br />
 * 
 * @package templateSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: html_header.php 6948 2007-09-02 23:30:49Z drbyte $
 */
/**
 * load the module for generating page meta-tags
 */
require(DIR_WS_MODULES . zen_get_module_directory('meta_tags.php'));
/**
 * output main page HEAD tag and related headers/meta-tags, etc
 */
  if ($_GET['main_page'] != 'advanced_search_result'){
	  if(isset($_GET['display']) and is_numeric(substr($_GET['display'],-1,1))){
	    $listTypes = substr($_GET['display'],-1,1);
	    zen_setcookie('listTypes',$listTypes,time()+60*60*24*30);
	  }else{
	    if ($_COOKIE['listTypes']) {
	      $listTypes = $_COOKIE['listTypes'];
	    }
	  }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<title><?php echo META_TAG_TITLE; ?></title>
<meta name="ROBOTS" content="All" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<meta name="keywords" content="<?php echo META_TAG_KEYWORDS; ?>" />
<meta name="description" content="<?php echo META_TAG_DESCRIPTION; ?>" />
<meta http-equiv="imagetoolbar" content="no" />
<?php echo rss_feed_link_alternate(); // RSS Feed ?>
<meta name="author" content="www.fpvok.net" />
<?php if (defined('FAVICON')) { ?>
<link rel="icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
<?php } //endif FAVICON ?>

<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>" />
<script type="text/javascript">var baseURL = "<?php echo HTTP_SERVER . DIR_WS_CATALOG?>"</script>
<?php

echo '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/global.css" />'."\n";
/**
 * load all template-specific stylesheets, named like "style*.css", alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^style/', '.css');
  while(list ($key, $value) = each($directory_array)) {
    echo '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '" />'."\n";
  }
/**
 * load stylesheets on a per-page/per-language/per-product/per-manufacturer/per-category basis. Concept by Juxi Zoza.
 */
  $manufacturers_id = (isset($_GET['manufacturers_id'])) ? $_GET['manufacturers_id'] : '';
  $tmp_products_id = (isset($_GET['products_id'])) ? (int)$_GET['products_id'] : '';
  $tmp_pagename = ($this_is_home_page) ? 'index_home' : $current_page_base;
  $sheets_array = array('/' . $_SESSION['language'] . '_stylesheet', 
                        '/' . $tmp_pagename, 
                        '/' . $_SESSION['language'] . '_' . $tmp_pagename, 
                        '/c_' . $cPath,
                        '/' . $_SESSION['language'] . '_c_' . $cPath,
                        '/m_' . $manufacturers_id,
                        '/' . $_SESSION['language'] . '_m_' . (int)$manufacturers_id, 
                        '/p_' . $tmp_products_id,
                        '/' . $_SESSION['language'] . '_p_' . $tmp_products_id
                        );
  while(list ($key, $value) = each($sheets_array)) {
    //echo "<!--looking for: $value-->\n";
    $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . $value . '.css';
    if (file_exists($perpagefile)) echo '<link rel="stylesheet" type="text/css" href="' . $perpagefile .'" />'."\n";
  }

/**
 * load printer-friendly stylesheets -- named like "print*.css", alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^print/', '.css');
  sort($directory_array);
  while(list ($key, $value) = each($directory_array)) {
    echo '<link rel="stylesheet" type="text/css" media="print" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '" />'."\n";
  }

/**
 * load all site-wide jscript_*.js files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'), '/^jscript_/', '.js');
  while(list ($key, $value) = each($directory_array)) {
    echo '<script type="text/javascript" src="' .  $template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value . '"></script>'."\n";
  }

/**
 * load all page-specific jscript_*.js files from includes/modules/pages/PAGENAME, alphabetically
 */
  $directory_array = $template->get_template_part($page_directory, '/^jscript_/', '.js');
  while(list ($key, $value) = each($directory_array)) {
    echo '<script type="text/javascript" src="' . $page_directory . '/' . $value . '"></script>' . "\n";
  }

/**
 * load all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'jscript'), '/^jscript_/', '.php');
  while(list ($key, $value) = each($directory_array)) {
/**
 * include content from all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically.
 * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
 */
    require($template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value); echo "\n";
  }
/**
 * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
 */
  $directory_array = $template->get_template_part($page_directory, '/^jscript_/');
  while(list ($key, $value) = each($directory_array)) {
/**
 * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
 * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
 */
    require($page_directory . '/' . $value); echo "\n";
  }

  echo rss_feed_link_alternate(); 
//DEBUG: echo '<!-- I SEE cat: ' . $current_category_id . ' || vs cpath: ' . $cPath . ' || page: ' . $current_page . ' || template: ' . $current_template . ' || main = ' . ($this_is_home_page ? 'YES' : 'NO') . ' -->';
?>
<LINK rel=stylesheet type=text/css 
href="/imgts/ddsmoothmenu.css">
<SCRIPT type=text/javascript 
src="/imgts/ddsmoothmenu.js"></SCRIPT>
<link rel="stylesheet" type="text/css" href="images/header_buttom.css">
<script type="text/javascript" src="images/Currency.js"></script>
<?php if($_GET['main_page']=='product_info' && false){?>
<script type="text/javascript" src="images/jquery1.js"></script>
<?php }?>
<script language="javascript"  type="text/javascript"><!--
var FRIENDLY_URLS='true';
function sortFocus(obj){
	if(isIE){
		obj.value ='';
	}
	else{
		o=new Option('','-1');
	    obj.options.add(o);
		obj.value ='-1';
	}	
}
function sortBlur(obj, value){
	if(isIE){
		obj.value ='value';
	}
}
function changeSort(obj, sort_url){
	if(obj.value != '-1'){
		if(sort_url.indexOf('?') > -1){
			window.location.href= sort_url + "&productsort=" + obj.value;
		}
		else{
			window.location.href= sort_url + "?productsort=" + obj.value;
		}
	}
}
function changePagesize(obj, sort_url){
	if(obj.value != '-1'){
		if(sort_url.indexOf('?') > -1){
			window.location.href= sort_url + "&pagesize=" + obj.value;
		}
		else{
			window.location.href= sort_url + "?pagesize=" + obj.value;
		}
	}
}
function changePage(obj, sort_url){
	if(obj.value != '-1'){
		if(sort_url.indexOf('?') > -1){
			window.location.href= sort_url + "&page=" + obj.value;
		}
		else{
			window.location.href= sort_url + "?page=" + obj.value;
		}
	}	
}
function getCookie(sName)
{
  // cookies are separated by semicolons
  var aCookie = document.cookie.split("; ");
  for (var i=0; i < aCookie.length; i++)
  {
  // a name/value pair (a crumb) is separated by an equal sign
  var aCrumb = aCookie[i].split("=");
  if (sName == aCrumb[0])
    return unescape(aCrumb[1]);
  }
    // a cookie with the requested name does not exist
    return null;
}
function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
  var expires = new Date();
  expires.setTime(expires.getTime() + seconds);
  document.cookie = escape(cookieName) + '=' + escape(cookieValue)
    + (expires ? '; expires=' + expires.toGMTString() : '')
    + (path ? '; path=' + path : '/')
    + (domain ? '; domain=' + domain : '')
    + (secure ? '; secure' : '');
}
//--></script>
<!--[if IE 6]><script>
document.execCommand("BackgroundImageCache", false, true);
</script><![endif]-->
</head>
<?php // NOTE: Blank line following is intended: ?>

