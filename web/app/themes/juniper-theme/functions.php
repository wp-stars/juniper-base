<?php
/**
 * Timber Juniper Theme
 * https://github.com/osomstudio/JuniperTheme
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

/**
 * Define Constants
 */
define( 'THEME_DIR', trailingslashit( get_stylesheet_directory() ) );
define( 'THEME_URI', trailingslashit( esc_url( get_stylesheet_directory_uri() ) ) );

/**
 * If you are installing Timber as a Composer dependency in your theme, you'll need this block
 * to load your dependencies and initialize Timber. If you are using Timber via the WordPress.org
 * plug-in, you can safely delete this block.
 */
$composer_autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;

	$timber = new \Timber\Timber();
}

require_once 'inc/include.php';

/**
 * @param callable $endpoints AUTHOR-API-EXPOSING deaktivieren
 */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( isset( $endpoints['/wp/v2/users'] ) ) {
		unset( $endpoints['/wp/v2/users'] );
	}
	if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}

	return $endpoints;
} );

/**
 * @param string $scssFile the File we want to watch
 * @param bool $is_import the watched file is a file that gets importet in scss via @import
 * @param string $fileToCombile the file that needs to be recombiled when the $scssFile file changes
 *
 * @throws SassException
 */

function check_for_recompile( string $scssFile, bool $is_import = false, string $fileToCombile = '' ): void {
	$css_file = __DIR__ . '/src/css/theme.min.css';
	$map_file = 'style.map';

	if ( ! file_exists( $scssFile ) ) {

		// show message for Administrators
		if ( function_exists( 'current_user_can' ) ) {
			if ( current_user_can( 'Administrator' ) ) {
				$style = 'position:fixed; top: 0; left: 0; right: 0; background: red; color: white; text-align: center; padding: 0.5rem;';
				echo '<div style="' . $style . '">' . $scssFile . ' - file not found in scss compiler' . '</div>';
			}
		}

		return;
	};


	if ( filemtime( $scssFile ) > filemtime( $css_file ) || filesize( $css_file ) == 0 ) {

		try {
			$wp_root_path = str_replace( '/wp-content/themes', '', get_theme_root() );
			$compiler     = new \ScssPhp\ScssPhp\Compiler();
			$compiler->setImportPaths( __DIR__ . '/scss' );
			$compiler->setOutputStyle( \ScssPhp\ScssPhp\OutputStyle::COMPRESSED );
			$compiler->setSourceMap( \ScssPhp\ScssPhp\Compiler::SOURCE_MAP_FILE );
			$compiler->setSourceMapOptions( [
				                                'sourceMapURL'      => get_stylesheet_directory_uri() . '/style.map',
				                                'sourceMapBasepath' => get_stylesheet_directory_uri(),//$wp_root_path
			                                ] );

			if ( true === $is_import ) {
				$scss_raw_string = file_get_contents( $fileToCombile );
			} else {
				$scss_raw_string = file_get_contents( $scssFile );
			}

			$result = $compiler->compileString( $scss_raw_string );

			if ( ! ! $result ) {
				file_put_contents( $map_file, $result->getSourceMap() );
				file_put_contents( $css_file, $result->getCss() );
			}

		} catch ( \Exception $e ) {
			// show message for Administrators
			if ( function_exists( 'current_user_can' ) ) {
				if ( current_user_can( 'administrator' ) ) {
					$style = 'position:fixed; top: 0; left: 0; right: 0; background: red; color: white; text-align: center; padding: 0.5rem; z-index: 999999999;';
					echo '<div style="' . $style . '">scssphp: Unable to compile content: ' . $e->getMessage() . '</div>';
				}
			}
		}
	}
}

/**
 * @throws SassException
 */
