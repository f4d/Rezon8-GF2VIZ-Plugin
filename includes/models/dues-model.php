<?php
 
/**
 * The Dues model
 *
 * @package Rezon8 GF2
 * @subpackage DuesModel
 * @since 0.1
 */

class DuesModel {
    private $hourly;
    private $hours_per_yr;
    private $dues_rate;
    private $cap_type;
    private $cap;
    private $contribution_rate;
    private $return_rate;
    private $data;

    public function __construct($hourly,$hours_per_yr,$dues_rate,$cap_type,$cap,$contribution_rate,$return_rate=0.5) {
        $this->hourly = $hourly;
        $this->hours_per_yr = $hours_per_yr;
        $this->dues_rate = $dues_rate;
        $this->cap_type = $cap_type;
        $this->cap = $cap;
        $this->contribution_rate = $contribution_rate;
        $this->return_rate = $return_rate;
        $this->data = array();
    }
    public function yearly_wage($hourly,$hours_per_yr) {
        return ($hourly * $hours_per_yr);
    }
    public function hourly_cap_dues_per_yr($hourly,$rate,$cap) {
        if (($hourly*$rate*12)>$cap) {return $cap;}
        else{ return ($hourly*$rate*12);}
    }
    public function year_cap_dues_per_yr($yearly_wage,$rate,$cap) {
        if ($yearly_wage*$rate<$cap) {return ($yearly_wage*$rate);}
        else {return $cap;}
    }
    public function annual_contribution($contribution,$match_rate) {
        return $contribution + ($contribution*$match_rate);
    }
    public function calculate_annual_contribution() {
        $this->data['yr_wage'] = $this->yearly_wage($this->hourly,$this->hours_per_yr);
        $this->data['dues_per_yr'] = $this->calculate_dues_per_year($this->hourly, $this->data['yr_wage'],$this->dues_rate,$this->cap_type,$this->cap);
        $this->data['annual_contribution'] = $this->annual_contribution($this->data['dues_per_yr'],$this->contribution_rate);
        return $this->data;
    }    
    public function calculate_return($num_years,$annual,$rate=0.5) {
        require_once('financial_class.php');
        $f = new Financial;
        return round($f->FV($rate,$num_years, (-1 * $annual), 0));
    }    

    public function calculate_dues_per_year($hourly,$yr_wage,$dues_rate,$cap_type,$cap) {
        if ($cap_type=='hourly') {
            $dues_per_yr = $this->hourly_cap_dues_per_yr($hourly,$dues_rate,$cap);
        } elseif ($cap_type=='yearly') {
            $dues_per_yr = $this->year_cap_dues_per_yr($yr_wage,$dues_rate,$cap);
        }
        return $dues_per_yr;
    }
    
}
?>