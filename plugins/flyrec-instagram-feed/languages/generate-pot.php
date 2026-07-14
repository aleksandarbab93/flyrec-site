<?php
/**
 * POT file generator za Flyrec Instagram Feed plugin
 * Pokrenuti jednom iz terminala: php generate-pot.php
 * Generiše flyrec-instagram-feed.pot (php + js stringovi) za prevod u Poedit-u
 */

$plugin_dir = dirname( __DIR__ );
$pot_file   = __DIR__ . '/flyrec-instagram-feed.pot';
$domain     = 'flyrec-instagram-feed';

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator( $plugin_dir )
);

$strings = [];

function fig_add_string( &$strings, $string, $relative, $line ) {
    if ( ! isset( $strings[ $string ] ) ) {
        $strings[ $string ] = [];
    }
    $strings[ $string ][] = $relative . ':' . $line;
}

foreach ( $files as $file ) {
    $ext = $file->getExtension();
    if ( ! in_array( $ext, [ 'php', 'js' ], true ) ) continue;
    if ( strpos( $file->getPathname(), '/languages/' ) !== false ) continue;
    if ( strpos( $file->getPathname(), '/node_modules/' ) !== false ) continue;

    $content  = file_get_contents( $file->getPathname() );
    $relative = str_replace( $plugin_dir . '/', '', $file->getPathname() );

    $patterns = [
        '/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*\'((?:[^\'\\\\]|\\\\.)*)\'\s*,\s*\'' . preg_quote( $domain, '/' ) . '\'\s*\)/',
        '/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*"((?:[^"\\\\]|\\\\.)*)"\s*,\s*"' . preg_quote( $domain, '/' ) . '"\s*\)/',
    ];

    foreach ( $patterns as $pattern ) {
        preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $match ) {
            $string = $match[0];
            $offset = $match[1];
            $line   = substr_count( substr( $content, 0, $offset ), "\n" ) + 1;
            fig_add_string( $strings, $string, $relative, $line );
        }
    }
}

// Plugin header stringovi (Plugin Name / Description) - WP prevodi ove preko istog text domaina.
fig_add_string( $strings, 'Flyrec Instagram Feed', 'flyrec-instagram-feed.php', 3 );
fig_add_string( $strings, 'Automatski prikazuje najnovije Instagram objave (Reels, video, foto, carousel) sa poslovnog Flyrec Instagram naloga na sajtu, preko zvaničnog Instagram Graph API-ja. Bez scrapinga.', 'flyrec-instagram-feed.php', 5 );

ksort( $strings );

$pot  = "# Flyrec Instagram Feed Plugin\n";
$pot .= "# Copyright (C) " . date('Y') . " Flyrec\n";
$pot .= "# This file is distributed under the same license as the Flyrec Instagram Feed plugin.\n";
$pot .= "#\n";
$pot .= "msgid \"\"\n";
$pot .= "msgstr \"\"\n";
$pot .= "\"Project-Id-Version: Flyrec Instagram Feed 1.0.0\\n\"\n";
$pot .= "\"Report-Msgid-Bugs-To: info@flyrec.rs\\n\"\n";
$pot .= "\"POT-Creation-Date: " . date('Y-m-d H:i') . "+0000\\n\"\n";
$pot .= "\"MIME-Version: 1.0\\n\"\n";
$pot .= "\"Content-Type: text/plain; charset=UTF-8\\n\"\n";
$pot .= "\"Content-Transfer-Encoding: 8bit\\n\"\n";
$pot .= "\"Language: sr_RS\\n\"\n";
$pot .= "\"X-Generator: Flyrec POT Generator\\n\"\n\n";

foreach ( $strings as $string => $locations ) {
    foreach ( $locations as $loc ) {
        $pot .= "#: " . $loc . "\n";
    }
    $escaped = str_replace( '"', '\\"', $string );
    $pot .= "msgid \"" . $escaped . "\"\n";
    $pot .= "msgstr \"\"\n\n";
}

file_put_contents( $pot_file, $pot );
echo "✅ Generisano: flyrec-instagram-feed.pot (" . count( $strings ) . " stringova)\n";