function juniper_theme_enqueue(): void {
	wp_enqueue_style( 'flowbite-js', get_template_directory_uri() . '/src/js/flowbite/flowbite.min.css', [], filemtime( __DIR__ . '/src/js/flowbite/flowbite.min.css' ) );
	wp_enqueue_script( 'flowbite-css', get_template_directory_uri() . '/src/js/flowbite/flowbite.min.js', [], '1.0', true );

	wp_enqueue_script( 'nav-js', get_template_directory_uri() . '/src/js/nav.js', [], filemtime( get_template_directory_uri() . '/src/js/nav.js'), true );
	wp_enqueue_script( 'project-js', get_template_directory_uri() . '/src/js/project.js', [], filemtime( get_template_directory_uri() . '/src/js/project.js'), true );

	$shop_url = rtrim( home_url(), '/' );
	wp_localize_script( 'project-js', 'scriptData', [ 'shopUrl' => $shop_url ] );

	wp_enqueue_style( 'tailwind-css', get_template_directory_uri() . '/src/css/_tailwindStyles.css', [], filemtime( get_template_directory_uri() . '/src/css/_tailwindStyles.css' ) );

	check_for_recompile( __DIR__ . '/src/scss/_project.scss', true, __DIR__ . '/src/scss/_project.scss' );

	wp_enqueue_style( 'font-css', get_template_directory_uri() . '/fonts.css', [], filemtime( get_template_directory_uri() . '/fonts.css'));
	wp_enqueue_style( 'tailwind-css', get_template_directory_uri() . '/_tailwind.css', [], filemtime( get_template_directory_uri() . '/_tailwind.css' ));
	wp_enqueue_style( 'theme-css', get_template_directory_uri() . '/src/css/theme.min.css', [], filemtime( get_template_directory_uri() . '/src/css/theme.min.css' ) );
	wp_enqueue_style( 'style-css', get_template_directory_uri() . '/style.css', [], filemtime( get_template_directory_uri() . '/style.css' ) );

	wp_enqueue_style( 'slick-css', get_template_directory_uri() . '/src/js/slick/slick.min.css', [], '1.8.1' );
	wp_enqueue_style( 'slick-theme-css', get_template_directory_uri() . '/src/js/slick/slick-theme.min.css', [], '1.8.1' );
	wp_enqueue_script( 'slick-js', get_template_directory_uri() . '/src/js/slick/slick.js', [ 'jquery' ], filemtime( __DIR__ . '/src/js/slick/slick.js' ), true );

	wp_enqueue_style( 'aos-css', get_template_directory_uri() . '/src/js/aos/aos.css', [], '3.0.0' );
	wp_enqueue_script( 'aos-js', get_template_directory_uri() . '/src/js/aos/aos.js', [], '3.0.0', true );
	wp_enqueue_script( 'aos-starter', get_template_directory_uri() . '/src/js/aos/aos-enqueue.js', [], '3.0.0', true );

	wp_enqueue_script( 'lottie-js', get_template_directory_uri() . '/src/js/lottie/lottie-player.js', [], 'latest', true );
	wp_enqueue_script( 'lottie-on-click', get_template_directory_uri() . '/src/js/lottie/onClickPlay.js', [ 'lottie-js' ], filemtime( __DIR__ . '/src/js/lottie/onClickPlay.js' ), true );

	wp_enqueue_style( 'swiper-css', get_template_directory_uri() . '/src/js/swiper/swiper-bundle.min.css', [], '1.0');
	wp_enqueue_script( 'swiper-js', get_template_directory_uri() . '/src/js/swiper/swiper-bundle.min.js', [], '1.0', true);

	wp_enqueue_script( 'woocommerce-cart-icon-counter-updater', get_template_directory_uri() . '/src/js/woocomemrce/cartCounterUpdater.js', [], 1, true);
}


add_action( 'wp_enqueue_scripts', 'juniper_theme_enqueue' );


/**
 * Required WordPress enqueue statements for child theme
 * Registers Parent theme and main child theme assets
 */
