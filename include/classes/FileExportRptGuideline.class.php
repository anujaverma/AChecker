<?php
/************************************************************************/
/* AChecker                                                             */
/************************************************************************/
/* Copyright (c) 2008 - 2011                                            */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id:

/**
* FileExportRptGuideline
* Class to generate error report in form of 3 arrays: known, likely, potential
* is based on HTMLByGuidelineRpt
* @access	public
* @author	Casian Olga
* @package checker
*/
if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");
include_once(AC_INCLUDE_PATH.'classes/DAO/UserDecisionsDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/AccessibilityRpt.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/ChecksDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelineGroupsDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelineSubgroupsDAO.class.php');

class FileExportRptGuideline extends AccessibilityRpt {
	
	var $errors_by_checks = array();               // Re-arranged errors table with the array key check_id

	var $group_known_problems = array();			// array of all info about known problems
	var $group_likely_problems = array();			// array of all info about known likely
	var $group_potential_problems = array();		// array of all info about known potential	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $group_affirmed_problems = array();			// array of all info about known affirmed
	var $group_checked_warnings = array();			// array of all info about known checked
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End
	
	
	var $nr_known_problems = 0;
	var $nr_likely_problems = 0;
	var $nr_potential_problems = 0;
	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $nr_affirmed_problems = 0;
	var $nr_checked_warnings = 0;
	var $arr_checks_repeated_known_likely_potential = array();
	var $arr_checks_repeated_affirmed = array();
	var $arr_checks_repeated_checked = array();
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End
	/**
	* public
	* $errors: an array, output of AccessibilityValidator -> getValidationErrorRpt
	* $type: array
	*/
	function FileExportRptGuideline($errors, $gid, $user_link_id = '')
	{
		// run parent constructor
		parent::AccessibilityRpt($errors, $user_link_id);
		
		$this->gid = $gid;

		$this->checksDAO = new ChecksDAO();
		$this->guidelineGroupsDAO = new GuidelineGroupsDAO();
		$this->guidelineSubgroupsDAO = new GuidelineSubgroupsDAO();
	}
	
	/**
	* public
	* @return nr of errors - $nr_known_problems, $nr_likely_problems, $nr_potential_problems
	*/
	public function getErrorNr()
	{
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
		return array($this->nr_known_problems, $this->nr_likely_problems, $this->nr_potential_problems, $this->nr_affirmed_problems, $this->nr_checked_warnings);
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	}
	
	/**
	* public
	* main process to generate report and store result in 3 arrays
	*/
	public function generateRpt()
	{
		$this->errors_by_checks = $this->rearrange_errors_array($this->errors);
		
		// display all named guidelines and their checks 
		$named_groups = $this->guidelineGroupsDAO->getNamedGroupsByGuidelineID($this->gid);	
		
		if (is_array($named_groups))
		{
			foreach ($named_groups as $group)
			{
				unset($subgroup_known_problems);
				unset($subgroup_likely_problems);
				unset($subgroup_potential_problems);
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				unset($subgroup_affirmed_problems);
				unset($subgroup_checked_warnings);
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				
				// display named subgroups and their checks
				$named_subgroups = $this->guidelineSubgroupsDAO->getNamedSubgroupByGroupID($group['group_id']);
				
				if (is_array($named_subgroups))
				{
					foreach ($named_subgroups as $subgroup)
					{						
						$subgroup_checks = $this->checksDAO->getChecksBySubgroupID($subgroup['subgroup_id']);
						if (is_array($subgroup_checks))
						{
							// get html of all the problems in this subgroup
							list($known_problems, $likely_problems, $potential_problems, $affirmed_problems, $checked_warnings) = 
								$this->generateChecksTable($subgroup_checks);
								
							$subgroup_title = _AC($subgroup['name']);
							
							if ($known_problems <> "") {
								$subgroup_known_problems[$subgroup_title] = $known_problems;
							} 
							if ($likely_problems <> "") {
								$subgroup_likely_problems[$subgroup_title] = $likely_problems;
							} 
							if ($potential_problems <> "") {
								$subgroup_potential_problems[$subgroup_title] = $potential_problems;
							}
							//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
							if ($affirmed_problems <> "") {
								$subgroup_affirmed_problems[$subgroup_title] = $affirmed_problems;
							}
							if ($checked_warnings <> "") {
								$subgroup_checked_warnings[$subgroup_title] = $checked_warnings;
							}
							//Added by Anirudh Subramanian for AChecker Manual Evaluations End
						}
					} // end of foreach $named_subgroups
				} // end of if $named_subgroups
				
				$group_title = _AC($group['name']);
				
				if ($subgroup_known_problems <> '') {
					$this->group_known_problems[$group_title] = $subgroup_known_problems;
				} 	
				
				if ($subgroup_likely_problems <> '') {
					$this->group_likely_problems[$group_title] = $subgroup_likely_problems;
				} 
				
				if ($subgroup_potential_problems <> '') {
					$this->group_potential_problems[$group_title] = $subgroup_potential_problems;
				}
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				if ($subgroup_affirmed_problems <> '') {
					$this->group_affirmed_problems[$group_title] = $subgroup_affirmed_problems; 
				}
				if ($subgroup_checked_warnings <> '') {
					$this->group_checked_warnings[$group_title] = $subgroup_checked_warnings; 
				}
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			} // end of foreach $named_groups 	
		} // end of if $named_groups
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
		return array($this->group_known_problems, $this->group_likely_problems, $this->group_potential_problems, $this->group_affirmed_problems, $this->group_checked_warnings );
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	}
	
