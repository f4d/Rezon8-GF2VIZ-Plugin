<?php
 
/**
 * The single post view
 *
 * @package MVC Example
 * @subpackage Single Post View
 * @since 0.1
 */
 
if( !class_exists( gf2SettingsView ) ):
 
/**
 * class to render the html for single posts
 *
 * @package MVC Example
 * @subpackage Single Post View
 * @since 0.1
 */
    class gf2SettingsView
    {
        /**
         * print the message
         *
         * @package MVC Example
         * @subpackage Single Post View
         *
         * @return string $html the html for the view
         * @since 0.1
         */
        public static function render( $message )
        {
        ?>
            <div class="wrap">
                <h2>Rezon8 GF2</h2>
                <form action="options.php" method="post">
                <?php settings_fields('plugin_options'); ?>
                <?php do_settings_sections('plugin'); ?>
                <hr /> 
                <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
                </form>
            </div>
        <?php
        }
    }
endif;