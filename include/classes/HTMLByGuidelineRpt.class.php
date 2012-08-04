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
// $Id: HTMLRpt.class.php 490 2011-02-04 19:22:32Z cindy $

/**
* HTMLRpt
* Class to generate error report in html format 
* @access	public
* @author	Cindy Qi Li
* @package checker
*/
if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");
include_once(AC_INCLUDE_PATH.'classes/DAO/UserDecisionsDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/AccessibilityRpt.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelineGroupsDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelineSubgroupsDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/ChecksDAO.class.php');

class HTMLByGuidelineRpt extends AccessibilityRpt {

	// all private
	var $gid;                            // Guideline id to report on
	var $errors_by_checks;               // Re-arranged errors table with the array key check_id
	
	var $num_of_no_decisions;            // Number of likely/potential errors that decisions have not been made
	var $num_of_made_decisions;            // Number of likely/potential errors that decisions have been made
	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $num_of_likely_no_decisions;         // Number of likely errors that decisions have not been made
	var $num_of_potential_no_decisions;	 // Number of potential errors that decisions have not been made
	var $num_of_affirmed_problems;
	var $num_of_checked_warnings;
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End
	
	var $num_of_likely_problems_fail;        // Number of likely errors that decisions have not been made
	var $num_of_potential_problems_fail;     // Number of potential errors that decisions have not been made
	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $arr_checks_repeated_known_likely_potential = array();
	var $arr_checks_repeated_affirmed = array();
	var $arr_checks_repeated_checked = array();
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End
	
	var $checksDAO;
	var $guidelineGroupsDAO;
	var $guidelineSubgroupsDAO;
	
	var $html_group =
'<h3>{GROUP_NAME}</h3><br/>
';

	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $html_group_hidden =
'<h3 hidden="true">{GROUP_NAME}</h3><br/>
';	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End

	var $html_subgroup = 
'<h4>{SUBGROUP_NAME}</h4><br/>
';
	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $html_subgroup_hidden = 
'<h4 hidden="true">{SUBGROUP_NAME}</h4><br/>
';
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End
	var $html_checks_table = 
'        <div class="gd_one_check"> 
           <span class="gd_msg">{CHECK_LABEL} {CHECK_ID}: 
              <a href="{BASE_HREF}checker/suggestion.php?id={CHECK_ID}"
                 onclick="AChecker.popup(\'{BASE_HREF}checker/suggestion.php?id={CHECK_ID}\'); return false;" 
                 target="_new">{ERROR}</a>
           </span>

           <div class="gd_question_section">
           {REPAIR}
           {QUESTION}
           </div>
         
           <table id="tb_problems_{SUBGROUP_ID}" class="data static">
           {PROBLEM_TABLE}
           {MAKE_DECISOIN_BUTTON}
           </table>
         </div>
';
	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin 
	var $html_checks_table_hidden = 
'        <div class="gd_one_check" hidden="true"> 
           <span class="gd_msg">{CHECK_LABEL} {CHECK_ID}: 
              <a href="{BASE_HREF}checker/suggestion.php?id={CHECK_ID}"
                 onclick="AChecker.popup(\'{BASE_HREF}checker/suggestion.php?id={CHECK_ID}\'); return false;" 
                 target="_new">{ERROR}</a>
           </span>

           <div class="gd_question_section">
           {REPAIR}
           {QUESTION}
           </div>
         
           <table id="tb_problems_{SUBGROUP_ID}" class="data static">
           {PROBLEM_TABLE}
           {MAKE_DECISOIN_BUTTON}
           </table>
         </div>
';
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End 
	var $html_tr_header =
'           <tr class="gd_th">
             <td width="5%">{PASS_TEXT}<br /><input type="checkbox" class="AC_selectAllCheckBox" id="selectall_{CHECK_ID}" name="selectall_{CHECK_ID}" title="{SELECT_ALL_TEXT}" /></th>
             <td width="95%"><label for="selectall_{CHECK_ID}">{SELECT_ALL_TEXT}</label></th>
           </tr>
';

	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $html_tr_header_hidden =
'           <tr class="gd_th" hidden = "true">
             <td width="5%">{PASS_TEXT}<br /><input type="checkbox" class="AC_selectAllCheckBox" id="selectall_{CHECK_ID}" name="selectall_{CHECK_ID}" title="{SELECT_ALL_TEXT}" /></th>
             <td width="95%"><label for="selectall_{CHECK_ID}">{SELECT_ALL_TEXT}</label></th>
           </tr>
';	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End

	var $html_tr_with_decision =
'           <tr {ROW_SELECTED}>
             <td width="5%">{CHECKBOX}</td>
             <td width="95%" class="AC_problem_detail">{PROBLEM_DETAIL}</td>
           </tr>
';

	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $html_tr_with_decision_hidden =
'           <tr {ROW_SELECTED} hidden = "true">
             <td width="5%">{CHECKBOX}</td>
             <td width="95%" class="AC_problem_detail">{PROBLEM_DETAIL}</td>
           </tr>
';	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End

	var $html_tr_without_decision =
'           <tr>
             <td>{PROBLEM_DETAIL}</td>
           </tr>
';

	var $html_image = 
'<img src="{SRC}" height="{HEIGHT}" border="1" {ALT} />
';

