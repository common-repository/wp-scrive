<?php
/*
Plugin Name: WP Scrive by Webbstart
Description: Automate your document management and convert more visitors to signed customers
Text domain: wp-scrive
Domain Path: /languages
Requires PHP: 7.2
Version: 1.2.6
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author: Webbstart
Author URI: https://webbstart.nu

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

//SETTINGS
define('WP_SCRIVE_BASEURL', 'https://scrive.com'); // Shall normally be https://scrive.com for production. And https://api-testbed.scrive.com for development.
define('WP_SCRIVE_SETTINGS_DISABLED', false); // If true the settings page will be disabled. This is a good way to protect your credentials.

/* Block direct-access */
if (!defined( 'WPINC' ) ) { header("HTTP/1.1 403 Unauthorized"); echo("403 - Permission denied"); die;}

/* Initiate root class */
$wp_scrive = new wp_scrive;
$wp_scrive->init();

/**
 * The main class of the plugin
 * 
 * @author Jamie Blomerus <jamie.blomerus@webbstart.nu>
 */
class wp_scrive {

    function __construct() {
        $error = new wp_scrive_error_handling;
        set_exception_handler([$error, 'exception_handler']); // Set error handler.
    }

    /**
     * Include scripts, load objects and check if setup is complete.
     * 
     * @return void
     */
    function init() {
        /* Include scripts */
        require_once("includes/setup.php");
        require_once("includes/settings.php");
        
        /* Load objects */
        new wp_scrive_settings;
        $hooks = new wp_scrive_hooks;
        $setup = new wp_scrive_setup;

        /* Hooks, actions etc. */
        $hooks->init();

        /* Check if setup complete */
        if( $setup->checkSetup() ) {
            $this->get_settings();
            $hooks->shortcode_hook();
        }
    }

    /**
     * Get settings and save them to variables
     * 
     * @return void
     */
    function get_settings() {
        global  $document,
                $apitoken,
                $accesstoken,
                $apisecret,
                $accesssecret,
                $signature;
        $options = get_option( 'scrive_options' );
        $document = $options['scrive_document'];
        $apitoken = $options['scrive_apitoken'];
        $apisecret = $options['scrive_apisecret'];
        $accesstoken = $options['scrive_accesstoken'];
        $accesssecret = $options['scrive_accesssecret'];
        $signature = "$apisecret&$accesssecret";
    }

    /**
     * Include form template
     * 
     * @return void
     */
    function output_html() {
        require("template/form.php");
    }

    /**
     * Template for sending API requests
     * 
     * @param string $API_path The path on Scrive API servers. Shall not include the $baseurl as it is added automatically.
     * @param array $postparams Any parameters may sent with the request.
     * 
     * @return mixed Returns body of response.
     */
    function api_request( string $API_path, array $postparams = array() ) {
        global  $apitoken,
                $accesstoken,
                $apisecret,
                $accesssecret,
                $signature;
    
        $headers = array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'oauth_signature_method="PLAINTEXT",oauth_consumer_key="'.$apitoken.'",oauth_token="'.$accesstoken.'",oauth_signature="'. $signature . '"'
        );

        $args = array(
            'body'        => $postparams,
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => $headers,
            'cookies'     => array(),
        );

        $response = wp_remote_post( WP_SCRIVE_BASEURL . $API_path, $args );

