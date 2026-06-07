<?php
/**
 * Theme authentication routes and handlers.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function register_auth_routes(): void {
	add_rewrite_rule('^auth/?$', 'index.php?tmg_auth=1', 'top');
	add_rewrite_rule('^login/?$', 'index.php?tmg_auth=1&auth_mode=login', 'top');
	add_rewrite_rule('^register/?$', 'index.php?tmg_auth=1&auth_mode=register', 'top');
	add_rewrite_rule('^logout/?$', 'index.php?tmg_logout=1', 'top');
}
add_action('init', __NAMESPACE__ . '\\register_auth_routes');

function register_auth_query_vars(array $vars): array {
	$vars[] = 'tmg_auth';
	$vars[] = 'auth_mode';
	$vars[] = 'tmg_logout';

	return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\register_auth_query_vars');

function is_auth_request(): bool {
	$request_uri  = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
	$request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
	$home_path    = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

	if ($home_path !== '' && str_starts_with($request_path, $home_path . '/')) {
		$request_path = substr($request_path, strlen($home_path) + 1);
	}

	return in_array($request_path, array('auth', 'login', 'register'), true);
}

function get_auth_mode(): string {
	$mode = sanitize_text_field((string) get_query_var('auth_mode'));

	if ($mode === '') {
		$request_uri  = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
		$request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');

		if (str_ends_with($request_path, 'register')) {
			$mode = 'register';
		}
	}

	return $mode === 'register' ? 'register' : 'login';
}

function maybe_use_auth_template(string $template): string {
	$is_auth = (bool) get_query_var('tmg_auth') || is_auth_request();

	if (! $is_auth) {
		return $template;
	}

	$custom_template = TMG_RENTALS_PATH . '/template-auth.php';

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
add_filter('template_include', __NAMESPACE__ . '\\maybe_use_auth_template', 99);

function normalize_auth_query(): void {
	if (! ((bool) get_query_var('tmg_auth') || is_auth_request())) {
		return;
	}

	global $wp_query;

	if ($wp_query instanceof \WP_Query) {
		$wp_query->is_404      = false;
		$wp_query->is_page     = true;
		$wp_query->is_singular = true;
		$wp_query->is_home     = false;
		$wp_query->is_archive  = false;
	}
}
add_action('template_redirect', __NAMESPACE__ . '\\normalize_auth_query', 1);

function maybe_fix_auth_document_title(array $parts): array {
	if ((bool) get_query_var('tmg_auth') || is_auth_request()) {
		$parts['title'] = get_auth_mode() === 'register' ? __('إنشاء حساب', 'tmg-rentals') : __('تسجيل الدخول', 'tmg-rentals');
	}

	return $parts;
}
add_filter('document_title_parts', __NAMESPACE__ . '\\maybe_fix_auth_document_title');

function maybe_fix_auth_title_text(string $title): string {
	if ((bool) get_query_var('tmg_auth') || is_auth_request()) {
		$page_title = get_auth_mode() === 'register' ? __('إنشاء حساب', 'tmg-rentals') : __('تسجيل الدخول', 'tmg-rentals');

		return $page_title . ' - TMG Rentals';
	}

	return $title;
}
add_filter('pre_get_document_title', __NAMESPACE__ . '\\maybe_fix_auth_title_text', 99);

function maybe_handle_logout(): void {
	$is_logout = (bool) get_query_var('tmg_logout');

	if (! $is_logout) {
		$request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
		$path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
		$is_logout = $path === 'logout';
	}

	if (! $is_logout) {
		return;
	}

	wp_logout();
	wp_safe_redirect(home_url('/auth/?logged_out=1'));
	exit;
}
add_action('template_redirect', __NAMESPACE__ . '\\maybe_handle_logout');

function handle_auth_forms(): void {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['tmg_auth_action'])) {
		return;
	}

	$action = sanitize_text_field(wp_unslash($_POST['tmg_auth_action']));

	if ($action === 'login') {
		handle_login_submission();
		return;
	}

	if ($action === 'register') {
		handle_register_submission();
	}
}
add_action('init', __NAMESPACE__ . '\\handle_auth_forms');

function handle_login_submission(): void {
	if (! isset($_POST['tmg_auth_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tmg_auth_nonce'])), 'tmg_auth_login')) {
		return;
	}

	$creds = array(
		'user_login'    => sanitize_text_field(wp_unslash($_POST['log'] ?? '')),
		'user_password' => (string) wp_unslash($_POST['pwd'] ?? ''),
		'remember'      => ! empty($_POST['rememberme']),
	);

	$user = wp_signon($creds, is_ssl());

	if (is_wp_error($user)) {
		set_transient('tmg_auth_notice', array('type' => 'error', 'message' => $user->get_error_message()), 60);
		wp_safe_redirect(home_url('/auth/?mode=login'));
		exit;
	}

	$redirect = get_user_account_type($user->ID) === 'agent' ? home_url('/subscriptions/') : home_url('/add-property/');
	wp_safe_redirect($redirect);
	exit;
}

function handle_register_submission(): void {
	if (! isset($_POST['tmg_auth_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tmg_auth_nonce'])), 'tmg_auth_register')) {
		return;
	}

	$name         = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
	$email        = sanitize_email(wp_unslash($_POST['user_email'] ?? ''));
	$password     = (string) wp_unslash($_POST['user_password'] ?? '');
	$account_type = sanitize_text_field(wp_unslash($_POST['account_type'] ?? 'owner'));

	if ($name === '' || $email === '' || $password === '') {
		set_transient('tmg_auth_notice', array('type' => 'error', 'message' => __('يرجى استكمال جميع الحقول المطلوبة.', 'tmg-rentals')), 60);
		wp_safe_redirect(home_url('/auth/?mode=register'));
		exit;
	}

	if (email_exists($email)) {
		set_transient('tmg_auth_notice', array('type' => 'error', 'message' => __('هذا البريد الإلكتروني مستخدم بالفعل.', 'tmg-rentals')), 60);
		wp_safe_redirect(home_url('/auth/?mode=register'));
		exit;
	}

	$username = sanitize_user(current(explode('@', $email)), true);
	$base_username = $username !== '' ? $username : 'user';
	$counter = 1;

	while (username_exists($username)) {
		$username = $base_username . $counter;
		$counter++;
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $username,
			'user_pass'    => $password,
			'user_email'   => $email,
			'display_name' => $name,
			'first_name'   => $name,
			'role'         => 'subscriber',
		)
	);

	if (is_wp_error($user_id)) {
		set_transient('tmg_auth_notice', array('type' => 'error', 'message' => $user_id->get_error_message()), 60);
		wp_safe_redirect(home_url('/auth/?mode=register'));
		exit;
	}

	update_user_account_type((int) $user_id, $account_type);
	wp_set_current_user((int) $user_id);
	wp_set_auth_cookie((int) $user_id, true);

	set_transient('tmg_auth_notice', array('type' => 'success', 'message' => __('تم إنشاء الحساب بنجاح.', 'tmg-rentals')), 60);
	$redirect = $account_type === 'agent' ? home_url('/subscriptions/') : home_url('/add-property/');
	wp_safe_redirect($redirect);
	exit;
}

function get_google_auth_settings(): array {
	return array(
		'client_id' => (string) get_field('google_client_id', 'option'),
	);
}

function get_auth_notice(): array {
	$notice = get_transient('tmg_auth_notice');

	if (! is_array($notice)) {
		return array();
	}

	delete_transient('tmg_auth_notice');

	return $notice;
}

function maybe_redirect_logged_in_auth(): void {
	if (! ((bool) get_query_var('tmg_auth') || is_auth_request())) {
		return;
	}

	if (is_user_logged_in()) {
		$redirect = get_user_account_type(get_current_user_id()) === 'agent' ? home_url('/subscriptions/') : home_url('/add-property/');
		wp_safe_redirect($redirect);
		exit;
	}
}
add_action('template_redirect', __NAMESPACE__ . '\\maybe_redirect_logged_in_auth', 5);

function handle_google_auth_ajax(): void {
	check_ajax_referer('tmg_google_auth', 'nonce');

	$credential = sanitize_text_field(wp_unslash($_POST['credential'] ?? ''));
	$settings   = get_google_auth_settings();
	$client_id  = $settings['client_id'];

	if ($credential === '' || $client_id === '') {
		wp_send_json_error(array('message' => __('تعذر بدء تسجيل الدخول عبر Google.', 'tmg-rentals')), 400);
	}

	$response = wp_remote_get('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($credential), array('timeout' => 15));

	if (is_wp_error($response)) {
		wp_send_json_error(array('message' => __('تعذر التحقق من حساب Google حالياً.', 'tmg-rentals')), 500);
	}

	$payload = json_decode((string) wp_remote_retrieve_body($response), true);

	if (! is_array($payload)) {
		wp_send_json_error(array('message' => __('استجابة غير صالحة من Google.', 'tmg-rentals')), 500);
	}

	$aud = (string) ($payload['aud'] ?? '');
	$iss = (string) ($payload['iss'] ?? '');
	$exp = (int) ($payload['exp'] ?? 0);
	$email = sanitize_email($payload['email'] ?? '');
	$name = sanitize_text_field($payload['name'] ?? '');
	$email_verified = (string) ($payload['email_verified'] ?? '');

	if ($aud !== $client_id || ! in_array($iss, array('accounts.google.com', 'https://accounts.google.com'), true) || $exp < time() || $email === '' || $email_verified !== 'true') {
		wp_send_json_error(array('message' => __('فشل التحقق من هوية Google.', 'tmg-rentals')), 403);
	}

	$user = get_user_by('email', $email);

	if (! $user instanceof \WP_User) {
		$username = sanitize_user(current(explode('@', $email)), true);
		$base_username = $username !== '' ? $username : 'googleuser';
		$counter = 1;

		while (username_exists($username)) {
			$username = $base_username . $counter;
			$counter++;
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $username,
				'user_pass'    => wp_generate_password(32, true, true),
				'user_email'   => $email,
				'display_name' => $name !== '' ? $name : $email,
				'first_name'   => $name,
				'role'         => 'subscriber',
			)
		);

		if (is_wp_error($user_id)) {
			wp_send_json_error(array('message' => $user_id->get_error_message()), 500);
		}

		update_user_account_type((int) $user_id, 'owner');
		$user = get_user_by('id', (int) $user_id);
	}

	wp_set_current_user($user->ID);
	wp_set_auth_cookie($user->ID, true);

	$redirect = get_user_account_type($user->ID) === 'agent' ? home_url('/subscriptions/') : home_url('/add-property/');
	wp_send_json_success(array('redirect' => $redirect));
}
add_action('wp_ajax_nopriv_tmg_google_auth', __NAMESPACE__ . '\\handle_google_auth_ajax');
add_action('wp_ajax_tmg_google_auth', __NAMESPACE__ . '\\handle_google_auth_ajax');

function get_theme_login_url(string $mode = 'login'): string {
	$mode = $mode === 'register' ? 'register' : 'login';

	return home_url('/auth/?mode=' . $mode);
}

function maybe_redirect_default_login_screen(): void {
	if (is_user_logged_in()) {
		return;
	}

	$action = sanitize_text_field(wp_unslash($_REQUEST['action'] ?? 'login'));

	if (in_array($action, array('logout', 'postpass'), true)) {
		return;
	}

	$mode = $action === 'register' ? 'register' : 'login';

	wp_safe_redirect(get_theme_login_url($mode));
	exit;
}
add_action('login_init', __NAMESPACE__ . '\\maybe_redirect_default_login_screen');

function maybe_redirect_protected_admin(): void {
	if (wp_doing_ajax()) {
		return;
	}

	if (defined('REST_REQUEST') && REST_REQUEST) {
		return;
	}

	if (! is_admin()) {
		return;
	}

	$request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
	$request_path = strtolower((string) wp_parse_url($request_uri, PHP_URL_PATH));

	if (str_ends_with($request_path, '/async-upload.php') || str_ends_with($request_path, '/admin-post.php')) {
		return;
	}

	if (! is_user_logged_in()) {
		wp_safe_redirect(get_theme_login_url('login'));
		exit;
	}

	if (current_user_can('manage_options')) {
		return;
	}

	$redirect = get_user_account_type(get_current_user_id()) === 'agent'
		? home_url('/agent-dashboard/')
		: home_url('/add-property/');

	wp_safe_redirect($redirect);
	exit;
}
add_action('admin_init', __NAMESPACE__ . '\\maybe_redirect_protected_admin');

function filter_theme_login_url(string $login_url, string $redirect = '', bool $force_reauth = false): string {
	$url = get_theme_login_url('login');

	if ($redirect !== '') {
		$url = add_query_arg('redirect_to', rawurlencode($redirect), $url);
	}

	return $url;
}
add_filter('login_url', __NAMESPACE__ . '\\filter_theme_login_url', 10, 3);

function filter_theme_register_url(string $register_url): string {
	return get_theme_login_url('register');
}
add_filter('register_url', __NAMESPACE__ . '\\filter_theme_register_url');

function filter_theme_lostpassword_url(string $lostpassword_url, string $redirect = ''): string {
	$url = get_theme_login_url('login');

	if ($redirect !== '') {
		$url = add_query_arg('redirect_to', rawurlencode($redirect), $url);
	}

	return $url;
}
add_filter('lostpassword_url', __NAMESPACE__ . '\\filter_theme_lostpassword_url', 10, 2);