	var $html_problem =
'         <span class="err_type"><img id="msg_icon_{LINE_NUMBER}_{COL_NUMBER}_{CHECK_ID}" src="{BASE_HREF}images/{IMG_SRC}" alt="{IMG_TYPE}" title="{IMG_TYPE}" width="15" height="15" /></span>
         <em>{LABEL_START}{LINE_TEXT} {LINE_NUMBER_TO_SOURCE}, {COL_TEXT} {COL_NUMBER}{LABEL_END}</em>:
         <pre><code class="input">{HTML_CODE}</code></pre>
         {IMAGE}
         <p class="helpwanted">
         </p>
         {CSS_CODE}
';

	var $label_start = '<label for="{CHECKBOX_ID}">';
	var $label_end = '</label>';
	
	var $html_repair = 
'         <span style="font-weight:bold">{REPAIR_LABEL}: </span>{REPAIR_DETAIL}
';

	
	var $html_question = 
'         <table>
           <tr><th>{QUESTION_LABEL}:</th><td>{QUESTION}</td></tr>
           <tr><th>{PASS_LABEL}:</th><td>{PASS_ANSWER}</td></tr>
           <tr><th>{FAIL_LABEL}:</th><td>{FAIL_ANSWER}</td></tr>
         </table>
';
	var $html_make_decision_button = 
'  <tr>
    <td colspan="2">
      <input type="button" value="{LABEL_MAKE_DECISION}" id="AC_btn_make_decision_{SUBGROUP_ID}" />
      <span id="server_response_{SUBGROUP_ID}"></span>
    </td>
  </tr>
';

//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	var $html_reverse_decision_button = 
'  <tr>
    <td colspan="2">
      <input type="button" value="{LABEL_MAKE_DECISION}" id="AC_btn_reverse_decision_{SUBGROUP_ID}" />
      <span id="server_response_{SUBGROUP_ID}"></span>
    </td>
  </tr>
';
//Added by Anirudh Subramanian for AChecker Manual Evaluations End

	var $html_congrats =
'<p><img alt="{CONGRATS_ALT}" src="/images/feedback.gif" />{CONGRATS_TEXT}<br /></p>
';
		
	var $html_source = 
'	<ol class="source">
{SOURCE_CONTENT}
	</ol>
';
	
	var $html_source_line =
'		<li id="line-{LINE_ID}">{LINE}</li>
';
	
	/**
	* public
	* $errors: an array, output of AccessibilityValidator -> getValidationErrorRpt
	* $type: html
	*/
	function HTMLByGuidelineRpt($errors, $gid, $user_link_id = '')
	{
		// run parent constructor
		parent::AccessibilityRpt($errors, $user_link_id);
		
		$this->gid = $gid;
		
		$this->num_of_no_decisions = 0;
		$this->num_of_made_decisions = 0;
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		$this->num_of_likely_no_decisions = 0;
		$this->num_of_potential_no_decisions = 0;
		$this->num_of_affirmed_problems = 0;
		$this->num_of_checked_warnings = 0;
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		
		$this->num_of_likely_problems_fail = 0;
		$this->num_of_potential_problems_fail = 0;
		
		$this->checksDAO = new ChecksDAO();
		$this->guidelineGroupsDAO = new GuidelineGroupsDAO();
		$this->guidelineSubgroupsDAO = new GuidelineSubgroupsDAO();
	}
	
