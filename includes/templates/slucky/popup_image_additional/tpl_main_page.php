<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @package templateSystem
 * @copyright Copyright 2005-2006 breakmyzencart.com
 * @copyright Portions Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_main_page.php,v 1.1 2006/05/01 12:35:10 tim Exp $
 */
?>
<body id="popupAdditionalImage" class="centeredContent" onLoad="resize();">
<div>
<?php
// $products_values->fields['products_image']
  echo '<a href="javascript:window.close()">' . zen_image($_GET['products_image_large_additional'], SEO_COMMON_KEYWORDS . ' ' .$products_values->fields['products_name'] . ' ' . TEXT_CLOSE_WINDOW) . '</a>';
?>
</div>
</body>