function enqueue_ls_scripts(): void {
	/** Deregister and register jquery to load in footer (enqueue happens per script dependency) */
	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, null, true );

	wp_enqueue_style(
		'old-theme-style',
		THEME_URI . 'ls-styles.css',
		[],
		filemtime( THEME_DIR . 'ls-styles.css' ),
	);

	wp_enqueue_style(
		'wps-styles',
		THEME_URI . 'assets/css/wps-styles.css',
		[],
		filemtime( THEME_DIR . 'assets/css/wps-styles.css' )
	);

	global $post;
	if ( has_shortcode( $post->post_content, 'facetwp' ) ) {
		wp_enqueue_style(
			'productfinder-style',
			THEME_URI . 'assets/css/productfinder.css',
			[],
			filemtime( THEME_DIR . 'assets/css/productfinder.css' ),
		);
	}

	// Load css and js for homepage slider
	if ( is_front_page() ) {
		wp_enqueue_style(
			'slider-homepage-style',
			THEME_URI . 'assets/css/slider-homepage.css',
			[],
			filemtime( THEME_DIR . 'assets/css/slider-homepage.css' ),
		);

		$asset_file = include THEME_DIR . 'assets/js/slider-homepage.asset.php';
		wp_register_script(
			'slider-homepage-script',
			THEME_URI . 'assets/js/slider-homepage.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
		wp_enqueue_script(
			'slider-homepage-script'
		);
	}

	$asset_file = include THEME_DIR . 'assets/js/sample-wishlist.asset.php';
	wp_register_script(
		'sample-wishlist-script',
		THEME_URI . 'assets/js/sample-wishlist.js',
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);
	global $post;
	wp_localize_script(
		'sample-wishlist-script', 'wpVars', [
		'postID' => $post->ID,
	],
	);
	wp_enqueue_script(
		'sample-wishlist-script'
	);

	wp_register_script(
		'wps-scripts',
		THEME_URI . 'assets/js/wps-scripts.js',
		'',
		filemtime( THEME_DIR . 'assets/js/wps-scripts.js' ),
		true
	);

	global $post;
	wp_localize_script(
		'wps-scripts', 'wpVars', [
		'postID'   => $post->ID,
		'postName' => $post->post_title
	],
	);
	wp_enqueue_script(
		'wps-scripts'
	);

	$translation_array = [
		'loading'                 => __( 'Loading...', 'wps-juniper' ), // 'Laden...
		'all_label'               => __( 'all', 'wps-juniper' ), // Alle
		'others_label'            => __( 'other', 'wps-juniper' ), // Andere
		'no_results'              => __( 'No results.', 'wps-juniper' ), // Keine Ergebnisse.
		'select_label'            => __( 'Select...', 'wps-juniper' ), // Wähle ...
		'open_filter'             => __( 'Open filter', 'wps-juniper' ), // Filter öffnen
		'metals-and-accessories'  => __( 'Metals and accessories', 'wps-juniper' ), // Metalle und Zubehör
		'color'                   => __( 'Colours', 'wps-juniper' ), // Farben
		'product_cat'             => __( 'Categories', 'wps-juniper' ), // Kategorien
		'checkbox'                => __( 'Sample available', 'wps-juniper' ), // Muster erhältlich
		'product_search'          => __( 'Search products...', 'wps-juniper' ), // Suche Produkte
		'load_more'               => __( 'load more', 'wps-juniper' ), // mehr laden
		'choose'                  => __( 'select', 'wps-juniper' ), // Wähle
		'results_label'           => __( 'Products found', 'wps-juniper' ), // Produkte gefunden
		'filter_delete_button'    => __( 'Reset filter', 'wps-juniper' ), // Alle Filter zurücksetzten
		'filter_sample_available' => __( 'Sample available', 'wps-juniper' ), // Muster verfügbar
		'filter_online_available' => __( 'Online available', 'wps-juniper' ), // Online verfügbar
	];

	wp_localize_script( 'filter-js', 'translation', $translation_array );
}

\add_action( 'wp_enqueue_scripts', '\enqueue_ls_scripts' );


/**
 * This ensures that Timber is loaded and available as a PHP class.
 * If not, it gives an error message to help direct developers on where to activate
 */