	/**
	* public
	* main process to generate report in html format
	*/
	public function generateRpt()
	{
		global $msg;

		$group_known_problems = "";
		$group_likely_problems = "";
		$group_potential_problems = "";
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		$group_affirmed_problems = "";
		$group_checked_warnings = "";
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		
		
		$this->errors_by_checks = $this->rearrange_errors_array($this->errors);

		// display guideline level checks
		$guidelineLevel_checks = $this->checksDAO->getGuidelineLevelChecks($this->gid);
		
		if (is_array($guidelineLevel_checks))
		{
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
			list($guideline_level_known_problems, $guideline_level_likely_problems, $guideline_level_potential_problems, 
			$guideline_level_affirmed_problems, $guideline_level_checked_warnings, $no_guideline_level_affirmed_problems,$no_guideline_level_checked_warnings, $hide_title_guideline_level_likely_problems, $hide_title_guideline_level_potential_problems, $is_likely_problem_guideline, $is_potential_problem_guideline) =
				$this->generateChecksTable($guidelineLevel_checks);
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations End	
		}
		
		// display named guidelines and their checks 
		$named_groups = $this->guidelineGroupsDAO->getNamedGroupsByGuidelineID($this->gid);
		
		if (is_array($named_groups))
		{
			foreach ($named_groups as $group)
			{				
				$group_level_known_problems = "";
				$group_level_likely_problems = "";
				$group_level_potential_problems = "";
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$group_level_affirmed_problems = "";
				$group_level_checked_warnings = "";
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				
				$subgroup_known_problems = "";
				$subgroup_likely_problems = "";
				$subgroup_potential_problems = "";
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$subgroup_affirmed_problems = "";
				$subgroup_checked_warnings = "";
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		
				// get group level checks: the checks in subgroups without subgroup names
				$groupLevel_checks = $this->checksDAO->getGroupLevelChecks($group['group_id']);
				
				if (is_array($groupLevel_checks))
				{
					//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
					list($group_level_known_problems, $group_level_likely_problems, $group_level_potential_problems, 
					$group_level_affirmed_problems, $group_level_checked_warnings, $no_of_group_level_affirmed_problems, 
					$no_of_group_level_checked_warnings, $hide_title_group_level_likely_problems, $hide_title_group_level_potential_problems, $is_likely_problem_group, $is_potential_problem_group) = 
						$this->generateChecksTable($groupLevel_checks);
					//Modified by Anirudh Subramanian for AChecker Manual Evaluations End	
				}
				
				// display named subgroups and their checks
				$named_subgroups = $this->guidelineSubgroupsDAO->getNamedSubgroupByGroupID($group['group_id']);
				if (is_array($named_subgroups))
				{
					//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
						$no_of_likely_problems_subgroup_title_hidden = 0;
						$no_of_potential_problems_subgroup_title_hidden = 0;
						$no_subgroup_likely_problems = 0;
						$no_subgroup_potential_problems = 0;
						$no_of_subgroup_title_affirmed_problems = 0;
						$no_of_subgroup_title_checked_warnings = 0;
					//Added by Anirudh Subramanian for AChecker Manual Evaluations End
					foreach ($named_subgroups as $subgroup)
					{						
						$subgroup_checks = $this->checksDAO->getChecksBySubgroupID($subgroup['subgroup_id']);
						if (is_array($subgroup_checks))
						{	 
							
							// get html of all the problems in this subgroup
							//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
							list($known_problems, $likely_problems, $potential_problems, 
							$affirmed_problems, $checked_warnings, $no_of_subgroup_level_affirmed_problems, $no_of_subgroup_level_checked_warnings,$hide_title_subgroup_level_likely_problems, $hide_title_subgroup_level_potential_problems,$is_likely_problem_subgroup,$is_potential_problem_subgroup) = 
								$this->generateChecksTable($subgroup_checks);							
							//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
							$subgroup_title = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup);
							//Added  by Anirudh Subramanian for AChecker Manual Evaluations Begin
							$subgroup_title_likely_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup);
							$subgroup_title_potential_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup);
							if($no_of_subgroup_level_affirmed_problems > 0) {
								if($hide_title_subgroup_level_likely_problems) {
									$subgroup_title_likely_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
									$hide_title_subgroup_level_likely_problems = false;
									$no_of_likely_problems_subgroup_title_hidden++;
								}/*else{
									if(!$is_likely_problem_subgroup) {
										$subgroup_title_likely_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
									}
								}*/
								if($hide_title_subgroup_level_potential_problems) {
									$subgroup_title_potential_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
									$hide_title_subgroup_level_potential_problems = false;
									$no_of_potential_problems_subgroup_title_hidden++;
								}/*else{
									if(!$is_potential_problem_subgroup){
										$subgroup_title_potential_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
									}
								}*/
							
								$subgroup_title_affirmed_problems =  str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup);
								$no_of_subgroup_title_affirmed_problems++;
							}else{
								$subgroup_title_affirmed_problems =  str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
							}
							if($no_of_subgroup_level_checked_warnings > 0) {
								if($hide_title_subgroup_level_likely_problems) {
									$subgroup_title_likely_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
									$no_of_likely_problems_subgroup_title_hidden++;
								}/*else{
									if(!$is_likely_problem_subgroup) {
										$subgroup_title_likely_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
									}								
								}*/
								
								if($hide_title_subgroup_level_potential_problems) {
									$subgroup_title_potential_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
									$no_of_potential_problems_subgroup_title_hidden++;
								}/*else{
									if(!$is_potential_problem_subgroup){
										$subgroup_title_potential_problems = str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
									}
								}*/
								$subgroup_title_checked_warnings =  str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup);
								$no_of_subgroup_title_checked_warnings++;
							}else{
								$subgroup_title_checked_warnings =  str_replace("{SUBGROUP_NAME}", _AC($subgroup['name']), $this->html_subgroup_hidden);
							}
							
							//Added by Anirudh Subramanian for AChecker Manual Evaluations End
							if ($known_problems <> "") {
								$subgroup_known_problems .= $subgroup_title.$known_problems;
							} 
							if ($likely_problems <> "") {
								$no_subgroup_likely_problems++;
								$subgroup_likely_problems .= $subgroup_title_likely_problems.$likely_problems;
							}  
							
							if ($potential_problems <> "") {
								$no_subgroup_potential_problems++;
								$subgroup_potential_problems .= $subgroup_title_potential_problems.$potential_problems;
							} 
							//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
							if ($affirmed_problems <> "") {
								$subgroup_affirmed_problems .= $subgroup_title_affirmed_problems.$affirmed_problems;							
							}
							if ($checked_warnings <> "") {
								$subgroup_checked_warnings .= $subgroup_title_checked_warnings.$checked_warnings;
							}
							
							
							//Added by Anirudh Subramanian for AChecker Manual Evaluations End
						}
					} // end of foreach $named_subgroups
				} // end of if $named_subgroups
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$no_of_subgroups_for_group = count($named_subgroups);				
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				
				$group_title = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group);
				$group_title_likely_problems = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group);
				$group_title_potential_problems = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group);
				
				//Added by Anirudh Subramanian for Achecker Manual Evaluations begin
				if($no_of_subgroup_title_affirmed_problems > 0 || $no_of_group_level_affirmed_problems > 0){
					$group_title_affirmed_problems = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group);
				}else{
					$group_title_affirmed_problems = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group_hidden);
				}
				if($no_of_subgroup_title_checked_warnings > 0 || $no_of_group_level_checked_warnings > 0){
					$group_title_checked_warnings = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group);
				}else{
					$group_title_checked_warnings = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group_hidden);
				}
				if($no_of_likely_problems_subgroup_title_hidden == $no_subgroup_likely_problems) {
					$group_title_likely_problems = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group_hidden);
				}
				if($no_of_potential_problems_subgroup_title_hidden == $no_subgroup_potential_problems) {
					$group_title_potential_problems = str_replace("{GROUP_NAME}", _AC($group['name']), $this->html_group_hidden);
				}
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				
				
				if ($group_level_known_problems <> '' || $subgroup_known_problems <> ''){
					$group_known_problems .= $group_title.$group_level_known_problems.$subgroup_known_problems;
				} 
				if ($group_level_likely_problems <> '' || $subgroup_likely_problems <> ''){
					$group_likely_problems .= $group_title_likely_problems.$group_level_likely_problems.$subgroup_likely_problems;
				} 
				if ($group_level_potential_problems <> '' || $subgroup_potential_problems <> ''){
					$group_potential_problems .= $group_title_potential_problems.$group_level_potential_problems.$subgroup_potential_problems;
				}
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				if ($group_level_affirmed_problems <> '' || $subgroup_affirmed_problems <> ''){
					$group_affirmed_problems .= $group_title_affirmed_problems.$group_level_affirmed_problems.$subgroup_affirmed_problems;
				}
				if ($group_level_checked_warnings <> '' || $subgroup_checked_warnings <> ''){
					$group_checked_warnings .= $group_title_checked_warnings.$group_level_checked_warnings.$subgroup_checked_warnings;
				}
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			} // end of foreach $named_groups 	
		} // end of if $named_groups
		
		if ($guideline_level_known_problems <> "" || $group_known_problems <> "") {
			$this->rpt_errors = $guideline_level_known_problems . $group_known_problems;
		} 
		if ($guideline_level_likely_problems <> "" || $group_likely_problems <> "") {
			$this->rpt_likely_problems = $guideline_level_likely_problems . $group_likely_problems;
		} 
		if ($guideline_level_potential_problems <> "" || $group_potential_problems <> "") {
			$this->rpt_potential_problems = $guideline_level_potential_problems . $group_potential_problems;
		}		
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		if ($guideline_level_affirmed_problems <> "" || $group_affirmed_problems <> "") {
			$this->rpt_affirmed_problems = $guideline_level_affirmed_problems . $group_affirmed_problems;
		}
		if ($guideline_level_checked_warnings <> "" || $group_checked_warnings <> "") {
			$this->rpt_checked_warnings = $guideline_level_checked_warnings . $group_checked_warnings;
		}
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		
		if ($this->show_source == 'true')
		{
			$this->generateSourceRpt();
		}
	}
	
	/*
	 * Re-arrang check error array with check_id as the primary key
	 * @param: $errors - the error array
	 * @return: Re-arranged error array
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
	 * Return html of the checks error table
	 * @param $checks_array
	 * @return an array of htmls of (known_problem, likely_problems, potential_problems)
	 */
	private function generateChecksTable($checks_array)
	{
		if (!is_array($checks_array)) return NULL;
		
		$known_problems = "";
		$likely_problems = "";
		$potential_problems = "";
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		$affirmed_problems = "";
		$checked_warnings = "";
		$no_of_checks_checked_warnings = 0;
		$no_of_checks_affirmed_problems = 0;
		$hide_title_potential_problems = false;
		$hide_title_likely_problems = false;
		$no_of_check_errors_potential_problems = 0;
		$no_of_check_errors_likely_problems = 0;
		$no_of_check_errors_likely_affirmed_problems = 0;
		$no_of_check_errors_likely_checked_warnings = 0;
		$no_of_check_errors_potential_affirmed_problems = 0;
		$no_of_check_errors_potential_checked_warnings = 0;
		$is_likely_problem = true;
		$is_potential_problem = true;
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		
		foreach ($checks_array as $check) {
			$html_repair = "";
			$html_question = "";
			$html_make_decision_button = "";
						
			$check_id = $check["check_id"];
			
			// continue with the next check if there is no errors for this check
			if (!array_key_exists($check_id, $this->errors_by_checks) ||
			    array_key_exists($check_id, $this->errors_by_checks) && !is_array($this->errors_by_checks[$check_id])) {
			    	continue;
			}
			
			$row = $this->checksDAO->getCheckByID($check_id);
			
			$repair = _AC($row['how_to_repair']);
			if ($repair <> '') {
				$html_repair = str_replace(array('{REPAIR_LABEL}', '{REPAIR_DETAIL}'), 
				                           array(_AC("repair"), $repair), $this->html_repair);
				
			}
			
			if (($row["confidence"] == LIKELY || $row["confidence"] == POTENTIAL) && $this->allow_set_decision == 'true') {
				$html_question = str_replace(array("{QUESTION_LABEL}", "{QUESTION}",
				                                   "{PASS_LABEL}", "{PASS_ANSWER}",
				                                   "{FAIL_LABEL}", "{FAIL_ANSWER}"), 
				                             array(_AC("question"), _AC($row['question']),
				                                   _AC("pass"), _AC($row['decision_pass']),
				                                   _AC("fail"), _AC($row['decision_fail'])), 
				                             $this->html_question);
			}
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
			list($html_table_rows_for_one_check,$html_table_rows_for_one_check_checked_warnings,$html_table_rows_for_one_check_affirmed_problems,$no_of_errors_affirmed,$no_of_errors_checked,$no_of_errors_for_one_check) = $this->get_table_rows_for_one_check($this->errors_by_checks[$check_id], $check_id, $row["confidence"]);
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
			//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
			if (($row["confidence"] == LIKELY || $row["confidence"] == POTENTIAL) && $this->allow_set_decision == 'true') {
				$html_make_decision_button = str_replace(array("{LABEL_MAKE_DECISION}", "{SUBGROUP_ID}"), 
				                                         array(_AC("make_decision"), $check["subgroupID"]), 
				                                         $this->html_make_decision_button);
				
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$html_reverse_decision_button = str_replace(array("{LABEL_MAKE_DECISION}", "{SUBGROUP_ID}"), 
				                                         array(_AC("reverse_decision"), $check["subgroupID"]), 
				                                         $this->html_reverse_decision_button);
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			}
			
			
			
			$html_one_problem = str_replace(array("{CHECK_LABEL}",
			                                      "{BASE_HREF}", 
			                                      "{CHECK_ID}",
			                                      "{ERROR}",
			                                      "{REPAIR}", 
			                                      "{QUESTION}",
			                                      "{SUBGROUP_ID}",
			                                      "{PROBLEM_TABLE}", 
			                                      "{MAKE_DECISOIN_BUTTON}"), 
			                                array(_AC("check"), 
			                                      AC_BASE_HREF, 
			                                      $check_id,
			                                      _AC($row["err"]),
			                                      $html_repair,
			                                      $html_question,
			                                      $check["subgroupID"],
			                                      $html_table_rows_for_one_check,
			                                      $html_make_decision_button), 
			                                $this->html_checks_table);
			//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
			if($no_of_errors_checked > 0) {
				
				if(($no_of_errors_checked + $no_of_errors_affirmed) == $no_of_errors_for_one_check){
					$html_one_problem = str_replace(array("{CHECK_LABEL}",
			                                      "{BASE_HREF}", 
			                                      "{CHECK_ID}",
			                                      "{ERROR}",
			                                      "{REPAIR}", 
			                                      "{QUESTION}",
			                                      "{SUBGROUP_ID}",
			                                      "{PROBLEM_TABLE}", 
			                                      "{MAKE_DECISOIN_BUTTON}"), 
			                                array(_AC("check"), 
			                                      AC_BASE_HREF, 
			                                      $check_id,
			                                      _AC($row["err"]),
			                                      $html_repair,
			                                      $html_question,
			                                      $check["subgroupID"],
			                                      $html_table_rows_for_one_check,
 			                                      $html_make_decision_button), 
 			                                $this->html_checks_table_hidden);
 				}
				$html_one_problem_checked_warnings = str_replace(array("{CHECK_LABEL}",
									"{BASE_HREF}", 
									"{CHECK_ID}",
									"{ERROR}",
									"{REPAIR}", 
									"{QUESTION}",
									"{SUBGROUP_ID}",
									"{PROBLEM_TABLE}", 
									"{MAKE_DECISOIN_BUTTON}"), 
									array(_AC("check"), 
									AC_BASE_HREF, 
									$check_id,
									_AC($row["err"]),
									$html_repair,
									$html_question,
									$check["subgroupID"],
									$html_table_rows_for_one_check_checked_warnings,
									$html_reverse_decision_button), 
									$this->html_checks_table);
				$no_of_checks_checked_warnings++;		
			} else {
				$html_one_problem_checked_warnings = str_replace(array("{CHECK_LABEL}",
									"{BASE_HREF}", 
									"{CHECK_ID}",
									"{ERROR}",
									"{REPAIR}", 
									"{QUESTION}",
									"{SUBGROUP_ID}",
									"{PROBLEM_TABLE}", 
									"{MAKE_DECISOIN_BUTTON}"), 
									array(_AC("check"), 
									AC_BASE_HREF, 
									$check_id,
									_AC($row["err"]),
									$html_repair,
									$html_question,
									$check["subgroupID"],
									$html_table_rows_for_one_check_checked_warnings,
									$html_reverse_decision_button), 
									$this->html_checks_table_hidden);			
			}
			if($no_of_errors_affirmed > 0){
				if(($no_of_errors_checked + $no_of_errors_affirmed) == $no_of_errors_for_one_check){
					$html_one_problem = str_replace(array("{CHECK_LABEL}",
			                                      "{BASE_HREF}", 
			                                      "{CHECK_ID}",
			                                      "{ERROR}",
			                                      "{REPAIR}", 
			                                      "{QUESTION}",
			                                      "{SUBGROUP_ID}",
			                                      "{PROBLEM_TABLE}", 
			                                      "{MAKE_DECISOIN_BUTTON}"), 
			                                array(_AC("check"), 
			                                      AC_BASE_HREF, 
			                                      $check_id,
			                                      _AC($row["err"]),
			                                      $html_repair,
			                                      $html_question,
			                                      $check["subgroupID"],
			                                      $html_table_rows_for_one_check,
 			                                      $html_make_decision_button), 
 			                                $this->html_checks_table_hidden);
				}
				$html_one_problem_affirmed_problems = str_replace(array("{CHECK_LABEL}",
									"{BASE_HREF}", 
									"{CHECK_ID}",
									"{ERROR}",
									"{REPAIR}", 
									"{QUESTION}",
									"{SUBGROUP_ID}",
									"{PROBLEM_TABLE}", 
									"{MAKE_DECISOIN_BUTTON}"), 
								  array(_AC("check"), 
									AC_BASE_HREF, 
									$check_id,
									_AC($row["err"]),
									$html_repair,
									$html_question,
									$check["subgroupID"],
									$html_table_rows_for_one_check_affirmed_problems,
									$html_reverse_decision_button), 
									$this->html_checks_table);
				$no_of_checks_affirmed_problems++;				
			}else{
				$html_one_problem_affirmed_problems = str_replace(array("{CHECK_LABEL}",
									"{BASE_HREF}", 
									"{CHECK_ID}",
									"{ERROR}",
									"{REPAIR}", 
									"{QUESTION}",
									"{SUBGROUP_ID}",
									"{PROBLEM_TABLE}", 
									"{MAKE_DECISOIN_BUTTON}"), 
								  array(_AC("check"), 
									AC_BASE_HREF, 
									$check_id,
									_AC($row["err"]),
									$html_repair,
									$html_question,
									$check["subgroupID"],
									$html_table_rows_for_one_check_affirmed_problems,
									$html_reverse_decision_button), 
									$this->html_checks_table_hidden);			
			}
			//Added by Anirudh Subramanian for AChecker Manual Evaluations End                                
			                                
			if ($row["confidence"] == KNOWN) {
				$known_problems .= $html_one_problem;
			} else if ($row["confidence"] == LIKELY) {
				$likely_problems .= $html_one_problem;
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$affirmed_problems .= $html_one_problem_affirmed_problems;
				$checked_warnings .= $html_one_problem_checked_warnings;
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			} else if ($row["confidence"] == POTENTIAL) {
				$potential_problems .= $html_one_problem;
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$affirmed_problems .= $html_one_problem_affirmed_problems;
				$checked_warnings .= $html_one_problem_checked_warnings;
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
			}
		}
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		if($no_of_check_errors_potential_problems <> 0){
			if($no_of_check_errors_potential_problems == ($no_of_check_errors_potential_affirmed_problems + $no_of_check_errors_potential_checked_warnings)){
				$hide_title_potential_problems = true;
			}
		}/*else{
			$hide_title_potential_problems = false;
		}*/
		if($no_of_check_errors_likely_problems <> 0){
			if($no_of_check_errors_likely_problems == ($no_of_check_errors_likely_affirmed_problems + $no_of_check_errors_likely_checked_warnings)){
				$hide_title_likely_problems = true;
			}
		}/*else{
			$hide_title_likely_problems = false;
		}*/
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
		return array($known_problems, $likely_problems, $potential_problems,$affirmed_problems,$checked_warnings,$no_of_checks_affirmed_problems,$no_of_checks_checked_warnings, $hide_title_likely_problems, $hide_title_potential_problems,$is_likely_problem,$is_potential_problem);
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	}
	
	/** 
	* private
	* generate html table rows for all errors on one check 
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
		
		$th_row = "";
		$tr_rows = "";
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		$th_row_checked_warnings = "";
		$tr_rows_checked_warnings = "";
		$th_row_affirmed_problems = "";
		$tr_rows_affirmed_problems = "";
		$no_of_errors_checked = 0;
		$no_of_errors_affirmed = 0;
		$no_of_errors_for_one_check = 0;
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		// generate decision section
		if ($this->allow_set_decision == 'true' && $confidence <> KNOWN) {
			$th_row = str_replace(array("{PASS_TEXT}", "{SELECT_ALL_TEXT}", "{CHECK_ID}", "{SELECT_ALL_TEXT}"), 
			                       array(_AC("pass_header"), _AC("select_all"), $check_id, _AC("select_all")), 
			                       $this->html_tr_header);
			//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
			$th_row_checked_warnings = str_replace(array("{PASS_TEXT}", "{SELECT_ALL_TEXT}", "{CHECK_ID}", "{SELECT_ALL_TEXT}"), 
								array(_AC("pass_header"), _AC("select_all"), $check_id, _AC("select_all")), 
								$this->html_tr_header);
			
			$th_row_affirmed_problems = str_replace(array("{PASS_TEXT}", "{SELECT_ALL_TEXT}", "{CHECK_ID}", "{SELECT_ALL_TEXT}"), 
								array(_AC("pass_header"), _AC("select_all"), $check_id, _AC("select_all")), 
								$this->html_tr_header);
			//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		}
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		$likely_problems_fail = 0;
		$potential_problems_fail = 0;
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		foreach ($errors_for_this_check as $error) {
			$html_image = "";
			$msg_type = "";
			$img_type = "";
			$img_src = "";
			
			if ($confidence == KNOWN) {
				if(!isset($this->arr_checks_repeated_known_likely_potential[$check_id])) {
					$this->num_of_errors++;
				}
				$img_type = _AC('error');
				$img_src = "error.png";
			} else if ($confidence == LIKELY) {
				//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
				if(!isset($this->arr_checks_repeated_known_likely_potential[$check_id])) {
					$this->num_of_likely_problems++;
				}
				//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
				
				$img_type = _AC('warning');
				$img_src = "warning.png";
			} else if ($confidence == POTENTIAL) {
				//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
				if(!isset($this->arr_checks_repeated_known_likely_potential[$check_id])) {
					$this->num_of_potential_problems++;
				}
				//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
				$img_type = _AC('manual_check');
				$img_src = "info.png";
			}
			
			if ($this->show_source == 'true') {
				$line_number_to_source = '<a href="checker/index.php#line-'.$error["line_number"].'">'.$error["line_number"].'</a>';
			} else {
				$line_number_to_source = $error["line_number"];
			}
			
			// only display first 100 chars of $html_code
			if (strlen($error["html_code"]) > 100)
			$html_code = substr($error["html_code"], 0, 100) . " ...";
				
			if ($error["image"] <> '') 
			{
				$height = DISPLAY_PREVIEW_IMAGE_HEIGHT;
				
				if ($error["image_alt"] == '_NOT_DEFINED') $alt = '';
				else if ($error["image_alt"] == '_EMPTY') $alt = 'alt=""';
				else $alt = 'alt="'.$error["image_alt"].'"';
				
				$html_image = str_replace(array("{SRC}", "{HEIGHT}", "{ALT}"), 
				                          array($error["image"], $height, $alt), 
				                          $this->html_image);
			}
		
			$userDecisionsDAO = new UserDecisionsDAO();
			$row = $userDecisionsDAO->getByUserLinkIDAndLineNumAndColNumAndCheckID($this->user_link_id, $error["line_number"], $error["col_number"], $error['check_id']);
			
			if (!$row || $row['decision'] == AC_DECISION_FAIL) { // no decision or decision of fail
				if ($confidence == LIKELY) {
					
					$this->num_of_likely_problems_fail++;
					//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
					$likely_problems_fail++;
					//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				}
				if ($confidence == POTENTIAL) {
					$this->num_of_potential_problems_fail++;
					//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
					$potential_problems_fail++;
					//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				}
			}
			
			if ($row && $row['decision'] == AC_DECISION_PASS) { // pass decision has been made, display "congrats" icon
				$msg_type = "msg_info";
				$img_type = _AC('passed_decision');
				$img_src = "feedback.gif";
			}
			
			// generate individual problem string
			$problem_cell = str_replace(array("{IMG_SRC}", 
		                         "{IMG_TYPE}", 
		                         "{LINE_TEXT}", 
		                         "{LINE_NUMBER}", 
			                     "{LINE_NUMBER_TO_SOURCE}",
		                         "{COL_TEXT}", 
		                         "{COL_NUMBER}", 
			                     "{CHECK_ID}",
		                         "{HTML_CODE}",
		                         "{CSS_CODE}", 
		                         "{BASE_HREF}", 
		                         "{IMAGE}"),
		                   array($img_src, 
		                         $img_type,
		                         _AC('line'),
		                         $error['line_number'], 
		                         $line_number_to_source, 
		                         _AC('column'),
		                         $error["col_number"],
		                         $check_id, 
		                         htmlentities($error["html_code"], ENT_COMPAT, 'UTF-8'),
		                         $error['css_code'], 
		                         AC_BASE_HREF, 
		                         $html_image),
		                   $this->html_problem);
		    
		    // compose all <tr> rows
		    // checkboxes only appear 
		    // 1. when user is login. In other words, user can make decision.
		    // 2. likely or potential reports, not error report
			if ($this->allow_set_decision == "true" && $confidence <> KNOWN) {
				$checkbox_name = "d[".$error["line_number"]."_".$error["col_number"]."_".$error["check_id"]."]";
				$checkbox_html = '<input type="checkbox" class="AC_childCheckBox" id="'.$checkbox_name.'" name="'.$checkbox_name.'" value="1" ';
				
				$row_selected = "";
				
				
				$checkbox_html .= '/>';
				
				// associate checkbox label
				$label_start = str_replace("{CHECKBOX_ID}", $checkbox_name, $this->label_start);
				$problem_cell = str_replace(array("{LABEL_START}", "{LABEL_END}"), 
				                            array($label_start, $this->label_end), 
				                            $problem_cell);
				                            
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				if ($row && $row['decision'] == AC_DECISION_PASS){
					$no_of_errors_checked++;
					$no_of_errors_for_one_check++;
					$tr_rows_checked_warnings .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
									array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision);
					$tr_rows .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
				                       array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision_hidden);
				        $tr_rows_affirmed_problems .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
									array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision_hidden);
					//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
					if(!isset($this->arr_checks_repeated_checked[$check_id])){
						$this->num_of_checked_warnings++;	
				        }
					//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				}
				else if ($row['decision'] == AC_DECISION_FAIL){
					$no_of_errors_affirmed++;
					$no_of_errors_for_one_check++;
					$tr_rows_affirmed_problems .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
										array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision);
					$tr_rows .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
				                       array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision_hidden);
				        $tr_rows_checked_warnings .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
									array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision_hidden);               
					//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
					if(!isset($this->arr_checks_repeated_affirmed[$check_id])){
						$this->num_of_affirmed_problems++;
					}
					//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				}else{
					$tr_rows_checked_warnings .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
									array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision_hidden);
					$tr_rows_affirmed_problems .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
									array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision_hidden);
					$tr_rows .= str_replace(array("{ROW_SELECTED}", "{CHECKBOX}", "{PROBLEM_DETAIL}"), 
				                       array($row_selected, $checkbox_html, $problem_cell), $this->html_tr_with_decision);
				        if(!isset($this->arr_checks_repeated_known_likely_potential[$check_id])){
						if ($confidence == LIKELY){
							$this->num_of_likely_no_decisions++;
						}
						if ($confidence == POTENTIAL){
							$this->num_of_potential_no_decisions++;
						}	
				        }               
				}					
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				                       
			} else {
				$problem_cell = str_replace(array("{LABEL_START}", "{LABEL_END}"), 
				                            array("", ""), 
				                            $problem_cell);
				
				$tr_rows .= str_replace(array("{PROBLEM_DETAIL}"), 
				                       array($problem_cell), $this->html_tr_without_decision);
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
		return array($th_row . $tr_rows , $th_row_checked_warnings . $tr_rows_checked_warnings , $th_row_affirmed_problems . $tr_rows_affirmed_problems , $no_of_errors_affirmed , $no_of_errors_checked , $no_of_errors_for_one_check);
	}
	
	// generate $this->rpt_source
	public function generateSourceRpt()
	{
		if (count($this->source_array) == 0) return;
		
		$line_num = 1;
		foreach ($this->source_array as $line)
		{
			$source_content .= str_replace(array("{LINE_ID}","{LINE}"), 
			                               array($line_num, htmlspecialchars($line)), 
			                               $this->html_source_line);
			$line_num++;
		}
		
		$this->rpt_source = str_replace("{SOURCE_CONTENT}", $source_content, $this->html_source);
	}
	
	/**
	* public 
	* return number of likely errors that for guest user
	*/
	public function getNumOfLikelyProblems()
	{
		return $this->num_of_likely_problems;
	}
	
	/**
	* public 
	* return number of potential errors for guest user
	*/
	public function getNumOfPotentialProblems()
	{
		return $this->num_of_potential_problems;
	}
	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	/**
	* public 
	* return number of likely errors that decision have not been made
	*/
	public function getNumOfLikelyNoDecisions()
	{
		return $this->num_of_likely_no_decisions;
	}
	
	/**
	* public 
	* return number of potential errors that decision have not been made
	*/
	public function getNumOfPotentialNoDecisions()
	{
		return $this->num_of_potential_no_decisions;
	}
	
	/**
	* public 
	* return number of checked warnings that decision have not been made
	*/
	public function getNumOfCheckedWarnings()
	{
		return $this->num_of_checked_warnings;
	}
	
	/**
	* public 
	* return number of checked warnings that decision have not been made
	*/
	public function getNumOfAffirmedProblems()
	{
		return $this->num_of_affirmed_problems;
	}
	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations End
	
	/**
	* public 
	* return number of likely/potential errors that decision have not been made
	*/
	public function getNumOfNoDecisions()
	{
		return $this->num_of_no_decisions;
	}

	/**
	* public 
	* return number of likely errors that decision have not been made or have fail decision
	*/
	public function getNumOfLikelyWithFailDecisions()
	{
		return $this->num_of_likely_problems_fail;
	}
	
	/**
	* public 
	* return number of potential errors that decision have not been made or have fail decision
	*/
	public function getNumOfPotentialWithFailDecisions()
	{
		return $this->num_of_potential_problems_fail;
	}
	
	/** 
	* public
	* return error report in html
	* parameters: $errors: errors array
	* author: Cindy Qi Li
	*/
	public static function generateErrorRpt($errors)
	{
		// html error template
		$html_error = 
'<div id="error">
	<h4>{ERROR_MSG_TITLE}</h4>
	{ERROR_DETAIL}
</div>';
	
		$html_error_detail = 
'		<ul>
			<li>{ERROR}</li>
		</ul>
';
		if (!is_array($errors)) return false;
		
		foreach ($errors as $err)
		{
			$error_detail .= str_replace("{ERROR}", _AC($err), $html_error_detail);
		}
			
		return str_replace(array('{ERROR_MSG_TITLE}', '{ERROR_DETAIL}'), 
		                   array(_AC('the_follow_errors_occurred'), $error_detail),
		                   $html_error);
	}
	
	/** 
	* public
	* return success in html
	* parameters: none
	* author: Cindy Qi Li
	*/
	public static function generateSuccessRpt()
	{
		$html_success = 
'<div id="success">Success</div>';
		
		return $html_success;
	}
}
?>