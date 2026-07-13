<?php
/**
 * Plugin Name:       Flyrec Instagram Feed
 * Plugin URI:        https://flyrec.rs
 * Description:       Automatski prikazuje najnovije Instagram objave (Reels, video, foto, carousel) sa poslovnog Flyrec Instagram naloga na sajtu, preko zvaničnog Instagram Graph API-ja. Bez scrapinga.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Flyrec
 * Text Domain:       flyrec-instagram-feed
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Direktan pristup fajlu nije dozvoljen.
}

// =============================================
// KONSTANTE
// =============================================
define( 'FIG_VERSION', '1.0.0' );
define( 'FIG_FILE', __FILE__ );
define( 'FIG_DIR', plugin_dir_path( __FILE__ ) );
define( 'FIG_URL', plugin_dir_url( __FILE__ ) );
define( 'FIG_BASENAME', plugin_basename( __FILE__ ) );

// Verzija Instagram Graph API-ja koju plugin koristi. Meta redovno gasi starije
// verzije (obično posle ~2 godine) — ako sinhronizacija počne da vraća grešku
// tipa "Unsupported get request" ili "API version no longer supported",
// ovo je prvo mesto koje treba proveriti/podići.
define( 'FIG_API_VERSION', 'v21.0' );
define( 'FIG_GRAPH_HOST', 'https://graph.instagram.com' );
define( 'FIG_FB_GRAPH_HOST', 'https://graph.facebook.com' );

// Minimalni broj dana pre isteka tokena kada plugin pokušava automatski refresh.
define( 'FIG_TOKEN_REFRESH_THRESHOLD_DAYS', 10 );

// =============================================
// UČITAVANJE KLASA
// =============================================
require_once FIG_DIR . 'includes/class-fig-helpers.php';
require_once FIG_DIR . 'includes/class-fig-token-manager.php';
require_once FIG_DIR . 'includes/class-fig-api-client.php';
require_once FIG_DIR . 'includes/class-fig-cpt.php';
require_once FIG_DIR . 'includes/class-fig-sync.php';
require_once FIG_DIR . 'includes/class-fig-cron.php';
require_once FIG_DIR . 'includes/class-fig-ajax.php';
require_once FIG_DIR . 'includes/class-fig-admin-settings.php';
require_once FIG_DIR . 'includes/class-fig-shortcode.php';
require_once FIG_DIR . 'includes/class-fig-block.php';
require_once FIG_DIR . 'includes/class-fig-elementor.php';

/**
 * Glavna bootstrap klasa — kači sve pod-module na WordPress hook-ove.
 */
final class Fig_Plugin {

    /** @var Fig_Plugin|null */
    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
        add_action( 'init', [ Fig_CPT::class, 'register' ] );

        Fig_Cron::init();
        Fig_Ajax::init();
        Fig_Admin_Settings::init();
        Fig_Shortcode::init();
        Fig_Block::init();
        Fig_Elementor::init();
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'flyrec-instagram-feed', false, dirname( FIG_BASENAME ) . '/languages' );
    }
}

Fig_Plugin::instance();

// =============================================
// AKTIVACIJA / DEAKTIVACIJA
// =============================================
register_activation_hook( FIG_FILE, [ 'Fig_Cron', 'activate' ] );
register_deactivation_hook( FIG_FILE, [ 'Fig_Cron', 'deactivate' ] );