if ( ! class_exists( 'Timber' ) ) {

	\add_action(
		'admin_notices',
		function () {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		}
	);

	add_filter(
		'template_include',
		function ( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);

	return;
}

/**
 * Sets the directories (inside your theme) to find .twig files
 */
\Timber::$dirname = [ 'templates', 'views' ];

/**
 * By default, Timber does NOT autoescape values. Want to enable Twig's autoescape?
 * No prob! Just set this value to true
 */
\Timber::$autoescape = false;


//StarterSite class
require_once 'class-startersite.php';
$site = new \StarterSite();

\add_theme_support( 'custom-logo' );

function juniper_customizer_setting( $wp_customize ) {
	$wp_customize->add_setting( 'footer_logo' );
	$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize, 'footer_logo', [
		'label'    => 'Upload Footer Logo',
		'section'  => 'title_tagline', //this is the section where the custom-logo from WordPress is
		'settings' => 'footer_logo',
		'priority' => 8 // show it just below the custom-logo
	] ) );

	$wp_customize->add_setting( 'juniper_footer_textarea', [
		'capability'        => 'edit_theme_options',
		'default'           => 'Lorem Ipsum Dolor Sit amet',
		'sanitize_callback' => 'sanitize_textarea_field',
	] );

	$wp_customize->add_control( 'juniper_footer_textarea', [
		'type'        => 'textarea',
		'section'     => 'title_tagline', // // Add a default or your own section
		'label'       => __( 'Footer Quote' ),
		'description' => __( 'Enter footer quote.' ),
	] );
}

\add_action( 'customize_register', 'juniper_customizer_setting' );

function wps_juniper_register_nav_menu() {
	\register_nav_menus( [
		                     'primary_menu_de'   => __( 'Primary Menu DE', 'wps_juniper' ),
		                     'secondary_menu_de' => __( 'Secondary Menu DE', 'wps_juniper' ),
		                     'footer_menu_de'    => __( 'Footer Menu DE', 'wps_juniper' ),
		                     'primary_menu_en'   => __( 'Primary Menu EN', 'wps_juniper' ),
		                     'secondary_menu_en' => __( 'Secondary Menu EN', 'wps_juniper' ),
		                     'footer_menu_en'    => __( 'Footer Menu EN', 'wps_juniper' ),
	                     ] );
}

\add_action( 'after_setup_theme', 'wps_juniper_register_nav_menu', 0 );

add_filter( 'timber/context', 'wps_add_to_context' );
function wps_add_to_context( $context ) {
	$custom_logo_id              = \get_theme_mod( 'custom_logo' );
	$logo                        = \wp_get_attachment_image_url( $custom_logo_id, 'full' );
	$context['logo']             = $logo;
	$footer_logo                 = \get_theme_mod( 'footer_logo' );
	$context['footer_logo']      = $footer_logo;
	$footer_quote                = \get_theme_mod( 'juniper_footer_textarea' );
	$context['footer_quote']     = $footer_quote;
	$upload_dir                  = \wp_upload_dir();
	$context['uploads']          = $upload_dir;
	$context['theme_dir']        = \get_stylesheet_directory_uri();
	$languages                   = \apply_filters( 'wpml_active_languages', null, [ 'skip_missing' => 0 ] );
	$current_language            = \apply_filters( 'wpml_current_language', null );
	$context['languages']        = $languages;
	$context['current_language'] = $current_language;

	//$context['primary_menu']        = new \Timber\Menu( "primary_menu_$current_language" );
	//$context['secondary_menu']      = new \Timber\Menu( "primary_menu_$current_language" );

	// use the german menu and translate it with wpml navigation syncronization
	$context['primary_menu']   = new \Timber\Menu( "primary_menu_de" );
	$context['secondary_menu'] = new \Timber\Menu( "primary_menu_de" );

	//$context['footer_menu']         = new \Timber\Menu( "footer_menu_$current_language" );
	$context['footer_menu'] = new \Timber\Menu( "footer_menu_de" );

	$context['title']              = \get_the_title();
	$context['jumbotron_bg_image'] = \get_stylesheet_directory_uri() . '/assets/img/default_bg_image.png';
	$context['home_page_url']      = \home_url();
	$context['page_title']         = \get_the_title();
	$home_page_url                 = \home_url();
	$context['home_page_url']      = $home_page_url;
	$context['shop_url']           = \get_permalink( \wc_get_page_id( 'shop' ) );
	$context['products_url']       = get_permalink( get_page_by_path( 'productfinder' ) );
	$context['account_url']        = \wc_get_page_permalink( 'myaccount' );
	$context['cart_url']           = \wc_get_cart_url();
	$context['parent_page_title']  = '';
	$context['parent_page_url']    = '';

	$context['page_banner'] = __('Discover our new Galvano Online Shop', 'wps-juniper');
	$context['products_products_button_label'] = __('Products', 'wps-juniper');

	if ( WC()->cart ) {
		$context['cart_count'] = \WC()->cart->get_cart_contents_count();
	}


	if ( \is_single() ) {
		$post_type = \get_post_type();
		$page      = \get_field( $post_type . '_archive_page', 'option' );
		if ( $page ) {
			$context['parent_page_title'] = $page->post_title;
			$context['parent_page_url']   = \get_permalink( $page );
		}

		if ( $post_type === "post" ) {
			$post = \get_post();
			if ( $post_thumbnail = \get_the_post_thumbnail_url( $post, 'full' ) ) {
				$context['jumbotron_bg_image'] = $post_thumbnail;
			}
		}

		if ( $post_type === "jobs" ) {
			$context['single_job_content'] = do_shortcode( '[single-job-content]' );
		}
	}

	if ( \is_product() ) {
		$context['parent_page_title'] = 'Produkte';
		$context['parent_page_url']   = \get_permalink( \wc_get_page_id( 'shop' ) );
	}

	return $context;
}

