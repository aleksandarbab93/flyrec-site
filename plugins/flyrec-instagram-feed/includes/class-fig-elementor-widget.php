<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Elementor widget "Flyrec Instagram Feed" — tanak omotač oko iste
 * Fig_Shortcode::render() funkcije koju koriste shortcode i Gutenberg blok.
 */
class Fig_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'flyrec-instagram-feed';
    }

    public function get_title() {
        return __( 'Flyrec Instagram Feed', 'flyrec-instagram-feed' );
    }

    public function get_icon() {
        return 'eicon-instagram-post';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section( 'fig_section_content', [
            'label' => __( 'Podešavanja', 'flyrec-instagram-feed' ),
        ] );

        $this->add_control( 'limit', [
            'label'   => __( 'Broj objava', 'flyrec-instagram-feed' ),
            'type'    => Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 50,
            'default' => 12,
        ] );

        $this->add_control( 'columns', [
            'label'   => __( 'Broj kolona', 'flyrec-instagram-feed' ),
            'type'    => Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 6,
            'default' => 4,
        ] );

        $this->add_control( 'type', [
            'label'   => __( 'Tip sadržaja', 'flyrec-instagram-feed' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'all',
            'options' => [
                'all'      => __( 'Sve', 'flyrec-instagram-feed' ),
                'reels'    => __( 'Reels', 'flyrec-instagram-feed' ),
                'video'    => __( 'Video', 'flyrec-instagram-feed' ),
                'image'    => __( 'Fotografije', 'flyrec-instagram-feed' ),
                'carousel' => __( 'Carousel', 'flyrec-instagram-feed' ),
            ],
        ] );

        $this->add_control( 'click_action', [
            'label'   => __( 'Klik na objavu', 'flyrec-instagram-feed' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'lightbox',
            'options' => [
                'lightbox'  => __( 'Popup/lightbox', 'flyrec-instagram-feed' ),
                'embed'     => __( 'Popup sa Instagram embed-om', 'flyrec-instagram-feed' ),
                'instagram' => __( 'Direktan link ka Instagramu', 'flyrec-instagram-feed' ),
            ],
        ] );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        echo Fig_Shortcode::render( [
            'limit'        => $settings['limit'] ?? 12,
            'columns'      => $settings['columns'] ?? 4,
            'type'         => $settings['type'] ?? 'all',
            'click_action' => $settings['click_action'] ?? 'lightbox',
        ] ); // phpcs:ignore WordPress.Security.EscapeOutput -- render() already escapes internally.
    }
}
