/**
 * Flyrec Instagram Feed – Gutenberg blok (editor strana).
 * Čist ES5 + wp.element.createElement, bez JSX/webpack build koraka.
 * Preview u editoru koristi ServerSideRender — isti PHP render_callback
 * kao na frontend-u, tako da nema duplirane markup logike.
 */
( function ( blocks, element, blockEditor, components, i18n, serverSideRender ) {
    'use strict';

    var el = element.createElement;
    var __ = i18n.__;
    var ServerSideRender = serverSideRender;

    blocks.registerBlockType( 'flyrec/instagram-feed', {
        title: __( 'Flyrec Instagram Feed', 'flyrec-instagram-feed' ),
        description: __( 'Prikazuje najnovije Instagram objave (Reels, video, foto, carousel) sa povezanog naloga.', 'flyrec-instagram-feed' ),
        icon: 'instagram',
        category: 'widgets',
        attributes: {
            limit: { type: 'number', default: 12 },
            columns: { type: 'number', default: 4 },
            type: { type: 'string', default: 'all' },
            clickAction: { type: 'string', default: 'lightbox' },
        },

        edit: function ( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            var inspector = el(
                blockEditor.InspectorControls,
                {},
                el(
                    components.PanelBody,
                    { title: __( 'Podešavanja feed-a', 'flyrec-instagram-feed' ) },
                    el( components.RangeControl, {
                        label: __( 'Broj objava', 'flyrec-instagram-feed' ),
                        value: attributes.limit,
                        min: 1,
                        max: 50,
                        onChange: function ( value ) { setAttributes( { limit: value } ); },
                    } ),
                    el( components.RangeControl, {
                        label: __( 'Broj kolona', 'flyrec-instagram-feed' ),
                        value: attributes.columns,
                        min: 1,
                        max: 6,
                        onChange: function ( value ) { setAttributes( { columns: value } ); },
                    } ),
                    el( components.SelectControl, {
                        label: __( 'Tip sadržaja', 'flyrec-instagram-feed' ),
                        value: attributes.type,
                        options: [
                            { label: __( 'Sve', 'flyrec-instagram-feed' ), value: 'all' },
                            { label: __( 'Reels', 'flyrec-instagram-feed' ), value: 'reels' },
                            { label: __( 'Video', 'flyrec-instagram-feed' ), value: 'video' },
                            { label: __( 'Fotografije', 'flyrec-instagram-feed' ), value: 'image' },
                            { label: __( 'Carousel', 'flyrec-instagram-feed' ), value: 'carousel' },
                        ],
                        onChange: function ( value ) { setAttributes( { type: value } ); },
                    } ),
                    el( components.SelectControl, {
                        label: __( 'Klik na objavu', 'flyrec-instagram-feed' ),
                        value: attributes.clickAction,
                        options: [
                            { label: __( 'Popup/lightbox', 'flyrec-instagram-feed' ), value: 'lightbox' },
                            { label: __( 'Popup sa Instagram embed-om', 'flyrec-instagram-feed' ), value: 'embed' },
                            { label: __( 'Direktan link ka Instagramu', 'flyrec-instagram-feed' ), value: 'instagram' },
                        ],
                        onChange: function ( value ) { setAttributes( { clickAction: value } ); },
                    } )
                )
            );

            var preview = el( ServerSideRender, {
                block: 'flyrec/instagram-feed',
                attributes: attributes,
            } );

            return el( element.Fragment, {}, inspector, preview );
        },

        save: function () {
            return null; // Dinamički blok — sav markup dolazi iz render_callback na PHP strani.
        },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n,
    window.wp.serverSideRender
);
