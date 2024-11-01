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
 * @author Jamie Blomerus <jamie.blomerus@webbstart.nu>
 */
class wp_scrive_settings {

    /**
     * Add actions
     */
    function __construct() {
        add_action( 'admin_init', [ $this, 'scrive_register_settings' ] );
        add_action( 'admin_menu', [ $this, 'scrive_add_settings' ] );
    }

    /**
     * Show description next to the API settings.
     * 
     * @return void
     */
    function scrive_api_text() { ?>
        <p class="description">
        <?php
        printf(
            esc_html__( 'Get your personal access credentials from %1$sScrive%2$s.', 'wp-scrive' ),
            '<a href="https://scrive.com/new/integrations">',
            '</a>'
        ); ?>
        </p>
        <?php
    }

    /**
     * Print setting fields to screen.
     * 
     * @return void
     */
    function scrive_setting_doc_id() {
        echo "<input id='scrive_setting_doc_id' name='scrive_options[scrive_document]' type='text' value='" . esc_attr($this->output_setting('scrive_document')) . "' />";
    }
    function scrive_setting_apitoken() {
        echo "<input id='scrive_setting_apitoken' name='scrive_options[scrive_apitoken]' type='text' value='" . esc_attr($this->output_setting('scrive_apitoken')) . "' />";
    }
    function scrive_setting_apisecret() {
        echo "<input id='scrive_setting_apisecret' name='scrive_options[scrive_apisecret]' autocomplete='off' type='password' value='" . esc_attr($this->output_setting('scrive_apisecret')) . "' />";
    }
    function scrive_setting_accesstoken() {
        echo "<input id='scrive_setting_accesstoken' name='scrive_options[scrive_accesstoken]' type='text' value='" . esc_attr($this->output_setting('scrive_accesstoken')) . "' />";
    }
    function scrive_setting_accesssecret() {
        echo "<input id='scrive_setting_accesssecret' name='scrive_options[scrive_accesssecret]' autocomplete='off' type='password' value='" . esc_attr($this->output_setting('scrive_accesssecret')) . "' />";
    }

    /**
     * Makes setting values ready to be output.
     * 
     * @param string $setting The name of the setting.
     * 
     * @return string
     */
    function output_setting(string $setting) {
        $options = get_option( 'scrive_options' );
        if (isset($options[$setting])) {
            return $options[$setting];
        } else {
            return "";
        }
    }

    /**
     * Register setting, sections and fields.
     * 
     * @return void
     */
    function scrive_register_settings() {
        register_setting( 'scrive_options', 'scrive_options' );
        add_settings_section( 'scrive_settings', __('WP Scrive - Settings', 'wp-scrive'), null, 'wp-scrive_plugin' );
        add_settings_section( 'scrive_api', __('API settings', 'wp-scrive'), [ $this, 'scrive_api_text' ], 'wp-scrive_plugin' );

        add_settings_field( 'scrive_setting_doc_id', __('Document-ID', 'wp-scrive'), [ $this, 'scrive_setting_doc_id' ], 'wp-scrive_plugin', 'scrive_settings' );
        add_settings_field( 'scrive_setting_apitoken', __('Client credentials identifier', 'wp-scrive'), [ $this, 'scrive_setting_apitoken' ], 'wp-scrive_plugin', 'scrive_api' );
        add_settings_field( 'scrive_setting_apisecret', __('Client credentials secret', 'wp-scrive'), [ $this, 'scrive_setting_apisecret' ], 'wp-scrive_plugin', 'scrive_api' );
        add_settings_field( 'scrive_setting_accesstoken', __('Token credentials identifier', 'wp-scrive'), [ $this, 'scrive_setting_accesstoken' ], 'wp-scrive_plugin', 'scrive_api' );
        add_settings_field( 'scrive_setting_accesssecret', __('Token credentials secret', 'wp-scrive'), [ $this, 'scrive_setting_accesssecret' ], 'wp-scrive_plugin', 'scrive_api' );
    }

    /**
     * Print settings fields to screen.
     * 
     * @return void
     */
    function scrive_settings() {
        ?>
        <form action="options.php" method="post">
            <?php 
            settings_fields( 'scrive_options' );
            do_settings_sections( 'wp-scrive_plugin' ); ?>
            <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
        </form><br>
        <?php
    }

    /**
     * Check whether settings page are enabled and if it are then add it.
     * 
     * @return void
     */
    function scrive_add_settings() {
        if(!WP_SCRIVE_SETTINGS_DISABLED) {
            add_menu_page( __('WP Scrive', 'wp-scrive'), __('WP Scrive Settings', 'wp-scrive'), 'activate_plugins', 'wp-scrive', [ $this, 'scrive_settings' ] );
        }
    }
}