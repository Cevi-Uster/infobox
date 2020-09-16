<?php
/*
   Plugin Name: Chäschtlizettel
   Plugin URI: http://wordpress.org/extend/plugins/chaeschtlizettel/
   Version: 1.3
   Author: Matthias Kunz
   Description: Provides Information for Meetingpoints in YMCA Scouting
   Text Domain: chaeschtlizettel
   License: GPLv3
  */

/*
    "Chaeschtlizettel Plugin" Copyright (C) 2018 Matthias Kunz v/o Funke  (email : funke.uster@cevi.ch)
    "Chaeschtlizettel Plugin" is derived from "WordPress Plugin Template" Copyright (C) 2018 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Chaeschtlizettel for WordPress.

    Chaeschtlizettel is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

$Chaeschtlizettel_minimalRequiredPhpVersion = '4.9';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function Chaeschtlizettel_noticePhpVersionWrong() {
    global $Chaeschtlizettel_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "Chäschtlizettel" requires a newer version of PHP to be running.',  'chaeschtlizettel').
            '<br/>' . __('Minimal version of PHP required: ', 'chaeschtlizettel') . '<strong>' . $Chaeschtlizettel_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'chaeschtlizettel') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function Chaeschtlizettel_PhpVersionCheck() {
    global $Chaeschtlizettel_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $Chaeschtlizettel_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'Chaeschtlizettel_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function Chaeschtlizettel_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('chaeschtlizettel', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// Initialize i18n
add_action('plugins_loadedi','Chaeschtlizettel_i18n_init');

// Run the version check.
// If it is successful, continue with initialization for this plugin
if (Chaeschtlizettel_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('chaeschtlizettel_init.php');
    Chaeschtlizettel_init(__FILE__);
}
