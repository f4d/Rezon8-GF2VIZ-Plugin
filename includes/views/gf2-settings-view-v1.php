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

                <form method="post" action="options.php">
                    <?php //settings_fields( 'baw-settings-group' ); ?>
                    <?php //do_settings_sections( 'baw-settings-group' ); ?>
                    <table class="form-table">
                        <tr valign="top">
                        <th scope="row">New Option Name</th>
                        <td><input type="text" name="new_option_name" value="<?php echo get_option('new_option_name'); ?>" /></td>
                        </tr>
                         
                        <tr valign="top">
                        <th scope="row">Some Other Option</th>
                        <td><input type="text" name="some_other_option" value="<?php echo get_option('some_other_option'); ?>" /></td>
                        </tr>
                        
                        <tr valign="top">
                        <th scope="row">Options, Etc.</th>
                        <td><input type="text" name="option_etc" value="<?php echo get_option('option_etc'); ?>" /></td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>

                </form>
            </div>
        <?php
        }
    }
endif;