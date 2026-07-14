<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gutenberg blok "Flyrec Instagram Feed" — klasičan dynamic block (PHP
 * render_callback, JS samo za editor prikaz preko ServerSideRender).
 *
 * Namerno bez build koraka (webpack/JSX) — block.js je čist ES5 sa
 * wp.element.createElement, tako da plugin radi odmah iz ZIP-a bez `npm
 * run build`.
 */
class Fig_Block {

    const NAME = 'flyrec/instagram-feed';

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_block' ] );
    }

    public static function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return; // Starija WP verzija bez block editor podrške.
        }

        wp_register_script(
            'fig-block-editor',
            FIG_URL . 'blocks/instagram-feed/block.js',
            [ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ],
            FIG_VERSION,
            true
        );
        wp_set_script_translations( 'fig-block-editor', 'flyrec-instagram-feed', FIG_DIR . 'languages' );

        register_block_type( self::NAME, [
            'editor_script'   => 'fig-block-editor',
            'render_callback' => [ __CLASS__, 'render' ],
            'attributes'      => [
                'limit'       => [ 'type' => 'number', 'default' => 12 ],
                'columns'     => [ 'type' => 'number', 'default' => 4 ],
                'type'        => [ 'type' => 'string', 'default' => 'all' ],
                'clickAction' => [ 'type' => 'string', 'default' => 'lightbox' ],
            ],
        ] );
    }

    public static function render( $attributes ) {
        return Fig_Shortcode::render( [
            'limit'        => $attributes['limit'] ?? 12,
            'columns'      => $attributes['columns'] ?? 4,
            'type'         => $attributes['type'] ?? 'all',
            'click_action' => $attributes['clickAction'] ?? 'lightbox',
        ] );
    }
}
