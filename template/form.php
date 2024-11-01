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
?>
<form method="post">
    <noscript><p><?php esc_html_e('We recommend enabling Javascript for the best experience', 'wp-scrive'); ?></p></noscript>
    <p>
        <?php esc_html_e('Full name:', 'wp-scrive'); ?> <br/>
        <input type="text" name="fname" autocomplete="given-name" placeholder="<?php esc_html_e('First name', 'wp-scrive'); ?>" value="" size="20" />
        <input type="text" name="lname" autocomplete="famliy-name" placeholder="<?php esc_html_e('Last name', 'wp-scrive'); ?>" value="" size="20" /><br>

        <?php esc_html_e('Contact details:', 'wp-scrive'); ?> <br/>
        <input type="email" name="email" autocomplete="email" placeholder="<?php esc_html_e('Email', 'wp-scrive'); ?>" value="" size="45" />
    </p>
    <p>
        <input type="submit" name="form-submit" value="<?php esc_html_e('Create document', 'wp-scrive'); ?>">
    </p>
</form> 