        return $response['body'];
    }

    /**
     * Redirects end user to the document. Or shows link if javascript is disabled.
     * 
     * @param string $path The path to the document. Will be added after the $baseurl.
     */
    function redirectToScrive( string $path ) {
        global $baseurl;
        $scriveurl = esc_url(WP_SCRIVE_BASEURL.$path);
        $html_redirect = "<script>window.location = '$scriveurl';</script><h3>". __('Javascript seem to be disabled in your browser. Please activate it or click this link to go to the document:', 'wp-scrive') ." <a href='$scriveurl'>$scriveurl</a></h3>";
        //Escape and echo the redirect
        echo wp_kses($html_redirect, array(
            'script' => array(),
            'h3' => array(),
            'a' => array(
                'href' => array()
            )
        ));
        exit;
    }

    /**
     * This is the function which creates the document, adds end user data and redirects user to Scrive.
     * 
     * @param string $template_id The document-id of the template.
     * 
     * @return void
     */
    function create_document( string $template_id ) {
        global $error; // Use error class

        if ( isset( $_POST['form-submit'] ) ) {
            /*  End user input is scary */
            $fname = sanitize_text_field( $_POST['fname'] );
            $lname = sanitize_text_field( $_POST['lname'] );
            $email = sanitize_email( $_POST['email'] );

            $document = json_decode($this->api_request("/api/v2/documents/newfromtemplate/$template_id"));

            "wp_scrive_error_handling"::check_for_errors($document); //Check for errors - Not perfect line, but gave up after 2 hours of trying to fix it.
            
            $document_id = $document->id;
            
            /* Make updates to document data */
            $document->parties[1]->delivery_method = "api"; // Changes delivery method to api
            $document->parties[1]->fields[0]->value = $fname; // Fill user's first name
            $document->parties[1]->fields[1]->value = $lname; // Fill user's last name
            $document->parties[1]->fields[2]->value = $email; // Fill user's email
            
            /* Send updates to Scrive */
            $this->api_request("/api/v2/documents/$document_id/update", array('document' =>json_encode($document)));
            
            $response = json_decode($this->api_request("/api/v2/documents/$document_id/start"));

            "wp_scrive_error_handling"::check_for_errors($response); //Check for errors - Not perfect line, but gave up after 2 hours of trying to fix it.
            
            $this->redirectToScrive($response->parties[1]->api_delivery_url);
	    }
    }

    /**
     * Shortcode callback
     * 
     * @param mixed[] $atts Attributes added by WordPress.
     * 
     * @return mixed
     */
    function shortcode( $atts ) {
        if ( isset($atts['document']) ) {
            $document = $atts['document'];
        } else {
            global $document;
        }

	    ob_start();
	    $this->create_document($document);
	    $this->output_html();

	    return ob_get_clean();
    }
}

/**
 * A class for all WordPress hooks
 * 
 * @author Jamie Blomerus <jamie.blomerus@webbstart.nu>
 */
class wp_scrive_hooks {

    /**
     * Load textdomain on initiation.
     * 
     * @return void
     */
    function init() {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'wp_scrive_cron', 'bl_cron_exec' );
    }

    /**
     * Adds shortcode.
     *
     * @return void
     */
    function shortcode_hook() {
        global $wp_scrive;
        add_shortcode( 'wp-scrive', [ $wp_scrive, 'shortcode' ] );
    }

    /**
     * Loads plugin textdomain
     * 
     * @return void
     */
    function load_textdomain() {
        load_plugin_textdomain( 'wp-scrive', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
}

/**
 * All error related stuff in here.
 * 
 * @author Jamie Blomerus Jamie Blomerus <jamie.blomerus@webbstart.nu>
 */
class wp_scrive_error_handling {
    /**
     * Check for errors in Scrive API Requests
     * 
     * @param mixed[] $document Document object
     * 
     * @throws Exception Outputting errors if request failed.
     * 
     * @return void
     */
    static function check_for_errors($document) {
        if(isset($document->error_type)){
            switch($document->error_type) {
                case "request_parameters_missing":
                case "request_parameters_parse_error":
                case "endpoint_not_found":
                case "document_object_version_mismatch":
                    throw new Exception(__( 'Something went wrong in this plugins code. Contact support at ', 'wp-scrive' ). "support@webbstart.nu", $document->http_code);
                    break;
                case "invalid_authorisation":
                    throw new Exception(__( 'Supplied credentials are invalid. Please update them.', 'wp-scrive' ), $document->http_code);
                    break;
                case "insufficient_privileges":
                    throw new Exception(__( 'Supplied credential does not have permission for this request.', 'wp-scrive' ), $document->http_code);
                    break;
                case "document_action_forbidden":
                    throw new Exception(__( 'Supplied credential does not have sufficient privileges to perform actions on this document.', 'wp-scrive' ), $document->http_code);
                    break;
                case "resource_not_found":
                    throw new Exception(__( 'The document does not seem to exist.', 'wp-scrive' ), $document->http_code);
                    break;
                case "server_error":
                    throw new Exception(__( 'Something went wrong on Scrives servers.', 'wp-scrive' ), $document->http_code);
                    break;
                case "document_state_error":
                    throw new Exception(__( 'The specified document is not a template.', 'wp-scrive' ), $document->http_code);
                    break;
                default:
                    throw new Exception(__( 'Unknown error occured. Contact support at ', 'wp-scrive' ). "support@webbstart.nu", $document->http_code);
                    break;
            }
        }
    }

    /**
     * PHP Default error handler, shall not be called manually.
     */
    function exception_handler($e){
        ?>
            <p><strong>
                <?php
                if ( current_user_can('administrator') ) {
                    echo (esc_html(__( 'Error occured: ', 'wp-scrive' ). $e->getMessage()));
                } else {
                    _e( "Well that's wierd. This plugin uses very simple code. But it still somehow managed to fail, lol.", 'wp-scrive' );
                }
                ?>
            </strong></p>
        <?php
        die;
    }
}
?>