	/**
	 * Re-arrang check error array with check_id as the primary key
	 * @param $errors - the error array
	 * @return Re-arranged error array
	 */
	private function rearrange_errors_array($errors) {
		// return an empty array if the parameter is not an expected array
		if (!is_array($errors)) return array();
		
		$new_errors = array();
		foreach ($errors as $error) {
			$new_errors[$error["check_id"]][] = $error;
		}
		return $new_errors;
	}

	/**
	 * private
	 * Return array of the checks error
	 * @param $checks_array
	 * @return an array of htmls of (known_problem, likely_problems, potential_problems)
	 */
	private function generateChecksTable($checks_array)
	{  
		if (!is_array($checks_array)) return NULL;
		
		foreach ($checks_array as $check) {
			unset($howto_repair);
			unset($question);
			
			$check_id = $check["check_id"];

			// continue with the next check if there is no errors for this check
			if (!is_array($this->errors_by_checks[$check_id])) continue;
			
			$row = $this->checksDAO->getCheckByID($check_id);
			
			$repair = _AC($row['how_to_repair']);
			if ($repair <> '') 
			{				
				$howto_repair['label'] = _AC("repair");
				$howto_repair['detail'] = $repair;
			}
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
			list($error_set, $error_set_likely, $error_set_potential, $error_set_affirmed, $error_set_checked, $no_of_errors_affirmed, $no_of_errors_checked) = $this->get_table_rows_for_one_check($this->errors_by_checks[$check_id], $check_id, $row["confidence"]);
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
			//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
			$no_of_errors_for_one_check = count($this->errors_by_checks[$check_id]);
			if($row["confidence"] == POTENTIAL){
				$no_of_check_errors_potential_problems = $no_of_check_errors_potential_problems + $no_of_errors_for_one_check;
				$no_of_check_errors_potential_affirmed_problems = $no_of_check_errors_potential_affirmed_problems + $no_of_errors_affirmed;
				$no_of_check_errors_potential_checked_warnings = $no_of_check_errors_potential_checked_warnings + $no_of_errors_checked;
			}
			if($row["confidence"] == LIKELY){
				$no_of_check_errors_likely_problems = $no_of_check_errors_likely_problems + $no_of_errors_for_one_check;
				$no_of_check_errors_likely_affirmed_problems = $no_of_check_errors_likely_affirmed_problems + $no_of_errors_affirmed;
				$no_of_check_errors_likely_checked_warnings = $no_of_check_errors_likely_checked_warnings + $no_of_errors_checked;
			}
			//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			$one_problem['check_label'] = _AC("check");			
			$one_problem['check_id'] = $check_id;			
			$one_problem['error'] = _AC($row["err"]);
			$one_problem['repair'] = $howto_repair;						
			$one_problem['subgroup_id'] = $check["subgroupID"];
			//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
			$is_likely_check_added = false;
			$is_potential_check_added = false;
			//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			                            
			if ($row["confidence"] == KNOWN) { 
				$one_problem['errors'] = $error_set;
				$known[] = $one_problem;
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
			} else if ($row["confidence"] == LIKELY) { 
				if($no_of_errors_checked > 0) {
					if(($no_of_errors_checked + $no_of_errors_affirmed) == $no_of_errors_for_one_check) {
						$one_problem['errors'] = $error_set_checked;
						$checked[] = $one_problem;
					} else {
						$one_problem['errors'] = $error_set_likely;
						$likely[] = $one_problem;
						$is_likely_check_added = true;
					}
				}
				if($no_of_errors_affirmed > 0) {
					if(($no_of_errors_checked + $no_of_errors_affirmed) == $no_of_errors_for_one_check) {
						debug_to_log('Inside affirmed set');
						$one_problem['errors'] = $error_set_affirmed;
						$affirmed[] = $one_problem;
					} else {
						if($is_likely_check_added){
							$one_problem['errors'] = $error_set_likely;
							$likely[] = $one_problem;
						}
					}
				}
				
				if( $no_of_errors_checked == 0 && $no_of_errors_affirmed == 0 ) {
					$one_problem['errors'] = $error_set_likely;
					$likely[] = $one_problem;
				} 
			} else if ($row["confidence"] == POTENTIAL) { 
				if($no_of_errors_checked > 0) {
					if(($no_of_errors_checked + $no_of_errors_affirmed) == $no_of_errors_for_one_check) {
						$one_problem['errors'] = $error_set_checked;
						$checked[] = $one_problem;
					} else {
						$one_problem['errors'] = $error_set_potential;
						$potential[] = $one_problem;
						$is_potential_check_added = true;
					}
				}
				if($no_of_errors_affirmed > 0) {
					if(($no_of_errors_checked + $no_of_errors_affirmed) == $no_of_errors_for_one_check) {
						debug_to_log('Inside affirmed set');
						$one_problem['errors'] = $error_set_affirmed;
						$affirmed[] = $one_problem;
						debug_to_log($error_set_affirmed);
					} else {
						if ( $is_potential_check_added ) {
							$one_problem['errors'] = $error_set_potential;
							$potential[] = $one_problem;
						}
					}
				}
				if( $no_of_errors_checked == 0 && $no_of_errors_affirmed == 0 ) {
					$one_problem['errors'] = $error_set_potential;
					$potential[] = $one_problem;
				}
			}
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
		}
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
		return array($known, $likely, $potential , $affirmed , $checked);
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	}
	