\add_action( 'wp_enqueue_scripts', 'wpse_enqueues' );
function wpse_enqueues() {
	// Only enqueue on specified single CPTs
	if ( \is_singular() ) {
		$refresh_cache_time = time();
		\wp_enqueue_style( 'wps-jumbotron-css', get_stylesheet_directory_uri() . '/blocks/jumbotron/style.css', [], $refresh_cache_time );
	}
}

// custom wps functionality from classes

require_once __DIR__ . '/classes/MailPoetGF.php';

use frontend\Modal;
use frontend\ModalStatus;
use MailPoetGF;
use ScssPhp\ScssPhp\Exception\SassException;

// define in init so plugin functions are available in this class
//\add_action( 'init', [ MailPoetGF::get_instance(), 'init' ] );


add_filter( 'render_block', 'wps_juniper_add_class_to_list_block', 10, 2 );
function wps_juniper_add_class_to_list_block( $block_content, $block ) {
	if ( 'core/group' === $block['blockName'] ) {
		$block_content = new \WP_HTML_Tag_Processor( $block_content );
		$block_content->next_tag( 'div' );
		//$block_content->add_class( 'container' );
		$block_content->add_class( 'wps-content' );
		$block_content->get_updated_html();
	}

	return $block_content;
}

add_filter( 'acf/settings/remove_wp_meta_box', '__return_false' );
function wps_juniper_acf_init() {

	\acf_update_setting( 'google_api_key', 'AIzaSyA2nwpgRNcXh27RBL41e47d6pFcJda9qiY' );
}

\add_action( 'acf/init', 'wps_juniper_acf_init' );

/**
 * Change the excerpt more string
 */
function wps_juniper_excerpt_more( $more ) {
	return ' [...]';
}

add_filter( 'excerpt_more', 'wps_juniper_excerpt_more' );


add_filter( 'locale', 'change_gravity_forms_language' );
function change_gravity_forms_language( $locale ) {

	if ( \class_exists( 'RGForms' ) ) {
		return 'de_DE';
	}

	return $locale;
}

// Enable SVG uploads
function allow_svg_upload( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}

add_filter( 'upload_mimes', 'allow_svg_upload' );

function allow_json_mime_types( $mimes ) {
	// New allowed mime types.
	$mimes['json'] = 'application/json';

	return $mimes;
}

add_filter( 'upload_mimes', 'allow_json_mime_types' );

// Additional security measures for SVG uploads
function validate_svg_upload( $file, $filename, $mimes ) {
	if ( str_ends_with( $filename, '.svg' ) && $file['type'] === 'image/svg+xml' ) {
		$file['type'] = 'image/svg+xml';
	}

	return $file;
}

add_filter( 'wp_check_filetype_and_ext', 'validate_svg_upload', 10, 4 );

// Add woocommerce support
function theme_add_woocommerce_support() {
	\add_theme_support( 'woocommerce' );
}

\add_action( 'after_setup_theme', 'theme_add_woocommerce_support' );

