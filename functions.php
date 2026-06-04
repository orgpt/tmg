<?php
/**
 * Theme bootstrap for TMG Rentals.
 *
 * @package TMG_Rentals
 */

if (! defined('ABSPATH')) {
	exit;
}

define('TMG_RENTALS_VERSION', '1.0.0');
define('TMG_RENTALS_PATH', get_template_directory());
define('TMG_RENTALS_URL', get_template_directory_uri());

require_once TMG_RENTALS_PATH . '/inc/theme-setup.php';
require_once TMG_RENTALS_PATH . '/inc/post-types.php';
require_once TMG_RENTALS_PATH . '/inc/acf-fields.php';
require_once TMG_RENTALS_PATH . '/inc/template-tags.php';
require_once TMG_RENTALS_PATH . '/inc/frontend-submission.php';
require_once TMG_RENTALS_PATH . '/inc/auth.php';
