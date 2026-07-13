<?php
/**
 * POT file generator za FlyRec temu
 * Pokrenuti jednom iz terminala: php generate-pot.php
 * Generiše flyrec.pot koji se koristi za prevod u Poedit-u
 */

$theme_dir = dirname( __DIR__ );
$pot_file  = __DIR__ . '/flyrec.pot';

$php_files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator( $theme_dir )
);

$strings = [];

foreach ( $php_files as $file ) {
    if ( $file->getExtension() !== 'php' ) continue;
    if ( strpos( $file->getPathname(), '/languages/' ) !== false ) continue;

    $content  = file_get_contents( $file->getPathname() );
    $relative = str_replace( $theme_dir . '/', '', $file->getPathname() );

    $patterns = [
        '/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*\'((?:[^\'\\\\]|\\\\.)*)\'(?:,\s*\'flyrec\')?\s*\)/',
        '/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*"((?:[^"\\\\]|\\\\.)*)"(?:,\s*"flyrec")?\s*\)/',
    ];

    foreach ( $patterns as $pattern ) {
        preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $match ) {
            $string = $match[0];
            $offset = $match[1];
            $line   = substr_count( substr( $content, 0, $offset ), "\n" ) + 1;

            if ( ! isset( $strings[ $string ] ) ) {
                $strings[ $string ] = [];
            }
            $strings[ $string ][] = $relative . ':' . $line;
        }
    }
}

ksort( $strings );

$pot  = "# FlyRec WordPress Theme\n";
$pot .= "# Copyright (C) " . date('Y') . " FlyRec Studio\n";
$pot .= "# This file is distributed under the same license as the FlyRec theme.\n";
$pot .= "#\n";
$pot .= "msgid \"\"\n";
$pot .= "msgstr \"\"\n";
$pot .= "\"Project-Id-Version: FlyRec 1.0.0\\n\"\n";
$pot .= "\"Report-Msgid-Bugs-To: info@flyrec.rs\\n\"\n";
$pot .= "\"POT-Creation-Date: " . date('Y-m-d H:i') . "+0000\\n\"\n";
$pot .= "\"MIME-Version: 1.0\\n\"\n";
$pot .= "\"Content-Type: text/plain; charset=UTF-8\\n\"\n";
$pot .= "\"Content-Transfer-Encoding: 8bit\\n\"\n";
$pot .= "\"Language: sr_RS\\n\"\n";
$pot .= "\"X-Generator: FlyRec POT Generator\\n\"\n\n";

foreach ( $strings as $string => $locations ) {
    foreach ( $locations as $loc ) {
        $pot .= "#: " . $loc . "\n";
    }
    $escaped = str_replace( '"', '\\"', $string );
    $pot .= "msgid \"" . $escaped . "\"\n";
    $pot .= "msgstr \"\"\n\n";
}

file_put_contents( $pot_file, $pot );
echo "✅ Generisano: flyrec.pot (" . count( $strings ) . " stringova)\n";
