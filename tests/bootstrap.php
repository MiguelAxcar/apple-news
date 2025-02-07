<?php
/**
 * Publish to Apple News Tests: Bootstrap File
 *
 * @package Apple_News
 * @subpackage Tests
 */

/* phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingVariable */

const WP_TESTS_PHPUNIT_POLYFILLS_PATH = __DIR__ . '/../vendor/yoast/phpunit-polyfills'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

$apple_news_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $apple_news_tests_dir ) {
	$apple_news_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $apple_news_tests_dir . '/includes/functions.php';

// Autoloading for prophecy.
require_once dirname( dirname( __FILE__ ) ) . '/vendor/autoload.php';

/**
 * Manually load the plugin for tests.
 */
function apple_news_manually_load_plugin() {
	// Disable VIP cache manager when testing against VIP Go integration.
	if ( method_exists( 'WPCOM_VIP_Cache_Manager', 'instance' ) ) {
		remove_action( 'init', [ WPCOM_VIP_Cache_Manager::instance(), 'init' ] );
	}

	// Set the permalink structure and domain options.
	update_option( 'home', 'https://www.example.org' );
	update_option( 'permalink_structure', '/%postname%' );
	update_option( 'siteurl', 'https://www.example.org' );

	// Force WP to treat URLs as HTTPS during testing so the home and siteurl option protocols are honored.
	$_SERVER['HTTPS'] = 1;

	// Load mocks for integration tests.
	require_once __DIR__ . '/mocks/class-bc-setup.php';
	if ( ! function_exists( 'coauthors' ) ) {
		require_once __DIR__ . '/mocks/function-coauthors.php';
	}

	// Activate mocked Brightcove functionality.
	$bc_setup = new BC_Setup();
	$bc_setup->action_init();

	// Load the plugin.
	require dirname( dirname( __FILE__ ) ) . '/apple-news.php';
}
tests_add_filter( 'muplugins_loaded', 'apple_news_manually_load_plugin' );

// Disable CAP by default - make it opt-in in tests.
tests_add_filter( 'apple_news_use_coauthors', '__return_false' );

// Filter the list of allowed protocols to allow Apple News-specific ones.
tests_add_filter(
	'kses_allowed_protocols',
	function ( $protocols ) {
		return array_merge(
			(array) $protocols,
			[
				'music',
				'musics',
				'stocks',
			]
		);
	}
);

require $apple_news_tests_dir . '/includes/bootstrap.php';

require_once __DIR__ . '/class-apple-news-testcase.php';

require_once __DIR__ . '/apple-exporter/components/class-component-testcase.php';
