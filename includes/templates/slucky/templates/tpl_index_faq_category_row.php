<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: faq_manager.php 001 2005-03-27 dave@open-operations.com
//
?>
       <td align="left" class="smallText" width="<?php echo $width; ?> " valign="top"><a href="<?php echo zen_href_link(FILENAME_FAQS, $fcPath_new); ?>"><?php echo $faq_categories->fields['faq_categories_name']; ?></a><br><?php echo $faq_categories->fields['faq_categories_description']; ?><br></td>
<?php
  if ($newrow) {
?>
    </tr>
    <tr>
<?php
  }
?>