	/** 
	* private
	* generate array for all errors on one check 
	* @param
	* $errors_for_this_check: all errors
	* $check_id
	* $confidence: KNOWN, LIKELY, POTENTIAL  @ see include/constants.inc.php
	* @return html table rows
	*/
	private function get_table_rows_for_one_check($errors_for_this_check, $check_id, $confidence)
	{
		if (!is_array($errors_for_this_check)) {  // no problem found for this check
			return '';
		}
		
		$no_of_errors_affirmed = 0;
		$no_of_errors_checked = 0;
		
		foreach ($errors_for_this_check as $error) {			
			if ($confidence == KNOWN) {
				if(!isset($this->arr_checks_repeated_known_likely_potential[$check_id])){
					$this->nr_known_problems++;		
				}
				$img_type = _AC('error');
				$img_src = "error.png";
			} else if ($confidence == LIKELY) {				
				$img_type = _AC('warning');
				$img_src = "warning.png";
			} else if ($confidence == POTENTIAL) {			
				$img_type = _AC('manual_check');
				$img_src = "info.png";
			}
			
			// only display first 100 chars of $html_code
			if (strlen($error["html_code"]) > 100)
			$html_code = substr($error["html_code"], 0, 100) . " ...";
				
			if ($error["image"] <> '') {
				$height = DISPLAY_PREVIEW_IMAGE_HEIGHT;
				
				if ($error["image_alt"] == '_NOT_DEFINED') $alt = '';
				else if ($error["image_alt"] == '_EMPTY') $alt = 'alt=""';
				else $alt = 'alt="'.$error["image_alt"].'"';
				
				$error_img['img_src'] = $error["image"];
				$error_img['height'] = $height;
			}			
		
			$userDecisionsDAO = new UserDecisionsDAO();
			$row = $userDecisionsDAO->getByUserLinkIDAndLineNumAndColNumAndCheckID($this->user_link_id, $error["line_number"], $error["col_number"], $error['check_id']);
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
			/*
			if (!$row || $row['decision'] == AC_DECISION_FAIL) { // no decision or decision of fail
				if ($confidence == LIKELY) {
					$this->nr_likely_problems++;
				}
				if ($confidence == POTENTIAL) {
					$this->nr_potential_problems++;
				}
			}*/
			//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
			$passed = 'false';
			//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			if(!$row) {
				if(!isset($this->arr_checks_repeated_known_likely_potential[$check_id])){
					if ($confidence == LIKELY) {
						$this->nr_likely_problems++;
					}
					if ($confidence == POTENTIAL) {
						$this->nr_potential_problems++;
					}
				}
				$passed = 'none';
			} else if( $row['decision'] == AC_DECISION_FAIL ) {
				$no_of_errors_affirmed++;
				if(!isset($this->arr_checks_repeated_affirmed[$check_id])){
					$this->nr_affirmed_problems++;    
				}
				$passed = 'false';
			} else if( $row['decision'] == AC_DECISION_PASS ) {
				$no_of_errors_checked++;
				if(!isset($this->arr_checks_repeated_checked[$check_id])){
					$this->nr_checked_warnings++;
				}	
				$passed = 'true';
			} 
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
			//$passed = FALSE;
			
			//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
			$problem_cell['img_src'] = $img_src;
			$problem_cell['line_text'] = _AC('line');
			$problem_cell['line_nr'] = $error['line_number'];
			$problem_cell['col_text'] = _AC('column');
			$problem_cell['col_nr'] = $error["col_number"];
			$problem_cell['check_id'] = $check_id;
			$problem_cell['html_code'] = htmlentities($error["html_code"], ENT_COMPAT, 'UTF-8');
			$problem_cell['css_code'] = $error['css_code'];
			$problem_cell['base_href'] = AC_BASE_HREF;
			$problem_cell['error_img'] = $error_img;
			$problem_cell['test_passed'] = $passed;
			//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			
			if (!$row) {
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				if ( $confidence == KNOWN ) {
					$array[] = $problem_cell;
				}else if ( $confidence == POTENTIAL ) {
					$array_potential[] = $problem_cell;
				}else if ( $confidence == LIKELY ) {
					$array_likely[] = $problem_cell;
				}
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			}
			
			if ($row && $row['decision'] == AC_DECISION_PASS) { // pass decision has been made, display "congrats" icon
				$msg_type = "msg_info";
				$img_type = _AC('passed_decision');
				$img_src = "feedback.gif";
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$array_checked[] = $problem_cell;
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			} else if ( $row['decision'] == AC_DECISION_FAIL) {
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$array_affirmed[] = $problem_cell;
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			}		

			
		}
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		if(!isset($this->arr_checks_repeated_known_likely_potential[$check_id])){
			$this->arr_checks_repeated_known_likely_potential[$check_id] = 1;
		}
		if(!isset($this->arr_checks_repeated_affirmed[$check_id])){
			$this->arr_checks_repeated_affirmed[$check_id] = 1;
		}
		if(!isset($this->arr_checks_repeated_checked[$check_id])){
			$this->arr_checks_repeated_checked[$check_id] = 1;
		}
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
		return array($array, $array_likely, $array_potential, $array_affirmed, $array_checked, $no_of_errors_affirmed, $no_of_errors_checked);
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	}
	


	
}
?>