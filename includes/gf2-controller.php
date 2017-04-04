<?php

/**
 * The main plugin controller
 *
 * @package Rezon8 GF2
 * @subpackage Main Plugin Controller
 * @since 0.1
 */

//NNU_HOURS_PER_YR =1872;
//SEIU_HOURS_PER_YR = 

class gf2Controller {
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
        $this->series = $series;
        return $series;
    }     
    //Graphing functions NNU/SEIU
    public function render_gravity_dues_data($data) {
    	// *** 
			// $graph_id = $_GET['graph'] ?: 1;
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
        // ***
				// [Fri Feb 10 21:05:32.463169 2017] [:error] [pid 6517] [client 68.60.151.162:21912] PHP Fatal error:  Allowed memory size of 536870912 bytes exhausted (tried to allocate 32 bytes) in /nas/content/staging/baycare/wp-content/plugins/rezon8-gf2/includes/models/financial_class.php on line 698
				// [Fri Feb 10 21:05:32.464437 2017] [:error] [pid 6517] [client 68.60.151.162:21912] PHP Fatal error:  Unknown: Cannot use output buffering in output buffering display handlers in Unknown on line 0			
        $interval = $this->_getPluginOption('plugin_options_interval',$graph_id) ?: 1;
        $iterations = $this->_getPluginOption('plugin_options_iterations',$graph_id) ?: 0;
        $hours_per_yr = $this->_getPluginOption('plugin_options_hours_per_yr',$graph_id);
        $dues_rate = $this->_getPluginOption('plugin_options_dues_rate',$graph_id);
        $cap_type = $this->_getPluginOption('plugin_options_cap_type',$graph_id);
        $cap = $this->_getPluginOption('plugin_options_cap',$graph_id);
        $contribution_rate = $this->_getPluginOption('plugin_options_contribution_rate',$graph_id);
        $return_rate = $this->_getPluginOption('plugin_options_return_rate',$graph_id);
        $hourly = $this->get_hourly_rate();

// 					echo "INTERVAL: $interval<br>";
// 			 		echo "ITERATIONS: $iterations<br>";
// 			 		echo "HOURS PER YR: $hours_per_yr<br>";
// 			 		echo "DUES RATE: $dues_rate<br>";
// 			 		echo "CAP TYPE: $cap_type<br>";
// 			 		echo "CAP: $cap<br>";
// 			 		echo "CONTRIBUTION RATE: $contribution_rate<br>";
// 			 		echo "HOURLY: $hourly<br>";
// 			 		ob_flush();
// 					flush();
// 					exit();

				$duesModel = new DuesModel($hourly,$hours_per_yr,$dues_rate,$cap_type,$cap,$contribution_rate);
        $data = $duesModel->calculate_annual_contribution();

        $savings = array();
        $dues = array();
				// *** potential mem leak >>>
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
        // *** 
				// [Fri Feb 10 12:04:10.807094 2017] [:error] [pid 21810] [client 68.60.151.162:23002] PHP Warning:  Illegal string offset 'label' in /nas/content/staging/baycare/wp-content/plugins/rezon8-gf2/includes/gf2-controller.php on line 89, referer: http://baycare.staging.wpengine.com/inside-the-nnu/
				// [Fri Feb 10 12:04:11.934768 2017] [:error] [pid 25064] [client 68.60.151.162:23018] PHP Warning:  Illegal string offset 'label' in /nas/content/staging/baycare/wp-content/plugins/rezon8-gf2/includes/gf2-controller.php on line 89, referer: http://baycare.staging.wpengine.com/dynamic-nnu-dues-graph/?hwage=%2415.00&id=216&form_id=7&graph=1
        if( isset( $this->series[1]['label'] ) && $this->series[1]['label']==$plan_label ){
            $dues_val = 2;
            $sav_val = 1;
        } else {
            $dues_val = 1;
            $sav_val = 2;

						// *** 
            $dues_val = 2;
            $sav_val = 1;
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
				// ***
				// [Thu Jan 26 20:11:01.493423 2017] [:error] [pid 2877] [client 71.227.63.103:14622] PHP Warning: Invalid argument supplied for foreach() in /nas/content/live/baycare/wp-content/plugins/rezon8-gf2/includes/gf2-controller.php on line 115
        //	if( !isset( $_GET['id'] ) )
        //		return 0;
        $lead_id = $_GET['id'] ?: 0;
        $lead = GFFormsModel::get_lead( $lead_id );
        if( empty( $lead ) )
        	return 0;
				$form = GFFormsModel::get_form_meta( $lead['form_id'] ); 
        if( empty( $form ) )
        	return 0;
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
	        add_settings_field('interval'."_$i", "#$i: Interval in Years<br>(Years Per Graph Bar)", array($v,'plugin_setting_string_intvl'), 'plugin', 'plugin_main');
	        add_settings_field('iter'."_$i", "#$i: Iterations<br>(# of Bars on Graph)", array($v,'plugin_setting_string_iter'), 'plugin', 'plugin_main');
	        add_settings_field('hrs_per_yr'."_$i", "#$i: Hours Worked Per Year", array($v,'plugin_setting_string_hours_per_yr'), 'plugin', 'plugin_main');
	        add_settings_field('dues_rate'."_$i", "#$i: Dues Rate <br>(Fraction, 2% = .02)", array($v,'plugin_setting_string_dues_rate'), 'plugin', 'plugin_main');
	        add_settings_field('cap_type'."_$i", "#$i: Dues Rate & Cap Type", array($v,'plugin_setting_string_cap_type'), 'plugin', 'plugin_main');
	        add_settings_field('cap'."_$i", "#$i: Dues Cap in $<br>(Per Year, or Per NNU Monthly)", array($v,'plugin_setting_string_cap'), 'plugin', 'plugin_main');
            add_settings_field('contrib_rate'."_$i", "#$i: Employer Matching Contribution<br>(Fraction, 2% = .02)", array($v,'plugin_setting_string_contribution_rate'), 'plugin', 'plugin_main');  
	        add_settings_field('return_rate'."_$i", "#$i: Rate of Return<br>(Fraction, 2% = .02)", array($v,'plugin_setting_string_return_rate'), 'plugin', 'plugin_main');  
			
        }
    }

    public function plugin_section_text() {
        $str = '<p>GF2 visualizer settings</p>';
        $str .= "<p><b>Setting The Gravity Forms Redirect</b><br>\n";
        $str .= "In the redirect URL, set graph=N, where N is a number 1-5 ";
        $str .= "that matches the desired plugin settings you wish to use ";
        $str .= "(#1 - #5 below).</p>";
        $str .= "<p><b>Setting the visualizer shortcode</b><br>\n";
        $str .= "[visualizer id=\"N\" data=\"filter_dues_data\" \n";
        $str .= "series=\"filter_403b_series\"]<br>Where N=ID of graph from the ";
        $str .= "visualizer plugin, and 'filter_dues_data' / 'filter_403b_series' ";
        $str .= "refer to filters referenced by the code.</p>";
        $str .= "<p><b>Setting up the visualizer .CSV file</b><br>";
        $str .= "Row #1: <i>Time,[Ascending],[Descending]</i><br>";
        $str .= "Where [Ascending] and [Descending] are the text labels for ascending and descending lines.<br>";
        $str .= "Row #2: <i>string,number,number</i><br>";
        $str .= "Row #3 - #N: <i>5 Years,0,0</i><br>";
        $str .= "It doesn't matter what these rows have as content, but you need a number of ";
        $str .= "rows equal to the number of points on your graph. So if you have 6 points on your graph, ";
        $str .= "your CSV should have 8 rows in total.</p>";
        echo $str;
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
