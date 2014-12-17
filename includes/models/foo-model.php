<?php
 
/**
 * The Foo model
 *
 * @package MVC Example
 * @subpackage FooModel
 * @since 0.1
 */
 
if( !class_exists( fooModel ) ):
    /**
     * the model for foo plugin
     *
     * @package MVC Example
     * @subpackage FooModel
     * @since 0.1
     */
    class fooModel
    {
        /**
         * the message to be applied with the filter
         * 
         * @package MVC Example
         * @subpackage FooModel
         * @var string
         * @since 0.1
         */
        private $message;
 
        /**
         * class contructor
         *
         * @package MVC Example
         * @subpackage FooModel
         * @since 0.1
         */
        public function __construct()
        {
            $this->message = "HELLO WORLD";
        }
 
        /**
         * allow the controller to modify this property
         *
         * @package MVC Example
         * @subpackage FooModel
         * @param string $newMessage
         * @since0.1
         */
        public function set_message( $newMessage )
        {
            $this->message = $newMessage;
        }
 
        /**
         * retrieve the message
         *
         * @package MVC Example
         * @subpackage FooModel
         * @return string $message
         * @since 0.1
         */
        public function get_message()
        {
            return $this->message;
        }
    }
endif;
?>