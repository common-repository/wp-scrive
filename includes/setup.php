<?php
/*
WP Scrive by Webbstart is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
WP Scrive by Webbstart is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with WP Scrive by Webbstart. If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.html.

NOTE: All trademarks may mentioned in this software belongs to their respective owners.
*/


/* Block direct-access */
if ( ! defined( 'WPINC' ) ) { header("HTTP/1.1 403 Unauthorized"); echo("403 - Permission denied"); die;}

/**
 * This class is for checking if the plugin is correctly set up.
 * 
 * @author Jamie Blomerus <jamie.blomerus@webbstart.nu>
 */
class wp_scrive_setup {

    /**
     * Checks whether the installation proccess is completed.
     *
     * @return bool
     */
    function checkSetup() {
        $options = get_option( 'scrive_options' );
        if( !isset($options['scrive_apitoken']) || !isset($options['scrive_apisecret']) || !isset($options['scrive_accesstoken']) || !isset($options['scrive_accesssecret']) ) {
            $this->RunSetup();
            return false;
        } else {
            return true;
        }
    }

    /**
     * Adds notice to the admin area.
     * 
     * @return void
     */
    function RunSetup() {
        add_action( 'admin_notices', [ $this, 'AdminNotice' ] );
    }

    /**
     * Prints notice to admin area.
     * 
     * @return void
     */
    function AdminNotice() { ?>
        <div class="error notice"><p>
            <?php
            printf(
                esc_html__( 'Remember to complete the setup of WP Scrive. %1$sClick here%2$s to go to settings.', 'wp-scrive' ),
                '<a href="admin.php?page=wp-scrive">',
                '</a>'
            ); ?>
        </p></div>
        <?php
    }
}