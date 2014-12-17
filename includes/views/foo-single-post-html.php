<?php
 
/**
 * The single post view
 *
 * @package MVC Example
 * @subpackage Single Post View
 * @since 0.1
 */
 
if( !class_exists( fooSinglePostHtmlView) ):
 
/**
 * class to render the html for single posts
 *
 * @package MVC Example
 * @subpackage Single Post View
 * @since 0.1
 */
    class fooSinglePostHtmlView
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
            <h1><?php echo $message; ?></h1>
            <p>This is a single post page.</p>
        <?php
        }
    }
endif;