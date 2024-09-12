<?php
// If uninstall is not called, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$deletable_options = [ 'user-switcher_version', 'user-switcher_install_time', 'user-switcher_docs_json', 'codexpert-blog-json' ];
foreach ( $deletable_options as $option ) {
    delete_option( $option );
}