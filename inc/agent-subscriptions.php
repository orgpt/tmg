<?php
/**
 * Agent subscriptions and package limits.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function register_agent_subscriptions_cpt(): void {
	register_post_type(
		'agent_subscriptions',
		array(
			'labels' => array(
				'name'          => __('طلبات اشتراكات الوكلاء', 'tmg-rentals'),
				'singular_name' => __('طلب اشتراك', 'tmg-rentals'),
				'menu_name'     => __('اشتراكات الوكلاء', 'tmg-rentals'),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'supports'     => array('title', 'author'),
			'menu_icon'    => 'dashicons-groups',
		)
	);
}
add_action('init', __NAMESPACE__ . '\\register_agent_subscriptions_cpt');

function register_subscriptions_route(): void {
	add_rewrite_rule('^subscriptions/?$', 'index.php?tmg_subscriptions=1', 'top');
}
add_action('init', __NAMESPACE__ . '\\register_subscriptions_route');

function register_subscriptions_query_var(array $vars): array {
	$vars[] = 'tmg_subscriptions';

	return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\register_subscriptions_query_var');

function is_subscriptions_request(): bool {
	$request_uri  = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
	$request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
	$home_path    = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

	if ($home_path !== '' && str_starts_with($request_path, $home_path . '/')) {
		$request_path = substr($request_path, strlen($home_path) + 1);
	}

	return $request_path === 'subscriptions';
}

function maybe_use_subscriptions_template(string $template): string {
	if (! ((bool) get_query_var('tmg_subscriptions') || is_subscriptions_request())) {
		return $template;
	}

	$custom_template = TMG_RENTALS_PATH . '/template-subscriptions.php';

	if (! file_exists($custom_template)) {
		return $template;
	}

	status_header(200);
	global $wp_query;
	if ($wp_query instanceof \WP_Query) {
		$wp_query->is_404      = false;
		$wp_query->is_page     = true;
		$wp_query->is_singular = true;
		$wp_query->is_home     = false;
		$wp_query->is_archive  = false;
	}

	return $custom_template;
}
add_filter('template_include', __NAMESPACE__ . '\\maybe_use_subscriptions_template', 99);

function maybe_fix_subscriptions_title(array $parts): array {
	if ((bool) get_query_var('tmg_subscriptions') || is_subscriptions_request()) {
		$parts['title'] = __('اشتراكات الوكلاء', 'tmg-rentals');
	}

	return $parts;
}
add_filter('document_title_parts', __NAMESPACE__ . '\\maybe_fix_subscriptions_title');

function maybe_fix_subscriptions_document_title(string $title): string {
	if ((bool) get_query_var('tmg_subscriptions') || is_subscriptions_request()) {
		return __('اشتراكات الوكلاء - TMG Rentals', 'tmg-rentals');
	}

	return $title;
}
add_filter('pre_get_document_title', __NAMESPACE__ . '\\maybe_fix_subscriptions_document_title', 99);

function get_account_type_choices(): array {
	return array(
		'owner' => __('مالك', 'tmg-rentals'),
		'agent' => __('وكيل', 'tmg-rentals'),
	);
}

function get_user_account_type(int $user_id): string {
	$type = (string) get_user_meta($user_id, 'tmg_account_type', true);

	return in_array($type, array('owner', 'agent'), true) ? $type : 'owner';
}

function update_user_account_type(int $user_id, string $type): void {
	update_user_meta($user_id, 'tmg_account_type', in_array($type, array('owner', 'agent'), true) ? $type : 'owner');
}

function get_agent_packages(): array {
	$packages = get_field('agent_packages', 'option');

	if (! is_array($packages)) {
		return array();
	}

	$normalized = array();

	foreach ($packages as $index => $package) {
		$title = sanitize_text_field($package['package_title'] ?? '');

		if ($title === '') {
			continue;
		}

		$key = sanitize_title($package['package_key'] ?? '');
		if ($key === '') {
			$key = 'package-' . ($index + 1);
		}

		$normalized[$key] = array(
			'key'           => $key,
			'title'         => $title,
			'price'         => (float) ($package['package_price'] ?? 0),
			'period'        => in_array($package['package_period'] ?? '', array('weekly', 'monthly'), true) ? $package['package_period'] : 'monthly',
			'listing_limit' => (int) ($package['package_listing_limit'] ?? 0),
			'description'   => sanitize_textarea_field($package['package_description'] ?? ''),
			'badge'         => sanitize_text_field($package['package_badge'] ?? ''),
		);
	}

	return $normalized;
}

function get_user_subscription(int $user_id): array {
	return array(
		'status'        => (string) get_user_meta($user_id, 'tmg_subscription_status', true),
		'package_key'   => (string) get_user_meta($user_id, 'tmg_subscription_package_key', true),
		'package_title' => (string) get_user_meta($user_id, 'tmg_subscription_package_title', true),
		'period'        => (string) get_user_meta($user_id, 'tmg_subscription_period', true),
		'limit'         => (int) get_user_meta($user_id, 'tmg_subscription_limit', true),
		'start'         => (string) get_user_meta($user_id, 'tmg_subscription_start', true),
		'end'           => (string) get_user_meta($user_id, 'tmg_subscription_end', true),
	);
}

function get_subscription_request_status(int $request_id): string {
	$status = (string) get_post_meta($request_id, 'request_status', true);

	return in_array($status, array('pending', 'approved', 'rejected'), true) ? $status : 'pending';
}

function activate_agent_subscription_for_user(int $user_id, array $package, string $start = ''): void {
	$start = $start !== '' ? $start : current_time('Y-m-d');
	$end   = date('Y-m-d', strtotime($start . ($package['period'] === 'weekly' ? ' +6 days' : ' +1 month -1 day')));

	update_user_account_type($user_id, 'agent');
	update_user_meta($user_id, 'tmg_subscription_status', 'active');
	update_user_meta($user_id, 'tmg_subscription_package_key', $package['key']);
	update_user_meta($user_id, 'tmg_subscription_package_title', $package['title']);
	update_user_meta($user_id, 'tmg_subscription_period', $package['period']);
	update_user_meta($user_id, 'tmg_subscription_limit', $package['listing_limit']);
	update_user_meta($user_id, 'tmg_subscription_start', $start);
	update_user_meta($user_id, 'tmg_subscription_end', $end);
}

function reject_agent_subscription_for_user(int $user_id): void {
	update_user_account_type($user_id, 'agent');
	update_user_meta($user_id, 'tmg_subscription_status', 'inactive');
}

function user_has_active_subscription(int $user_id): bool {
	$subscription = get_user_subscription($user_id);

	if ($subscription['status'] !== 'active' || $subscription['package_key'] === '') {
		return false;
	}

	if ($subscription['end'] !== '' && strtotime($subscription['end']) < strtotime(current_time('Y-m-d'))) {
		return false;
	}

	return true;
}

function get_subscription_window(array $subscription): array {
	$start = $subscription['start'] !== '' ? $subscription['start'] : current_time('Y-m-d');

	if ($subscription['period'] === 'weekly') {
		$end = date('Y-m-d', strtotime($start . ' +6 days'));
	} else {
		$end = date('Y-m-d', strtotime($start . ' +1 month -1 day'));
	}

	if ($subscription['end'] !== '' && strtotime($subscription['end']) < strtotime($end)) {
		$end = $subscription['end'];
	}

	return array($start, $end);
}

function get_user_subscription_usage(int $user_id): array {
	$subscription = get_user_subscription($user_id);

	if (! user_has_active_subscription($user_id)) {
		return array(
			'used'  => 0,
			'limit' => 0,
			'start' => '',
			'end'   => '',
		);
	}

	list($start, $end) = get_subscription_window($subscription);

	$query = new \WP_Query(
		array(
			'post_type'      => 'properties',
			'post_status'    => array('publish', 'pending'),
			'author'         => $user_id,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'date_query'     => array(
				array(
					'after'     => $start . ' 00:00:00',
					'before'    => $end . ' 23:59:59',
					'inclusive' => true,
				),
			),
		)
	);

	return array(
		'used'  => (int) $query->found_posts,
		'limit' => (int) $subscription['limit'],
		'start' => $start,
		'end'   => $end,
	);
}

function can_user_publish_property(int $user_id): array {
	$type = get_user_account_type($user_id);

	if ($type !== 'agent') {
		return array(
			'allowed' => true,
			'message' => '',
		);
	}

	if (! user_has_active_subscription($user_id)) {
		return array(
			'allowed' => false,
			'message' => __('حساب الوكيل يحتاج اشتراكًا فعالًا قبل نشر العقارات.', 'tmg-rentals'),
		);
	}

	$usage = get_user_subscription_usage($user_id);

	if ($usage['limit'] > 0 && $usage['used'] >= $usage['limit']) {
		return array(
			'allowed' => false,
			'message' => __('لقد وصلت إلى الحد الأقصى المسموح به في باقتك الحالية.', 'tmg-rentals'),
		);
	}

	return array(
		'allowed' => true,
		'message' => '',
	);
}

function get_subscription_notice(): array {
	$notice = get_transient('tmg_subscription_notice');

	if (! is_array($notice)) {
		return array();
	}

	delete_transient('tmg_subscription_notice');

	return $notice;
}

function handle_subscription_request_form(): void {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['tmg_subscription_action'])) {
		return;
	}

	if (! is_user_logged_in()) {
		wp_safe_redirect(home_url('/auth/?mode=login'));
		exit;
	}

	if (! isset($_POST['tmg_subscription_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tmg_subscription_nonce'])), 'tmg_subscription_request')) {
		return;
	}

	$user_id           = get_current_user_id();
	$package_key       = sanitize_text_field(wp_unslash($_POST['package_key'] ?? ''));
	$payment_method    = sanitize_text_field(wp_unslash($_POST['payment_method'] ?? ''));
	$payment_reference = sanitize_text_field(wp_unslash($_POST['payment_reference'] ?? ''));
	$agent_name        = sanitize_text_field(wp_unslash($_POST['agent_name'] ?? ''));
	$agent_phone       = sanitize_text_field(wp_unslash($_POST['agent_phone'] ?? ''));
	$packages          = get_agent_packages();

	if ($agent_name === '' || $agent_phone === '') {
		set_transient('tmg_subscription_notice', array('type' => 'error', 'message' => __('يرجى إدخال اسم الوكيل ورقم الهاتف.', 'tmg-rentals')), 60);
		wp_safe_redirect(home_url('/subscriptions/'));
		exit;
	}

	if (! isset($packages[$package_key])) {
		set_transient('tmg_subscription_notice', array('type' => 'error', 'message' => __('يرجى اختيار باقة صحيحة.', 'tmg-rentals')), 60);
		wp_safe_redirect(home_url('/subscriptions/'));
		exit;
	}

	$package = $packages[$package_key];

	$request_id = wp_insert_post(
		array(
			'post_type'   => 'agent_subscriptions',
			'post_status' => 'publish',
			'post_title'  => sprintf(__('طلب اشتراك: %s', 'tmg-rentals'), $package['title']),
			'post_author' => $user_id,
		)
	);

	if (is_wp_error($request_id) || ! $request_id) {
		set_transient('tmg_subscription_notice', array('type' => 'error', 'message' => __('تعذر إرسال طلب الاشتراك حاليًا.', 'tmg-rentals')), 60);
		wp_safe_redirect(home_url('/subscriptions/'));
		exit;
	}

	$profile_image_id = 0;

	if (! empty($_FILES['agent_profile_image']['name'])) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$profile_image_id = media_handle_upload('agent_profile_image', $request_id);

		if (is_wp_error($profile_image_id)) {
			wp_delete_post($request_id, true);
			set_transient('tmg_subscription_notice', array('type' => 'error', 'message' => __('تعذر رفع صورة البروفايل. يرجى المحاولة بصورة أخرى.', 'tmg-rentals')), 60);
			wp_safe_redirect(home_url('/subscriptions/'));
			exit;
		}
	}

	update_post_meta($request_id, 'package_key', $package['key']);
	update_post_meta($request_id, 'package_title', $package['title']);
	update_post_meta($request_id, 'package_price', $package['price']);
	update_post_meta($request_id, 'package_period', $package['period']);
	update_post_meta($request_id, 'package_listing_limit', $package['listing_limit']);
	update_post_meta($request_id, 'agent_name', $agent_name);
	update_post_meta($request_id, 'agent_phone', $agent_phone);
	update_post_meta($request_id, 'payment_method', $payment_method);
	update_post_meta($request_id, 'payment_reference', $payment_reference);
	update_post_meta($request_id, 'request_status', 'pending');

	if ($profile_image_id) {
		update_post_meta($request_id, 'agent_profile_image_id', $profile_image_id);
		update_post_meta($request_id, 'agent_profile_image_url', wp_get_attachment_url($profile_image_id));
	}

	update_user_meta($user_id, 'nickname', $agent_name);
	update_user_meta($user_id, 'tmg_agent_phone', $agent_phone);
	if ($profile_image_id) {
		update_user_meta($user_id, 'tmg_agent_profile_image_id', $profile_image_id);
		update_user_meta($user_id, 'tmg_agent_profile_image_url', wp_get_attachment_url($profile_image_id));
	}

	update_user_meta($user_id, 'tmg_account_type', 'agent');
	update_user_meta($user_id, 'tmg_subscription_status', 'pending');

	set_transient('tmg_subscription_notice', array('type' => 'success', 'message' => __('تم إرسال طلب الاشتراك بنجاح وسيتم مراجعته من الإدارة.', 'tmg-rentals')), 60);
	wp_safe_redirect(home_url('/subscriptions/'));
	exit;
}
add_action('init', __NAMESPACE__ . '\\handle_subscription_request_form');

function register_subscription_request_meta_boxes(): void {
	add_meta_box(
		'tmg_subscription_request_details',
		__('بيانات طلب الاشتراك', 'tmg-rentals'),
		__NAMESPACE__ . '\\render_subscription_request_meta_box',
		'agent_subscriptions',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', __NAMESPACE__ . '\\register_subscription_request_meta_boxes');

function render_subscription_request_meta_box(\WP_Post $post): void {
	$status            = get_subscription_request_status($post->ID);
	$agent_name        = (string) get_post_meta($post->ID, 'agent_name', true);
	$agent_phone       = (string) get_post_meta($post->ID, 'agent_phone', true);
	$package_title     = (string) get_post_meta($post->ID, 'package_title', true);
	$package_price     = (string) get_post_meta($post->ID, 'package_price', true);
	$payment_method    = (string) get_post_meta($post->ID, 'payment_method', true);
	$payment_reference = (string) get_post_meta($post->ID, 'payment_reference', true);
	$image_id          = (int) get_post_meta($post->ID, 'agent_profile_image_id', true);
	$author_id         = (int) $post->post_author;
	$approve_url       = wp_nonce_url(admin_url('edit.php?post_type=agent_subscriptions&tmg_subscription_action=approve&request_id=' . $post->ID), 'tmg_subscription_admin_action_' . $post->ID);
	$reject_url        = wp_nonce_url(admin_url('edit.php?post_type=agent_subscriptions&tmg_subscription_action=reject&request_id=' . $post->ID), 'tmg_subscription_admin_action_' . $post->ID);
	?>
	<div class="tmg-subscription-admin-card">
		<p><strong><?php esc_html_e('الحالة', 'tmg-rentals'); ?>:</strong> <?php echo esc_html($status); ?></p>
		<p><strong><?php esc_html_e('اسم الوكيل', 'tmg-rentals'); ?>:</strong> <?php echo esc_html($agent_name ?: '—'); ?></p>
		<p><strong><?php esc_html_e('رقم الهاتف', 'tmg-rentals'); ?>:</strong> <?php echo esc_html($agent_phone ?: '—'); ?></p>
		<p><strong><?php esc_html_e('الباقة', 'tmg-rentals'); ?>:</strong> <?php echo esc_html($package_title ?: '—'); ?></p>
		<p><strong><?php esc_html_e('السعر', 'tmg-rentals'); ?>:</strong> <?php echo esc_html($package_price !== '' ? number_format_i18n((float) $package_price) . ' ' . __('ج.م', 'tmg-rentals') : '—'); ?></p>
		<p><strong><?php esc_html_e('طريقة الدفع', 'tmg-rentals'); ?>:</strong> <?php echo esc_html($payment_method ?: '—'); ?></p>
		<p><strong><?php esc_html_e('مرجع الدفع', 'tmg-rentals'); ?>:</strong> <?php echo esc_html($payment_reference ?: '—'); ?></p>
		<p><strong><?php esc_html_e('الحساب', 'tmg-rentals'); ?>:</strong>
			<?php if ($author_id > 0) : ?>
				<a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $author_id)); ?>"><?php echo esc_html(get_the_author_meta('display_name', $author_id)); ?></a>
			<?php else : ?>
				<?php echo esc_html__('غير متاح', 'tmg-rentals'); ?>
			<?php endif; ?>
		</p>
		<?php if ($image_id) : ?>
			<div style="margin:1rem 0;">
				<?php echo wp_get_attachment_image($image_id, 'medium', false, array('style' => 'max-width:180px;height:auto;border-radius:12px;')); ?>
			</div>
		<?php endif; ?>
		<div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1rem;">
			<a class="button button-primary" href="<?php echo esc_url($approve_url); ?>"><?php esc_html_e('قبول الاشتراك', 'tmg-rentals'); ?></a>
			<a class="button" href="<?php echo esc_url($reject_url); ?>"><?php esc_html_e('رفض الاشتراك', 'tmg-rentals'); ?></a>
		</div>
	</div>
	<?php
}

function maybe_handle_subscription_admin_actions(): void {
	if (! is_admin() || ! current_user_can('manage_options')) {
		return;
	}

	$action     = sanitize_text_field(wp_unslash($_GET['tmg_subscription_action'] ?? ''));
	$request_id = (int) ($_GET['request_id'] ?? 0);

	if ($action === '' || $request_id <= 0) {
		return;
	}

	check_admin_referer('tmg_subscription_admin_action_' . $request_id);

	$request = get_post($request_id);

	if (! $request instanceof \WP_Post || $request->post_type !== 'agent_subscriptions') {
		return;
	}

	$user_id      = (int) $request->post_author;
	$package_key  = (string) get_post_meta($request_id, 'package_key', true);
	$packages     = get_agent_packages();

	if (! isset($packages[$package_key])) {
		wp_safe_redirect(admin_url('edit.php?post_type=agent_subscriptions&tmg_subscription_notice=invalid_package'));
		exit;
	}

	if ($action === 'approve') {
		activate_agent_subscription_for_user($user_id, $packages[$package_key]);
		update_post_meta($request_id, 'request_status', 'approved');
	} elseif ($action === 'reject') {
		reject_agent_subscription_for_user($user_id);
		update_post_meta($request_id, 'request_status', 'rejected');
	} else {
		return;
	}

	wp_safe_redirect(admin_url('edit.php?post_type=agent_subscriptions&tmg_subscription_notice=' . $action));
	exit;
}
add_action('admin_init', __NAMESPACE__ . '\\maybe_handle_subscription_admin_actions');

function add_subscription_request_row_actions(array $actions, \WP_Post $post): array {
	if ($post->post_type !== 'agent_subscriptions' || ! current_user_can('manage_options')) {
		return $actions;
	}

	$approve_url = wp_nonce_url(admin_url('edit.php?post_type=agent_subscriptions&tmg_subscription_action=approve&request_id=' . $post->ID), 'tmg_subscription_admin_action_' . $post->ID);
	$reject_url  = wp_nonce_url(admin_url('edit.php?post_type=agent_subscriptions&tmg_subscription_action=reject&request_id=' . $post->ID), 'tmg_subscription_admin_action_' . $post->ID);

	$actions['tmg_approve'] = '<a href="' . esc_url($approve_url) . '">' . esc_html__('قبول', 'tmg-rentals') . '</a>';
	$actions['tmg_reject']  = '<a href="' . esc_url($reject_url) . '">' . esc_html__('رفض', 'tmg-rentals') . '</a>';

	return $actions;
}
add_filter('post_row_actions', __NAMESPACE__ . '\\add_subscription_request_row_actions', 10, 2);

function register_subscription_request_columns(array $columns): array {
	$columns['request_status'] = __('الحالة', 'tmg-rentals');
	$columns['package_title']  = __('الباقة', 'tmg-rentals');
	$columns['agent_phone']    = __('رقم الهاتف', 'tmg-rentals');
	$columns['payment_method'] = __('الدفع', 'tmg-rentals');

	return $columns;
}
add_filter('manage_agent_subscriptions_posts_columns', __NAMESPACE__ . '\\register_subscription_request_columns');

function render_subscription_request_columns(string $column, int $post_id): void {
	if ($column === 'request_status') {
		echo esc_html(get_subscription_request_status($post_id));
		return;
	}

	if ($column === 'package_title') {
		echo esc_html((string) get_post_meta($post_id, 'package_title', true));
		return;
	}

	if ($column === 'agent_phone') {
		echo esc_html((string) get_post_meta($post_id, 'agent_phone', true));
		return;
	}

	if ($column === 'payment_method') {
		echo esc_html((string) get_post_meta($post_id, 'payment_method', true));
	}
}
add_action('manage_agent_subscriptions_posts_custom_column', __NAMESPACE__ . '\\render_subscription_request_columns', 10, 2);

function maybe_render_subscription_admin_notice(): void {
	if (! is_admin() || ! isset($_GET['post_type']) || $_GET['post_type'] !== 'agent_subscriptions' || empty($_GET['tmg_subscription_notice'])) {
		return;
	}

	$notice = sanitize_text_field(wp_unslash($_GET['tmg_subscription_notice']));
	$messages = array(
		'approve'         => __('تم قبول الاشتراك وتفعيل باقة الوكيل.', 'tmg-rentals'),
		'reject'          => __('تم رفض طلب الاشتراك.', 'tmg-rentals'),
		'invalid_package' => __('تعذر تنفيذ العملية لأن الباقة غير صالحة.', 'tmg-rentals'),
	);

	if (! isset($messages[$notice])) {
		return;
	}

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$notice]) . '</p></div>';
}
add_action('admin_notices', __NAMESPACE__ . '\\maybe_render_subscription_admin_notice');

function render_user_subscription_fields(\WP_User $user): void {
	$packages     = get_agent_packages();
	$subscription = get_user_subscription($user->ID);
	$account_type = get_user_account_type($user->ID);
	?>
	<h2><?php esc_html_e('بيانات الوكيل والاشتراك', 'tmg-rentals'); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="tmg_account_type"><?php esc_html_e('نوع الحساب', 'tmg-rentals'); ?></label></th>
			<td>
				<select name="tmg_account_type" id="tmg_account_type">
					<?php foreach (get_account_type_choices() as $key => $label) : ?>
						<option value="<?php echo esc_attr($key); ?>" <?php selected($account_type, $key); ?>><?php echo esc_html($label); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="tmg_subscription_status"><?php esc_html_e('حالة الاشتراك', 'tmg-rentals'); ?></label></th>
			<td>
				<select name="tmg_subscription_status" id="tmg_subscription_status">
					<option value="inactive" <?php selected($subscription['status'], 'inactive'); ?>><?php esc_html_e('غير مفعل', 'tmg-rentals'); ?></option>
					<option value="pending" <?php selected($subscription['status'], 'pending'); ?>><?php esc_html_e('قيد المراجعة', 'tmg-rentals'); ?></option>
					<option value="active" <?php selected($subscription['status'], 'active'); ?>><?php esc_html_e('مفعل', 'tmg-rentals'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="tmg_subscription_package_key"><?php esc_html_e('الباقة', 'tmg-rentals'); ?></label></th>
			<td>
				<select name="tmg_subscription_package_key" id="tmg_subscription_package_key">
					<option value=""><?php esc_html_e('بدون باقة', 'tmg-rentals'); ?></option>
					<?php foreach ($packages as $package) : ?>
						<option value="<?php echo esc_attr($package['key']); ?>" <?php selected($subscription['package_key'], $package['key']); ?>><?php echo esc_html($package['title']); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="tmg_subscription_start"><?php esc_html_e('تاريخ بداية الاشتراك', 'tmg-rentals'); ?></label></th>
			<td><input type="date" name="tmg_subscription_start" id="tmg_subscription_start" value="<?php echo esc_attr($subscription['start']); ?>"></td>
		</tr>
	</table>
	<?php
}
add_action('show_user_profile', __NAMESPACE__ . '\\render_user_subscription_fields');
add_action('edit_user_profile', __NAMESPACE__ . '\\render_user_subscription_fields');

function save_user_subscription_fields(int $user_id): void {
	if (! current_user_can('edit_user', $user_id)) {
		return;
	}

	$account_type = sanitize_text_field(wp_unslash($_POST['tmg_account_type'] ?? 'owner'));
	$status       = sanitize_text_field(wp_unslash($_POST['tmg_subscription_status'] ?? 'inactive'));
	$package_key  = sanitize_text_field(wp_unslash($_POST['tmg_subscription_package_key'] ?? ''));
	$start        = sanitize_text_field(wp_unslash($_POST['tmg_subscription_start'] ?? ''));
	$packages     = get_agent_packages();

	update_user_account_type($user_id, $account_type);
	update_user_meta($user_id, 'tmg_subscription_status', in_array($status, array('inactive', 'pending', 'active'), true) ? $status : 'inactive');
	update_user_meta($user_id, 'tmg_subscription_start', $start);

	if ($package_key !== '' && isset($packages[$package_key])) {
		$package = $packages[$package_key];
		update_user_meta($user_id, 'tmg_subscription_package_key', $package['key']);
		update_user_meta($user_id, 'tmg_subscription_package_title', $package['title']);
		update_user_meta($user_id, 'tmg_subscription_period', $package['period']);
		update_user_meta($user_id, 'tmg_subscription_limit', $package['listing_limit']);
		update_user_meta($user_id, 'tmg_subscription_end', $start !== '' ? date('Y-m-d', strtotime($start . ($package['period'] === 'weekly' ? ' +6 days' : ' +1 month -1 day'))) : '');
	} else {
		delete_user_meta($user_id, 'tmg_subscription_package_key');
		delete_user_meta($user_id, 'tmg_subscription_package_title');
		delete_user_meta($user_id, 'tmg_subscription_period');
		delete_user_meta($user_id, 'tmg_subscription_limit');
		delete_user_meta($user_id, 'tmg_subscription_end');
	}
}
add_action('personal_options_update', __NAMESPACE__ . '\\save_user_subscription_fields');
add_action('edit_user_profile_update', __NAMESPACE__ . '\\save_user_subscription_fields');
