<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    // not defined, abort
    exit ();
}
// it was defined, now delete
require_once('Chaeschtlizettel_Plugin.php');
$aPlugin = new Chaeschtlizettel_Plugin();

$aPlugin->uninstall();

?>
