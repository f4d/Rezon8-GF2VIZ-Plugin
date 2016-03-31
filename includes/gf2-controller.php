<?php

/**
 * The main plugin controller
 *
 * @package Rezon8 GF2
 * @subpackage Main Plugin Controller
 * @since 0.1
 */

 
require_once("models/financial_class.php");
 
 class gf2Controller {
    /** the class constructor   */
    public $series; 

    public function __construct() {
        $this->series = "NNNNN!";
        if ( is_admin() ){ 
            require_once("views/gf2-option-field-view.php");
            add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
            add_action( 'admin_init',  array( $this, 'plugin_admin_init' ) );
        } else {
            require_once("models/dues-model.php");
            //add_filter('filter_florida_data', array($this,'render_florida_graph'), 10, 3 );
            add_filter( 'filter_403b_series', array($this,'filter_403b_series'), 10, 3 );        
            add_filter( 'filter_dues_data', array($this,'render_gravity_dues_data'), 10, 3 );        
        }
    }
    public function filter_403b_series($series) {
        $this->series=$series;
        return $series;
    }     
    //Graphing functions NNU/SEIU
    public function render_gravity_dues_data($data) {
    	$graph_id = $_GET['graph'];
    	$data = $this->render_graph_data($data,$graph_id);
        return $data;
    }
    private function _getPluginOption($option,$graph_number) {
    	$option_str = $option.'_'.$graph_number;
    	$arr = get_option($option_str);
    	return $arr['text_string'];
    }
    public function render_graph_data($graph_data,$graph_id) {
        //print_r($this->series);
        $interval = $this->_getPluginOption('plugin_options_interval',$graph_id);
        $iterations = $this->_getPluginOption('plugin_options_iterations',$graph_id);
        $hours_per_yr = $this->_getPluginOption('plugin_options_hours_per_yr',$graph_id);
        $dues_rate = $this->_getPluginOption('plugin_options_dues_rate',$graph_id);
        $cap_type = $this->_getPluginOption('plugin_options_cap_type',$graph_id);
        $cap = $this->_getPluginOption('plugin_options_cap',$graph_id);
        $contribution_rate = $this->_getPluginOption('plugin_options_contribution_rate',$graph_id);
        $return_rate = $this->_getPluginOption('plugin_options_return_rate',$graph_id);
        $hourly = $this->get_hourly_rate();
 		/*echo "INTERVAL: $interval<br>";
 		echo "ITERATIONS: $iterations<br>";
 		echo "HOURS PER YR: $hours_per_yr<br>";
 		echo "DUES RATE: $dues_rate<br>";
 		echo "CAP TYPE: $cap_type<br>";
 		echo "CAP: $cap<br>";
 		echo "CONTRIBUTION RATE: $contribution_rate<br>";
 		echo "HOURLY: $hourly<br>";*/

        $duesModel = new DuesModel($hourly,$hours_per_yr,$dues_rate,$cap_type,$cap,$contribution_rate);
        $data = $duesModel->calculate_annual_contribution();

        $savings = array();
        $dues = array();
        for ($i=$interval;$i<=($interval*$iterations);$i=$i+$interval) {
            $savings[] = $duesModel->calculate_return($i,$data['annual_contribution'],$return_rate);
            $dues[] = (-1 * $data['dues_per_yr'] * $i);
            //echo "FV $i years:  " . $duesModel->calculate_return($i,$data['annual_contribution']) . "<br>";
            //echo "union dues $i years: ".(-1 * $data['dues_per_yr'] * $i) . "<br>";
        }

        $graph_data = $this->createGraphDataArray($graph_data,$savings,$dues,$interval,$iterations);
        return $graph_data;

    }

    public function createGraphDataArray( $data, $savings, $dues, $interval, $iterations ) {     
        $arr = get_option('plugin_options_savings_plan');
        $plan_label = $arr['text_string'];    
        if ($this->series[1]['label']==$plan_label) {
            $dues_val = 2;
            $sav_val = 1;
        } else {
            $dues_val = 1;
            $sav_val = 2;
        }
        for ($i=0;$i<$iterations;$i++) {
            for ($j=0;$j<count($data[$i]);$j++) {
                $a = $i + 1;                           
                if ($j==0) {$data[$i][$j] = ($interval*$a).' Years'; }
                elseif ($j==$dues_val) {$data[$i][$j] = $dues[$i];}
                elseif ($j== $sav_val) {$data[$i][$j] = $savings[$i];}                
            }
        }
        return $data; 
    }

    public function get_hourly_rate() {
        $lead_id = $_GET['id'];
        $lead = GFFormsModel::get_lead( $lead_id ); 
        $form = GFFormsModel::get_form_meta( $lead['form_id'] ); 
        $values= array();
        foreach( $form['fields'] as $field ) {

            $values[$field['id']] = array(
                'id'    => $field['id'],
                'label' => $field['label'],
                'value' => $lead[ $field['id'] ],
            );
        }   
        foreach($values as $v) {
			//The Gravity Forms Field Must Be labeled as below - "Hourly Wage" //
            if ($v['label']=='Hourly Wage') {return $v['value'];}
        }
        return 0;
    }

    /* ADMIN FUNCTIONS */
    public function plugin_menu() {
        add_options_page( 'Rezon8 GF2 Plugin Options', 'Rezon8 GF2', 'manage_options', 'rezon8-gf2-plugin', array( $this, 'plugin_options' ) );
    }   
    public function plugin_options() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        require_once( 'views/gf2-settings-view.php' );
        return gf2SettingsView::render('');
    }
    public function plugin_admin_init(){

        add_settings_section('plugin_main', 'Main Settings', array($this,'plugin_section_text'), 'plugin');
        $fv = new gf2OptionFieldView(0); 
        //print_r($v);
        register_setting( 'plugin_options', 'plugin_options_savings_plan', array($this,'savings_plan_validate') );
        
        add_settings_field('savings_plan', "Savings Plan Type - Label", array($fv,'plugin_setting_string_savings_plan'), 'plugin', 'plugin_main');  

        for ($i=1;$i<6;$i++) {
            $v = new gf2OptionFieldView($i);
	        register_setting( 'plugin_options', 'plugin_options_label'."_$i", array($this,'label_validate') );
	        register_setting( 'plugin_options', 'plugin_options_interval'."_$i", array($this,'interval_validate') );
	        register_setting( 'plugin_options', 'plugin_options_iterations'."_$i", array($this,'iterations_validate') );
	        register_setting( 'plugin_options', 'plugin_options_hours_per_yr'."_$i", array($this,'hours_per_yr_validate') );
	        register_setting( 'plugin_options', 'plugin_options_dues_rate'."_$i", array($this,'dues_rate_validate') );
	        register_setting( 'plugin_options', 'plugin_options_cap_type'."_$i", array($this,'cap_type_validate') );
	        register_setting( 'plugin_options', 'plugin_options_cap'."_$i", array($this,'cap_validate') );
            register_setting( 'plugin_options', 'plugin_options_contribution_rate'."_$i", array($this,'contribution_rate_validate') );
	        register_setting( 'plugin_options', 'plugin_options_return_rate'."_$i", array($this,'return_rate_validate') );

	        add_settings_field('label'."_$i", "#$i: Label", array($v,'plugin_setting_string_label'), 'plugin', 'plugin_main');
	        add_settings_field('interval'."_$i", "#$i: Interval in Years", array($v,'plugin_setting_string_intvl'), 'plugin', 'plugin_main');
	        add_settings_field('iter'."_$i", "#$i: Iterations", array($v,'plugin_setting_string_iter'), 'plugin', 'plugin_main');
	        add_settings_field('hrs_per_yr'."_$i", "#$i: Hours Per Year", array($v,'plugin_setting_string_hours_per_yr'), 'plugin', 'plugin_main');
	        add_settings_field('dues_rate'."_$i", "#$i: Dues Rate", array($v,'plugin_setting_string_dues_rate'), 'plugin', 'plugin_main');
	        add_settings_field('cap_type'."_$i", "#$i: Dues Rate & Cap Type", array($v,'plugin_setting_string_cap_type'), 'plugin', 'plugin_main');
	        add_settings_field('cap'."_$i", "#$i: Dues Cap", array($v,'plugin_setting_string_cap'), 'plugin', 'plugin_main');
            add_settings_field('contrib_rate'."_$i", "#$i: Employer Matching Contribution", array($v,'plugin_setting_string_contribution_rate'), 'plugin', 'plugin_main');  
	        add_settings_field('return_rate'."_$i", "#$i: Rate of Return", array($v,'plugin_setting_string_return_rate'), 'plugin', 'plugin_main');  
			
        }
    }

    public function plugin_section_text() {
        echo '<p>GF2 visualizer settings</p>';
    }

    //validation
   public function label_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
    }
    public function interval_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
    }
    public function iterations_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
    }
    public function hours_per_yr_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
    }
    public function dues_rate_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
    }
    public function cap_type_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
    }
    public function cap_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
    }
    public function contribution_rate_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
		
    }
    public function return_rate_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
        
    }
    public function savings_plan_validate($input) {
        $newinput['text_string'] = trim($input['text_string']);
        return $newinput;
        
    }
 
}