require_once THEME_DIR . 'ls-blocks/class-gutenberg-blocks.php';
require_once THEME_DIR . 'shortcodes/index.php';
require_once THEME_DIR . 'api/metalprices.php';
require_once THEME_DIR . 'api/samples.php';
require_once THEME_DIR . 'inc/job/job-detailpage.php';
require_once THEME_DIR . 'inc/contact/contact-button.php';
require_once THEME_DIR . 'inc/cover/cover-slider.php';
require_once THEME_DIR . 'inc/news/news-acf.php';
require_once THEME_DIR . 'inc/news/news-detailpage.php';
require_once THEME_DIR . 'inc/admin/capabilities.php';

// mrx create taxonomies and import fieldgroups
( function () {

	// taxonomies
	require_once THEME_DIR . 'taxonomies/product/Application.php';
	require_once THEME_DIR . 'taxonomies/product/Color.php';
	require_once THEME_DIR . 'taxonomies/product/Anwendung.php';
	new \IWG\Taxonomies\Product\Application();
	new \IWG\Taxonomies\Product\Color();
	new \IWG\Taxonomies\Product\Anwendung();

	// fieldgroups
	include THEME_DIR . 'fieldgroups/product-group.php';
	include THEME_DIR . 'fieldgroups/product_cat_group.php';

} )();

// handle product request modal

add_action( 'init', function () {
	require_once __DIR__ . '/classes/frontend/Modal.php';

	$modal                    = new \frontend\Modal();
	$modal->id                = 'product-request-modal';
	$modal->view              = 'productRequestModal.twig';
	$modal->title             = __( 'Product enquiry', 'wps-modal' ); // Produktanfrage
	$modal->content           = '';
	$modal->variables['form'] = '';
	$modal->showSubmitButton  = false;
	$modal->showCloseButton   = false;
	$modal->close()->render();

} );

add_filter( 'wps_modal_render', function ( $modal ) {

	if ( $modal->id === 'product-request-modal' ) {
		global $product;

		$productName = '';
		if ( isset( $product ) && ! ! $product ) {
			if ( ! ! $product->get_title() && $product->get_title() !== '' ) {
				$productName = $product->get_title();
			}
		}

		$formID = 1;
		if ( $modal->language === 'en' ) {
			$formID = 8;
		}

		$modal->content = do_shortcode( "[gravityform id='{$formID}' field_values='productName={$productName}' title='false' description='false' ajax='true']" );

		return $modal;
	}

	return $modal;
} );

// max number of musterbestellungen in Box
add_action( 'init', function () {
	require_once __DIR__ . '/classes/frontend/Modal.php';

	$modal                    = new \frontend\Modal();
	$modal->id                = 'full-samplebox-modal';
	$modal->title             = __( 'Unfortunately the SampleBox is full.', 'wps-modal' ); // Die SampleBox ist leider voll.
	$modal->content           = __( 'All available spaces in the sample box are occupied. If you want to add another pattern, you have to manually clear a space using the trash can icon.', 'wps-modal' ); // Alle verfügbaren Plätze der Musterbox sind belegt. Wenn Sie ein weiteres Muster hinzufügen möchten, müssen Sie manuell einen Platz freimachen mithilfe des Mistkübel Icons.
	$modal->variables['form'] = '';
	$modal->showSubmitButton  = false;
	$modal->showCloseButton   = true;
	$modal->close()->render();
} );

// Woocommerce related hooks
require_once __DIR__ . '/classes/frontend/WC_Customizations.php';
$woocommerce = new frontend\WC_Customizations();


// Product Card related hooks
require_once __DIR__ . '/classes/frontend/ProductCard.php';
$productCard = new frontend\ProductCard();


// musterbestellung related code
require_once __DIR__ . '/classes/frontend/Musterbestellung.php';


// disable fullscreen mode in gutenberg by default
add_action( 'enqueue_block_editor_assets', function () {
	$script = "window.onload = function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } }";
	wp_add_inline_script( 'wp-blocks', $script );
} );

// product-single-page-picture-size
add_action( 'after_setup_theme', function () {
	add_image_size( 'product-single-page-picture-size', 1024, 1024, true );
} );

