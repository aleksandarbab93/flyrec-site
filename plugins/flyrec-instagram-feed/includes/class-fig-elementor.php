<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor widget — registruje se samo ako je Elementor aktivan, tako da
 * plugin nema tvrdu zavisnost od njega.
 */
class Fig_Elementor {

    public static function init() {
        add_action( 'elementor/widgets/register', [ __CLASS__, 'register_widget' ] );
    }

    public static function register_widget( $widgets_manager ) {
        if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
            return;
        }

        require_once FIG_DIR . 'includes/class-fig-elementor-widget.php';
        $widgets_manager->register( new Fig_Elementor_Widget() );
    }
}
