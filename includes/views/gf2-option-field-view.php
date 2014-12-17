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
    class gf2OptionFieldView
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

        public function __construct($graph_id) {
            $this->graph_id = $graph_id;
        }

        public static function render( $field ) {
        
        }
        //HTML for options
        public function plugin_setting_string_savings_plan() {
            $options = get_option('plugin_options_savings_plan');
            echo "<input id='savings_plan' name='plugin_options_savings_plan[text_string]' size='40' type='text' value='{$options['text_string']}' />";
        }
        public function plugin_setting_string_label() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_label'.$i);
            echo "<input id='label{$i}' name='plugin_options_label{$i}[text_string]' size='40' type='text' value='{$options['text_string']}' />";
        }
        public function plugin_setting_string_intvl() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_interval'.$i);
            echo "<input id='interval{$i}' name='plugin_options_interval{$i}[text_string]' size='40' type='text' value='{$options['text_string']}' />";
        }
        public function plugin_setting_string_iter() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_iterations'.$i);
            echo "<input id='iter{$i}' name='plugin_options_iterations{$i}[text_string]' size='40' type='text' value='{$options['text_string']}' />";
        }
        public function plugin_setting_string_hours_per_yr() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_hours_per_yr'.$i);
            echo "<input id='hrs_per_yr{$i}' name='plugin_options_hours_per_yr{$i}[text_string]' size='40' type='text' value='{$options['text_string']}' />";
        }
        public function plugin_setting_string_dues_rate() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_dues_rate'.$i);
            echo "<input id='dues_rate{$i}' name='plugin_options_dues_rate{$i}[text_string]' size='40' type='text' value='{$options['text_string']}' />";
        }
        public function plugin_setting_string_cap_type() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_cap_type'.$i);
            $custom_str = "Custom Hourly* 12 rate (NNU)";
            $str = "<select id='cap_type{$i}' name='plugin_options_cap_type{$i}[text_string]' size='40' >";
            switch( $options['text_string'] ) {
                case 'yearly':
                    $str .= "<option value='yearly' selected>Yearly</option><option value='hourly'>$custom_str</option>";
                    break;
                case 'hourly':
                    $str .= "<option value='yearly'>Yearly</option><option value='hourly' selected>$custom_str</option>";
                    break;
                default: 
                    $str .= "<option value='yearly'>Yearly</option><option value='hourly'>$custom_str</option>";
                    break;
            }
            $str .= "</select>";
            echo $str;
        }
        public function plugin_setting_string_cap() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_cap'.$i);
            echo "<input id='cap{$i}' name='plugin_options_cap{$i}[text_string]' size='40' type='text' value='{$options['text_string']}' />";
        }
        public function plugin_setting_string_contribution_rate() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_contribution_rate'.$i);
            echo "<input id='contrib_rate{$i}' name='plugin_options_contribution_rate{$i}[text_string]' size='40' type='text' value='{$options['text_string']}' />";
        }
        public function plugin_setting_string_return_rate() {
            $i = '_'.$this->graph_id;
            $options = get_option('plugin_options_return_rate'.$i);
            echo "<input id='return_rate{$i}' name='plugin_options_return_rate{$i}[text_string]' size='40' type='text' value='{$options['text_string']}' />";
                echo '<hr class="style-five">';
        }        
    }
endif;