// add company & UID field to the registration process
add_action( 'woocommerce_register_form_start', function () {
	?>
	<p class="form-row form-row-wide">
		<label for="billing_company"><?php _e( 'Firma', 'woocommerce' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_company" id="billing_company"
			   value="<?php if ( ! empty( $_POST['billing_company'] ) ) {
			       esc_attr_e( $_POST['billing_company'] );
		       } ?>"/>
	</p>
	<p class="form-row form-row-wide">
		<label for="billing_company"><?php _e( 'UID', 'woocommerce' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_vat_id" id="billing_vat_id"
			   value="<?php if ( ! empty( $_POST['billing_vat_id'] ) ) {
			       esc_attr_e( $_POST['billing_vat_id'] );
		       } ?>"/>
	</p>
	<?php
},          999 );

// Validate Company & UID field during registration
add_filter( 'woocommerce_registration_errors', function ( $errors, $username, $email ) {

	if ( isset( $_POST['billing_company'] ) && empty( $_POST['billing_company'] ) ) {
		$errors->add( 'billing_company_error', __( 'Das Feld Firma ist ein Pflichtfeld', 'woocommerce' ) );
	}

	if ( isset( $_POST['billing_vat_id'] ) && empty( $_POST['billing_vat_id'] ) ) {
		$errors->add( 'billing_vat_id_error', __( 'Das Feld UID ist ein Pflichtfeld', 'woocommerce' ) );
	}

	return $errors;
},          999, 3 );

// Save Company & UID field during registration
add_action( 'woocommerce_created_customer', function ( $customer_id ) {
	if ( isset( $_POST['billing_company'] ) ) {
		update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );
	}

	if ( isset( $_POST['billing_vat_id'] ) ) {
		update_user_meta( $customer_id, 'billing_vat_id', sanitize_text_field( $_POST['billing_vat_id'] ) );
	}
} );

// shortcode to render buttons like on the 404 Seite
add_action( 'init', function () {
	add_shortcode( 'wps-orientation-buttons', function () {

		$site_url = '/';
		$shop_url = get_permalink( wc_get_page_id( 'shop' ) );

		$site_caption = __( 'Back to the Homepage', 'wps-juniper' ); // Zurück zur Startseite
		$shop_caption = __( 'Discover our online shop', 'wps-juniper' ); // Online Shop entdecken

		ob_start();
		?>

		<div class="flex flex-row gap-8 flex-wrap justify-center mb-16">
			<a href="<?php echo $site_url; ?>" class="btn btn-black text-white"> <?php echo $site_caption; ?></a>
			<a href="<?php echo $shop_url; ?>" class="btn btn-accent add-to-musterbestellung">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none">
					<path d="M7.20484 16.5967C6.51445 16.5967 5.95801 17.1531 5.95801 17.8435C5.95801 18.5339 6.52475 19.0903 7.20484 19.0903C7.89523 19.0903 8.45167 18.5236 8.45167 17.8435C8.45167 17.1531 7.89523 16.5967 7.20484 16.5967ZM7.20484 18.3793C6.90601 18.3793 6.66901 18.1423 6.66901 17.8435C6.66901 17.5447 6.90601 17.3077 7.20484 17.3077C7.50367 17.3077 7.74067 17.5447 7.74067 17.8435C7.74067 18.1423 7.50367 18.3793 7.20484 18.3793Z"
						  fill="black"/>
					<path d="M20.8583 11.1251L20.1782 10.1771C20.0648 10.0122 19.8382 9.98132 19.6836 10.0947C19.5187 10.208 19.4878 10.4347 19.6012 10.5893L20.2812 11.5373C20.3018 11.5682 20.3018 11.6197 20.2606 11.6403L18.0761 13.2169L15.2733 9.29093L15.5309 8.78602L17.3033 7.51858C17.3342 7.48766 17.3857 7.49797 17.4063 7.53918L17.9318 8.2811C18.0452 8.44597 18.2719 8.47688 18.4265 8.36353C18.5913 8.25019 18.6222 8.0338 18.5089 7.86893L17.9834 7.12701C17.7361 6.77666 17.2414 6.69423 16.8808 6.94153L16.2316 7.39492L16.5923 6.69422C16.685 6.50874 16.7056 6.29235 16.6438 6.09657C16.582 5.90079 16.448 5.73592 16.2522 5.63287L11.8729 3.37621C11.6874 3.28347 11.471 3.26286 11.2752 3.32469C11.0794 3.38652 10.9146 3.53078 10.8115 3.71626L10.4509 4.41695V3.62352C10.4509 3.19073 10.0902 2.83008 9.65741 2.83008H4.73192C4.29913 2.83008 3.93848 3.18043 3.93848 3.62352V17.8436C3.93848 19.6365 5.4017 21.0998 7.19466 21.0998C7.89536 21.0998 8.55484 20.8731 9.09067 20.4918C9.10098 20.4815 9.11128 20.4815 9.12159 20.4712L12.5426 18.0291L15.366 16.0094L20.6625 12.2277C21.0232 11.9804 21.1056 11.4858 20.8583 11.1251ZM17.4888 13.6291L15.2424 15.2263L13.4803 12.7532L14.9023 9.99163L17.4888 13.6291ZM12.419 17.2459L11.6977 16.2361L13.1197 13.4745L14.6757 15.6487L12.419 17.2459ZM11.5637 14.9171L10.4612 14.3504V11.2488L12.8312 12.4647L12.7487 12.6192L12.7384 12.6296L11.5637 14.9171ZM13.1609 11.8361L10.4612 10.445V7.3434L14.418 9.38367L13.1609 11.8361ZM9.75015 9.77524H4.67009V7.01366H9.75015V9.77524ZM4.65978 10.4965H9.73985V13.2581H4.65978V10.4965ZM10.4612 15.1541L11.2443 15.556L10.4612 17.0707V15.1541ZM11.3267 16.9471L11.8316 17.6581L10.4509 18.6473L11.3267 16.9471ZM11.4504 4.03569C11.4607 4.01508 11.4813 4.00478 11.5019 3.99447C11.5122 3.98417 11.5328 3.98417 11.5637 4.00478L15.9431 6.26144C15.9637 6.27174 15.974 6.29235 15.9843 6.30266C15.9946 6.32327 15.9946 6.34388 15.974 6.36448L14.7478 8.7448L10.4612 6.53966V5.97292L11.4504 4.03569ZM4.74222 3.54108H9.66772C9.70894 3.54108 9.75015 3.57199 9.75015 3.61321V6.29235H4.67009V3.61321C4.65978 3.5823 4.6907 3.54108 4.74222 3.54108ZM7.20497 20.3888C5.80357 20.3888 4.65978 19.245 4.65978 17.8436V13.9691H9.73985V17.8436C9.75015 19.245 8.60637 20.3888 7.20497 20.3888Z"
						  fill="black"/>
					<path d="M19.0657 9.58967C19.2615 9.58967 19.4161 9.42479 19.4161 9.23932C19.4161 9.04353 19.2512 8.87866 19.0657 8.87866C18.8699 8.87866 18.7051 9.04353 18.7051 9.23932C18.7051 9.42479 18.8596 9.58967 19.0657 9.58967Z"
						  fill="black"/>
				</svg> <?php echo $shop_caption; ?>
			</a>
		</div>
		<?php
		return ob_get_clean();
	} );
} );

add_filter( 'woocommerce_checkout_fields', function ( $fields ) {

	if ( isset( $fields['billing']['billing_company'] ) ) {
		$fields['billing']['billing_company']['required'] = true;
	}

	return $fields;

} );

add_action( 'woocommerce_checkout_billing', function () {
	echo '<div class="my-8">' . __( '*) Required', 'wps-juniper' ) . '</div>';
},          9999 );

add_filter( 'woocommerce_get_checkout_page_id', function ( $page_id ) {
	return apply_filters( 'wpml_object_id', $page_id, 'page' );
} );

add_filter( 'wps_get_attachment_id_with_name_like', function ( $attachment_name ) {
	global $wpdb;

	$attachment_name = "%$attachment_name%";

	$query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_title LIKE %s", $attachment_name );

	return $wpdb->get_var( $query ) ?? 0;
} );

add_filter( 'woocommerce_format_sale_price', function ( $price, $regular_price, $sale_price ) {
	$regular_price = wc_price( $regular_price);
	$sale_price = wc_price($sale_price);

	return sprintf('<div class="flex flex-row"><span class="regular-price line-through pr-2 text-[#737373]">%s</span><span class="sale-price">%s</span></div>', $regular_price, $sale_price);
},          10, 3 );
