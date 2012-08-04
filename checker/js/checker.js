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
// $Id: checker_results.tmpl.php 460 2011-01-25 18:26:41Z cindy $

// Declare dependencies
/*global window, alert, jQuery*/

var AChecker = AChecker || {};
AChecker.utility = AChecker.utility || {};
AChecker.input = AChecker.input || {};
AChecker.output = AChecker.output || {};

(function ($) {
    // The mapping between the tab IDs and corresponding menu & spinner IDs on the validator input form
    var inputDivMapping = {
        "AC_by_uri": {
            menuID: "AC_menu_by_uri",
            spinnerID: "AC_spinner_by_uri"
        },
        "AC_by_upload": {
            menuID: "AC_menu_by_upload",
            spinnerID: "AC_spinner_by_upload"
        },
        "AC_by_paste": {
            menuID: "AC_menu_by_paste",
            spinnerID: "AC_spinner_by_paste"
        }
    };

    // The mapping between the tab IDs and their corresponding menu IDs on the validator output form
    var outputDivMapping = {
        "AC_errors": {
            menuID: "AC_menu_errors"
        },
        "AC_likely_problems": {
            menuID: "AC_menu_likely_problems" 
        },
        "AC_potential_problems": {
            menuID: "AC_menu_potential_problems"
        },
        "AC_html_validation_result": {
            menuID: "AC_menu_html_validation_result"
        },
        "AC_css_validation_result": {
            menuID: "AC_menu_css_validation_result"
        },
        /*Added by Anirudh Subramanian for AChecker Manual Evaluations Begin*/
	"AC_affirmed_problems": {
            menuID: "AC_menu_affirmed_problems"
        },
	"AC_checked_warnings": {
	    menuID: "AC_menu_checked_warnings"
	}  
	/*Added by Anirudh Subramanian for AChecker Manual Evaluations End*/
    };

    AChecker.output.makeDecisionButtonId = "AC_btn_make_decision_lineNumRpt";
    AChecker.output.sealDivID = "AC_seals_div";

    // Private variables that are only available in this script
    var disableClass = "AC_disabled";
    
    var clickOptionRptGDL = function () {
        $("#guideline_in_checkbox").hide();
        $("#guideline_in_radio").show();
    };
    
    var clickOptionRptLine = function () {
        $("#guideline_in_checkbox").show();
        $("#guideline_in_radio").hide();
    };
    
    /**
     * Display the clicked tab and show/hide "made decision" button according to the displayed tab.
     * @param tab: "validate_uri", "validate_file", "validate_paste"
     *        rptFormat: "by_guideline", "by_line"
     */
    AChecker.input.initialize = function (tab, rptFormat) {
        // initialize input form
        $("#" + inputDivMapping[tab].spinnerID).hide();
        
        AChecker.showDivOutof(tab, inputDivMapping);
        
        // initialize output form
        var div_errors_id = "AC_errors";
        var div_errors = document.getElementById(div_errors_id);

        if (div_errors) {
            // show tab "errors", hide other tabs
            AChecker.showDivOutof(div_errors_id, outputDivMapping);            

            // hide button "make decision" as tab "errors" are selected
            $("#" + AChecker.output.makeDecisionButtonId).hide();
        } else { // no output yet, set focus on "check by uri" input box
            document.getElementById("checkuri").focus();
        }
        
        // link click event on radio buttons on "options" => "report format"
        $("#option_rpt_gdl").click(clickOptionRptGDL);
        $("#option_rpt_line").click(clickOptionRptLine);
        //Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	$("#option_rpt_errtyp").click(clickOptionRptLine);
	//Added by Anirudh Subramanian for Achecker Manual Evaluations End
	
        // initialized the "options" => "guidelines" section, based on the selected "report format"
        if (rptFormat === "by_guideline") {
            $("#option_rpt_gdl").trigger("click");
        } else if (rptFormat === "by_line") {
            $("#option_rpt_line").trigger("click");
        } else if (rptFormat == "by_errortype") { // Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	    $("#option_rpt_errtyp").trigger("click");
	}// Added by Anirudh Subramanian for AChecker Manual Evaluations End
    };
    
    /**
     * Display and activate the selected input div
     * @param divId: the id of the selected input div
     */
    AChecker.input.onClickTab = function (divId) {
        // check if the div is disabled
        if (!$('#' + inputDivMapping[divId].menuID).hasClass(disableClass)) {
            AChecker.showDivOutof(divId, inputDivMapping);
        }
        return false;
    };

    var disableClickablesAndShowSpinner = function (spinnerID) {
        // disable the tabs on the input form by adding css class "AC_disabled"
        // which is detected and processed in AChecker.input.onClickTab()
        for (var key in inputDivMapping) {
            $('#' + inputDivMapping[key].menuID).addClass(disableClass);
        }
        
        $("#" + spinnerID).show();
        document.getElementById(spinnerID).focus();
    };
    
    var enableClickablesAndHideSpinner = function (spinnerID) {
        for (var key in inputDivMapping) {
            $('#' + inputDivMapping[key].menuID).removeClass(disableClass);
        }
        
        $("#" + spinnerID).hide();
    };
    
    /**
     * Validates if a uri is provided
     */
    AChecker.input.validateURI = function () {
        // check uri
        var uri = document.getElementById("checkuri").value;
        if (!uri) {
            alert(AChecker.lang.provide_uri);
            return false;
        }
        disableClickablesAndShowSpinner(inputDivMapping.AC_by_uri.spinnerID);
    };
        
    /**
     * Validates if a html file is provided
     */
    AChecker.input.validateUpload = function () {
        // check file type
        var upload_file = document.getElementById("checkfile").value;
        if (!upload_file || upload_file.trim() === '') {
            alert(AChecker.lang.provide_html_file);
            return false;
        }
        
        var file_extension = upload_file.slice(upload_file.lastIndexOf(".")).toLowerCase();
        if (file_extension !== '.html' && file_extension !== '.htm') {
            alert(AChecker.lang.provide_upload_file);
            return false;
        }
        disableClickablesAndShowSpinner(inputDivMapping.AC_by_upload.spinnerID);
    };

    /**
     * Validates if a html file (paste) is provided
     */
    AChecker.input.validatePaste = function () {
        // check file type
        var paste_html = document.getElementById("checkpaste").value;
        if (!paste_html || paste_html.trim() === '') {
            alert(AChecker.lang.provide_html_input);
            return false;
        }
        disableClickablesAndShowSpinner(inputDivMapping.AC_by_paste.spinnerID);
    };
    
    /**
     * Validates file select menu, sends file & problem type to start_export.php,
     * receives file's path and starts downloading
     */
    AChecker.input.validateFile = function (exportSpinnerID) {
        // check selected items
        var file = document.getElementById("fileselect").value;
        var problem = document.getElementById("problemselect").value;
        
        $("#validate_file_button").val(AChecker.lang.wait);
        
        // show spinner        
        disableClickablesAndShowSpinner(exportSpinnerID);             
        
        // make dataString and send it
        var dataString = 'file=' + file + '&problem=' + problem;
        
        $.ajax({
            type: "POST",
            url: "checker/start_export.php",
            data: dataString,
            cache: false,
            success: function (returned_data) {
                // change button label
                $("#validate_file_button").val(AChecker.lang.get_file);
            
                // enable the clickable tabs/buttons and hide the spinner
                enableClickablesAndHideSpinner(exportSpinnerID);
            
                // change src and start downloading
                var ifrm = document.getElementById("downloadFrame");
                ifrm.src = "checker/download.php?path=" + returned_data;
            },
        
            error: function (xhr, errorType, exception) {
                alert(AChecker.lang.error_occur + exception);

                // enable the clickable tabs/buttons and hide the spinner
                enableClickablesAndHideSpinner(exportSpinnerID);
            }
        });
    };

    /**
     * Display and activate the selected output div
     * @param divId: the id of the selected output div
     */
    AChecker.output.onClickTab = function (divId) {
        window.location.hash = 'output_div';
        AChecker.showDivOutof(divId, outputDivMapping);

        if (divId === "AC_errors" || divId === "AC_html_validation_result" || divId === "AC_css_validation_result") {
            $("#" + AChecker.output.makeDecisionButtonId).hide();
        } else {
            $("#" + AChecker.output.makeDecisionButtonId).show();
        }
        
        return false;
    };
    
    /**
     * private
     * clicking the last unchecked or checked child checkbox should check or uncheck the parent "select all" checkbox
     */
    var undoSelectAll = function (this_child) {
        if ($(this_child).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('checked') === true && this_child.checked === false) {
            $(this_child).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('checked', false);
        }
        
        if (this_child.checked) {
            var flag = true;
            $(this_child).parents('table:eq(0)').find('.AC_childCheckBox').each(
                function () {
		    //Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		    if($(this).is(':hidden') === false){
		    //Added by Anirudh Subramanian for AChecker Manual Evaluations End  
			if (!this.checked) {
			  flag = false;
			}
		    //Added by Anirudh Subramanian for AChecker Manual Evaluations Begin	
		    }
		    //Added by Anirudh Subramanian for AChecker Manual Evaluations End
                }
            );	
            $(this_child).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('checked', flag);
            $(this_child).parent().parent().addClass('selected');
        } else {
            $(this_child).parent().parent().removeClass('selected');
        }
    };
    
    /**
     * private
     * Display server response message - success
     * Called by makeDecisions()
     */
    var displaySuccessMsg = function (btn_make_decision, message) {
        var serverMsgSpan = $(btn_make_decision).parents('tr:eq(0)').find('span[id^="server_response"]');
        serverMsgSpan.addClass("gd_success");
        serverMsgSpan.html(message);
    };
    
    /**
     * private
     * Display server response message - error.
     * Called by makeDecisions()
     */
    var displayErrorMsg = function (btn_make_decision, message) {
        var serverMsgSpan = $(btn_make_decision).parents('tr:eq(0)').find('span[id^="server_response"]');
        serverMsgSpan.addClass("gd_error");
        serverMsgSpan.html(message);
    };
    
    /**
     * private
     * When the pass decision is made, flip the likely/potential icons to green congrats icons;
     * When the pass decision is cancelled, flip green congrats icons to the likely/potential icons.
     * Called by makeDecisions() 
     */
    var flipMsgIcon = function (btn_make_decision) {
        $(btn_make_decision).parents('table:eq(0)').find('.AC_childCheckBox').each(function () {
            // find out the id of message icon
            var checkboxName = $(this).attr('name');
	    //Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	    var checkboxID = $(this).attr('id');
	    //Added by Anirudh Subramanian for AChecker Manual Evaluations End
            var msgIconID = checkboxName.replace('d[', '');
            msgIconID = msgIconID.replace(']', '');
            
            var msgIconIDValue = '#msg_icon_' + msgIconID;
	    
            
            //Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
            var msgIcon = $(this).parents('tr:eq(0)').find(msgIconIDValue);
	    //Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	    
	    if (this.checked) {
                msgIcon.attr('src', 'images/feedback.gif');
                msgIcon.attr('title', AChecker.lang.pass_decision);
                msgIcon.attr('alt', AChecker.lang.pass_decision);
            } else {
                // find out the problem is a likely or a potential
                var inLikelyDiv = msgIcon.parents('div[id="AC_likely_problems"]');
                var inPotentialDiv = msgIcon.parents('div[id="AC_potential_problems"]');

                if (inLikelyDiv.length) { // likely problem
                    msgIcon.attr('src', 'images/warning.png');
                    msgIcon.attr('title', AChecker.lang.warning);
                    msgIcon.attr('alt', AChecker.lang.warning);
                } 
                if (inPotentialDiv.length) { // potential problem
                    msgIcon.attr('src', 'images/info.png');
                    msgIcon.attr('title', AChecker.lang.manual_check);
                    msgIcon.attr('alt', AChecker.lang.manual_check);
                } 
            }
        });
    };
    
    /**
     * private
     * Modify the number of problems on the tab bar. 
     * Called by makeDecisions()
     */
    var changeNumOfProblems = function (noOfRedundantChecks,presentTab) {
        var divsToLookup = ["AC_likely_problems", "AC_potential_problems"];
        var divIDsToUpdateErrorNum = ["AC_num_of_likely", "AC_num_of_potential"];
        var arrayNumOfProblems = new Array(2);
        // decide the tab to work on and number of problems
        for (var i in divsToLookup) {
	    if(presentTab === divsToLookup[i]){
		
		var currentDiv = $('div[id="' + divsToLookup[i] + '"]');
		// find number of all problems (checkboxes) in the current tab
		var total = $(currentDiv).find("input[class=AC_childCheckBox]").length;
		var checked = $(currentDiv).find("input[class=AC_childCheckBox]:checked").length;
		var hidden = $(currentDiv).find("input[class=AC_childCheckBox]:hidden").length;
		
		var numOfProblems = total - hidden;
		
		
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
		if(divsToLookup[i] === 'AC_likely_problems'){
		    arrayNumOfProblems[i] = numOfProblems + noOfRedundantChecks[0];
		}else{
		    arrayNumOfProblems[i] = numOfProblems + noOfRedundantChecks[1];
		}
		$("#" + divIDsToUpdateErrorNum[i]).html(arrayNumOfProblems[i]);
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	    }else{
		arrayNumOfProblems[i] = 1;
	    }      
	}
        return arrayNumOfProblems;
    };
    
    
    /**
     * private
     * Modify the number of problems on the tab bar. 
     * Called by makeDecisions()
     */
    var changeNumOfProblemsLikelyPotential = function (btn_make_decision) {
        var divsToLookup = ["AC_likely_problems", "AC_potential_problems"];
        var divIDsToUpdateErrorNum = ["AC_num_of_likely", "AC_num_of_potential"];
        var arrayNumOfProblems = new Array(2);
	// decide the tab to work on and number of problems
        for (var i in divsToLookup) {
		if($(btn_make_decision).parents('.gd_one_check').parent().attr('id') === divsToLookup[i]){
		    var currentDiv = $('div[id="' + divsToLookup[i] + '"]');
		    var toBeHidden = $(btn_make_decision).parents('table:eq(0)').find('.AC_childCheckBox').length;
		    var alreadyHidden = $(btn_make_decision).parents('table:eq(0)').find("input[class=AC_childCheckBox]:hidden").length;
		    var checked = $(btn_make_decision).parents('table:eq(0)').find("input[class=AC_childCheckBox]:checked").length;
		    var hidden = toBeHidden - alreadyHidden;
		    var currentNumber = $("#" + divIDsToUpdateErrorNum[i]).html();
		    var numOfProblems = currentNumber - hidden;
		    arrayNumOfProblems[i] = numOfProblems;
		    
		    $("#" + divIDsToUpdateErrorNum[i]).html(arrayNumOfProblems[i]);
		    
		    var errorsAffirmed = hidden - checked;
		    var errorsChecked = checked;
		    var currentNumberAffirmed = $("#AC_num_of_affirmed").html();
		    var currentNumberChecked = $("#AC_num_of_checked").html();
		    
		    currentNumberChecked = parseInt(currentNumberChecked);
		    currentNumberAffirmed = parseInt(currentNumberAffirmed);
		    
		    $("#AC_num_of_affirmed").html(currentNumberAffirmed + errorsAffirmed);
		    $("#AC_num_of_checked").html(currentNumberChecked + errorsChecked);
		}else{
		    arrayNumOfProblems[i] = $("#" + divIDsToUpdateErrorNum[i]).html();
		}
      
	}
        return arrayNumOfProblems;
    };
    
    /**
     * private
     * Modify the number of problems on the tab bar. 
     * Called by makeDecisions()
     */
    var changeNumOfProblemsCheckedAffirmed = function (btn_make_decision,problem_type) {
        var divsToLookup = ["AC_affirmed_problems", "AC_checked_warnings"];
        var divIDsToUpdateErrorNum = ["AC_num_of_affirmed", "AC_num_of_checked"];
        var arrayNumOfProblems = new Array(2);
	arrayNumOfProblems[0] = parseInt($("AC_num_of_likely").html());
	arrayNumOfProblems[1] = parseInt($("AC_num_of_potential").html())
	// decide the tab to work on and number of problems
        for (var i in divsToLookup) {
		if($(btn_make_decision).parents('.gd_one_check').parent().attr('id') === divsToLookup[i]){
		    var currentDiv = $('div[id="' + divsToLookup[i] + '"]');
		    var toBeHidden = $(btn_make_decision).parents('table:eq(0)').find('.AC_childCheckBox').length;
		    var checked = $(btn_make_decision).parents('table:eq(0)').find("input[class=AC_childCheckBox]:checked").length;
		    var currentNumber = $("#" + divIDsToUpdateErrorNum[i]).html();
		    currentNumber = parseInt(currentNumber);
		    var numOfProblems = currentNumber - checked;
		    $(btn_make_decision).parents('table:eq(0)').find("input[class=AC_childCheckBox]").attr('checked',false);
		    $("#" + divIDsToUpdateErrorNum[i]).html(numOfProblems);
		    if(problem_type === 'AC_likely_problems'){
			var currentNumberLikely = $("#AC_num_of_likely").html();
			currentNumberLikely = parseInt(currentNumberLikely);
			
			var numOfProblemsLikely = currentNumberLikely + checked;		      
			$("#AC_num_of_likely").html(numOfProblemsLikely);			
			arrayNumOfProblems[0] = numOfProblemsLikely;
		    }else{
			var currentNumberPotential = $("#AC_num_of_potential").html();
			currentNumberPotential = parseInt(currentNumberPotential);
			
			var numOfProblemsPotential = currentNumberPotential + checked;
			$("#AC_num_of_potential").html(numOfProblemsPotential);
			
			arrayNumOfProblems[1] = numOfProblemsPotential;
		    }
		}
	}
        return arrayNumOfProblems;
    };
    
    /**
     * retrieve and display seal
     * Called by makeDecisions()
     */
    var showSeal = function (btn_make_decision) {
        var ajaxPostStr = "uri" + "=" + $.URLEncode($('input[name="uri"]').attr('value')) + "&" + 
                          "jsessionid" + "=" + $('input[name="jsessionid"]').attr('value') + "&" +
                          "gids[]=" + $('input[name="radio_gid[]"][type="hidden"]').attr('value');

        $.ajax({
            type: "POST",
            url: "checker/get_seal_html.php",
            data: ajaxPostStr,
            
            success: function (data) {
                // display seal
                $('#' + AChecker.output.sealDivID).html(data);

                // inform the user that the seal has been issued and displayed at the top seal container
                var serverMsgSpan = $(btn_make_decision).parents('tr:eq(0)').find('span[id^="server_response"]');
                serverMsgSpan.html(serverMsgSpan.text() + AChecker.lang.get_seal);
            }
        });
    };
    /**
     * Method to count no of errors under a check
     * Called by reverseDecision()
     * Added by Anirudh Subramanian for AChecker Manual Evaluations
     */
    var countHiddenErrorSiblings = function (checkboxToBeHidden) {
	var noOfSiblingErrorsVisible = 0;
	checkboxToBeHidden.parents('table:eq(0)').find('.AC_childCheckBox').each( function() {
		if($(this).parents('tr:eq(0)').is(':hidden') === false){
			
			noOfSiblingErrorsVisible++;
		}
	});
	return noOfSiblingErrorsVisible;
    };
    
    /**
     * Method to count no of checks visible under a subgroup
     * Called by makeDecision()
     * Added by Anirudh Subramanian for AChecker Manual Evaluations
    */
    var countHiddenSubGroupChecks = function (group_check) {
	var noOfSiblingChecksVisible = 0;
	$(group_check).siblings('.gd_one_check').each(function(){
		if($("input[id=option_rpt_gdl]").attr('checked')){
			if($(this).prevAll('h4').first().text() === $(group_check).prevAll('h4').first().text()){
				if($(this).is(':hidden') === false){
					noOfSiblingChecksVisible++;
				}					
			}
		}else{
			if($(this).prevAll('h3').first().text() === $(group_check).prevAll('h3').first().text()){
				if($(this).is(':hidden') === false){
					noOfSiblingChecksVisible++;
				}					
			}
		}  
	});
	return noOfSiblingChecksVisible;
    };
    
    /**
     * Method to count no of subgroups visible under a group
     * Called by makeDecision()
     * Added by Anirudh Subramanian for AChecker Manual Evaluations 
     */ 
    var countHiddenSubGroups = function (group_check) {
      var noOfSiblingSubGroupsVisible = 0;
      $(group_check).siblings('.gd_one_check').each(function(){
		if($(this).prevAll('h3').first().text() === $(group_check).prevAll('h3').first().text()){
			if($(this).prevAll('h4').first().is(':hidden') === false){
				noOfSiblingSubGroupsVisible++;
			}
		}
      });
      return noOfSiblingSubGroupsVisible;
    };
    //Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
    /**
     * shows error groups in appropriate tabs when the user does manual evaluation 
     */
    
    var showErrorGroup = function (child_checkbox) {
	$(child_checkbox).parents('tr:eq(0)').removeClass('selected');
	$(child_checkbox).attr('checked',false);
	$(child_checkbox).parents('tr:eq(0)').show();
	$(child_checkbox).parents('.gd_one_check').find('.AC_selectAllCheckBox').attr('checked', false); 
	$(child_checkbox).parents('.gd_one_check').show();
	$(child_checkbox).parents('tr:eq(0)').parents('.gd_one_check').prevAll('h3').first().show();
	if($("input[id=option_rpt_gdl]").attr('checked')) {
		$(child_checkbox).parents('tr:eq(0)').parents('.gd_one_check').prevAll('h4').first().show();	  
	}      
    }
    
    
    
    
    
    
    //Added by Anirudh Subramanian for AChecker Manual Evaluations End
    
    /**
     * Click event on "reverse decision" buttons. It does:
     * 1.hides the decision from affirmed problems /checked warnings section
     * 2.renders it in the likely problems / potential problems section
     * Added by Anirudh Subramanian for AChecker Manual Evaluations
     */
    
    
    var reverseDecision = function (btn_make_decision) {
	var ajaxPostStr = "";
	var noOfSiblingChecksSubgroupVisible = 0;
	var noOfSiblingSubGroupsVisible = 0;
	var selectAllBoxId = $(btn_make_decision).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('id');
	var isInsideLikelyProblems = false;
	var isInsidePotentialProblems = false;
	var arrayNumOfProblems = new Array(2);
	$(btn_make_decision).parents('table:eq(0)').parents('.gd_one_check').parent().find('.AC_selectAllCheckBox').each( function() {
	    if($(this).attr('id') === selectAllBoxId) {
		$(this).parents('table:eq(0)').find('.AC_childCheckBox').each(function () {    
			if (this.checked) {
			    ajaxPostStr += $(this).attr('name') + "=" + "N" + "&";
			    var checkboxToBeHidden = $(this);
			    var childCheckBoxIdShow = $(this).attr('name');

			    if($("input[id=option_rpt_gdl]").attr('checked')){
				$(this).parents('tr:eq(0)').hide();
				//$(this).attr('checked',false);
				noOfSiblingErrorsVisible = countHiddenErrorSiblings(checkboxToBeHidden);
				
				if(noOfSiblingErrorsVisible === 0){

					$(this).parents('.gd_one_check').hide();
					noOfSiblingChecksSubgroupVisible = countHiddenSubGroupChecks($(this).parents('.gd_one_check'));				
					if(noOfSiblingChecksSubgroupVisible === 0){
						$(this).parents('.gd_one_check').prevAll('h4').first().hide();
						noOfSiblingSubGroupsVisible = countHiddenSubGroups($(this).parents('.gd_one_check'));
						if(noOfSiblingSubGroupsVisible === 0){
							$(this).parents('.gd_one_check').prevAll('h3').first().hide();					
						}
					}				  
				}
					  
				
				$(this).parents('.gd_one_check').parent().siblings('#AC_likely_problems').find('.AC_childCheckBox').each(function (){
					if($(this).attr('name') === childCheckBoxIdShow){
						isInsideLikelyProblems = true;
						showErrorGroup(this);						
					}
				});
				$(this).parents('.gd_one_check').parent().siblings('#AC_potential_problems').find('.AC_childCheckBox').each(function (){
					if($(this).attr('name') === childCheckBoxIdShow){
						isInsidePotentialProblems = true;
						showErrorGroup(this);
					}
				});
			    }else{
			      
				$(this).parents('tr:eq(0)').hide();  
				noOfSiblingErrorsVisible = countHiddenErrorSiblings(checkboxToBeHidden);
				if(noOfSiblingErrorsVisible === 0){

					$(this).parents('.gd_one_check').hide();
					noOfSiblingChecksSubgroupVisible = countHiddenSubGroupChecks($(this).parents('.gd_one_check'));				
					if(noOfSiblingChecksSubgroupVisible === 0){
						$(this).parents('.gd_one_check').prevAll('h3').first().hide();				  
					}				  
			    
				    
				}      
				$(this).parents('.gd_one_check').parent().siblings('#AC_likely_problems').find('.AC_childCheckBox').each(function (){
					if($(this).attr('name') === childCheckBoxIdShow){
						isInsideLikelyProblems = true;
						showErrorGroup(this);
					}
				});
				$(this).parents('.gd_one_check').parent().siblings('#AC_potential_problems').find('.AC_childCheckBox').each(function (){
					if($(this).attr('name') === childCheckBoxIdShow){
						
						isInsidePotentialProblems = true;
						showErrorGroup(this);
					}
				});
			    }  
		    
			}
			});    
			  
		}
	});
	
	
	//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	ajaxPostStr += "uri" + "=" + $.URLEncode($('input[name="uri"]').attr('value')) + "&" + 
            "output" + "=" + $('input[name="output"]').attr('value') + "&" +
            "jsessionid" + "=" + $('input[name="jsessionid"]').attr('value');
        $.ajax({
            type: "POST",
            url: "checker/save_decisions.php",
            data: ajaxPostStr,

            success: function (data) {
                // display success message
                displaySuccessMsg(btn_make_decision, data);
                
                // flip icon to green pass icon
                flipMsgIcon(btn_make_decision);
            
                
		//Modified by Anirudh Subramanian for AChecker Manual Evaluations End
		// modify and store the number of problems on the tab bar
		if(isInsideLikelyProblems){
			arrayNumOfProblems = changeNumOfProblemsCheckedAffirmed(btn_make_decision, 'AC_likely_problems');
		}else{
			arrayNumOfProblems = changeNumOfProblemsCheckedAffirmed(btn_make_decision, 'AC_potential_problems' );
		}
                // No more likely problems, display congrats message on "likely problems" tab
                if (arrayNumOfProblems[0] === 0) {
                    $("#AC_congrats_msg_for_likely").html(AChecker.lang.congrats_likely);
                    $("#AC_congrats_msg_for_likely").addClass("congrats_msg");
                } else {
                    $("#AC_congrats_msg_for_likely").html("");
                    $("#AC_congrats_msg_for_likely").removeClass("congrats_msg");
                }
                
                // No more potential problems, display congrats message on "potential problems" tab
                if (arrayNumOfProblems[1] === 0) {
                    $("#AC_congrats_msg_for_potential").html(AChecker.lang.congrats_potential);
                    $("#AC_congrats_msg_for_potential").addClass("congrats_msg");
                } else {
                    $("#AC_congrats_msg_for_potential").html("");
                    $("#AC_congrats_msg_for_potential").removeClass("congrats_msg");
                }
                
                // if all errors, likely, potential problems are 0, retrieve seal
                if (arrayNumOfProblems[0] === 0 && arrayNumOfProblems[1] === 0) {
                    // find the number of errors
                    var numOfErrors = $('#AC_num_of_errors').text();

                    if (numOfErrors === 0) {
                        showSeal(btn_make_decision);
                    }
                } else {
                    $('#' + AChecker.output.sealDivID).html("");
                }
            }, 
            
            error: function (xhr, errorType, exception) {
                // display error message
                displayErrorMsg(btn_make_decision, $(xhr.responseText).text());
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		
		var selectAllCheckBoxId = $(btn_make_decision).parents('tr:eq(0)').parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('id');
		$(btn_make_decision).parents('.gd_one_check').siblings().find('input[id^="AC_btn_make_decision"]').each( function(){	
			var currSelectAllCheckBoxId = $(this).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('id');
			if(selectAllCheckBoxId === currSelectAllCheckBoxId){
				displayErrorMsg(this, $(xhr.responseText).text());
			}
		});
		
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
            }
	});    
            //Added by Anirudh Subramanian for AChecker Manual Evaluations End
    };
    /**
     * Click event on "make decision" buttons. It does:
     * 1. ajax post to save into db
     * 2. prompt success or error msg returned from server besides the "make decision" button
     * 3. flip warning/info icons besides problems with pass decisons made to green pass icons
     * 4. change the number of problems on the tab bar
     * 5. when the number of problems is reduced to 0, 
     *    ajax request the seal html from server and display it in seal container
     */
    var makeDecision = function (btn_make_decision) {
        var ajaxPostStr = "";
	var makeDecisionCheckboxID = $(btn_make_decision).parents('table:eq(0)').find('.AC_childCheckBox').attr('id');
	var noOfSiblingChecksSubgroupVisible = 0;
	var noOfSiblingSubGroupsVisible = 0;
	var selectAllBoxId = $(btn_make_decision).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('id');
	// modify and store the number of problems on the tab bar
	var arrayNumOfProblems = changeNumOfProblemsLikelyPotential(btn_make_decision);
	$(btn_make_decision).parents('table:eq(0)').parents('.gd_one_check').parent().find('.AC_selectAllCheckBox').each( function() {
	    if($(this).attr('id') === selectAllBoxId) {
		$(this).parents('table:eq(0)').find('.AC_childCheckBox').each(function () {
		    if (this.checked) { 
			ajaxPostStr += $(this).attr('name') + "=" + "P" + "&";
			//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
			var checkboxToBeHidden = $(this);
			var childCheckBoxIdShow = $(this).attr('name');
			
			if($("input[id=option_rpt_gdl]").attr('checked')){
						$(this).parents('tr:eq(0)').hide();
						$(this).attr('checked',false);
						$(this).parents('.gd_one_check').hide();
						noOfSiblingChecksSubgroupVisible = countHiddenSubGroupChecks($(this).parents('.gd_one_check'));				
						if(noOfSiblingChecksSubgroupVisible === 0){
							$(this).parents('.gd_one_check').prevAll('h4').first().hide();
							noOfSiblingSubGroupsVisible = countHiddenSubGroups($(this).parents('.gd_one_check'));
							
							if(noOfSiblingSubGroupsVisible === 0) {
								$(this).parents('.gd_one_check').prevAll('h3').first().hide();					
							}				  
						}
				$(this).parents('.gd_one_check').parent().siblings('#AC_checked_warnings').find('.AC_childCheckBox').each(function () {
					if($(this).attr('name') === childCheckBoxIdShow){
						showErrorGroup(this);
					}
				});
			}else{
				$(this).parents('tr:eq(0)').hide();
				$(this).attr('checked',false);
				$(this).parents('.gd_one_check').hide();
				noOfSiblingChecksSubgroupVisible = countHiddenSubGroupChecks($(this).parents('.gd_one_check'));				
				if(noOfSiblingChecksSubgroupVisible === 0){
					$(this).parents('.gd_one_check').prevAll('h3').first().hide();				  
				}
				$(this).parents('.gd_one_check').parent().siblings('#AC_checked_warnings').find('.AC_childCheckBox').each(function (){
					if($(this).attr('name') === childCheckBoxIdShow){
						showErrorGroup(this);
						
					}
				});
			}
			//Added by Anirudh Subramanian for AChecker Manual Evaluations End
		    }else{
			if($("input[id=option_rpt_gdl]").attr('checked')) {
				$(this).parents('tr:eq(0)').hide();
				$(this).attr('checked',false);
				var checkboxToBeHidden = $(this);
				var childCheckBoxIdShow = $(this).attr('id');
				noOfSiblingChecksSubgroupVisible = 0;
				noOfSiblingSubGroupsVisible = 0;
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$(this).parents('.gd_one_check').parent().find('.gd_one_check').each(function() {
					if($(this).find('.gd_msg').text() === checkboxToBeHidden.parents('tr:eq(0)').parents('.gd_one_check').find('.gd_msg').text()) {
						$(this).hide();
						noOfSiblingChecksSubgroupVisible = countHiddenSubGroupChecks(this);				
						if(noOfSiblingChecksSubgroupVisible === 0) {
							$(this).prevAll('h4').first().hide();
							noOfSiblingSubGroupsVisible = countHiddenSubGroups(this);
							if(noOfSiblingSubGroupsVisible === 0){
								$(this).prevAll('h3').first().hide();					
							}				  
						}
					}	
				});
				$(this).parents('.gd_one_check').parent().siblings('#AC_affirmed_problems').find('.AC_childCheckBox').each(function () {
					if($(this).attr('id') === childCheckBoxIdShow){
						showErrorGroup(this);
					}
				});
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				ajaxPostStr += $(this).attr('name') + "=" + "F" + "&";
			}else{
				var checkboxToBeHidden = $(this);
				var childCheckBoxIdShow = $(this).attr('name');
				noOfSiblingChecksSubgroupVisible = 0;
				//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
				$(this).parents('tr:eq(0)').hide();
				$(this).attr('checked',false);
				$(this).parents('.gd_one_check').hide();
				noOfSiblingChecksSubgroupVisible = countHiddenSubGroupChecks($(this).parents('.gd_one_check'));				
				if(noOfSiblingChecksSubgroupVisible === 0) {
					$(this).parents('.gd_one_check').prevAll('h3').first().hide();				  
				}
				$(this).parents('.gd_one_check').parent().siblings('#AC_affirmed_problems').find('.AC_childCheckBox').each(function () {
					if($(this).attr('name') === childCheckBoxIdShow) {
						showErrorGroup(this);					  
					}
				});
				//Added by Anirudh Subramanian for AChecker Manual Evaluations End
				ajaxPostStr += $(this).attr('name') + "=" + "F" + "&";
			}
		    }
		});
		
	    }
	});

        ajaxPostStr += "uri" + "=" + $.URLEncode($('input[name="uri"]').attr('value')) + "&" + 
            "output" + "=" + $('input[name="output"]').attr('value') + "&" +
            "jsessionid" + "=" + $('input[name="jsessionid"]').attr('value');
        $.ajax({
            type: "POST",
            url: "checker/save_decisions.php",
            data: ajaxPostStr,

            success: function (data) {
                // display success message
                displaySuccessMsg(btn_make_decision, data);
                
                // flip icon to green pass icon
                flipMsgIcon(btn_make_decision);
		
                
                // No more likely problems, display congrats message on "likely problems" tab
                if (arrayNumOfProblems[0] === 0) {
                    $("#AC_congrats_msg_for_likely").html(AChecker.lang.congrats_likely);
                    $("#AC_congrats_msg_for_likely").addClass("congrats_msg");
                } else {
                    $("#AC_congrats_msg_for_likely").html("");
                    $("#AC_congrats_msg_for_likely").removeClass("congrats_msg");
                }
                
                // No more potential problems, display congrats message on "potential problems" tab
                if (arrayNumOfProblems[1] === 0) {
                    $("#AC_congrats_msg_for_potential").html(AChecker.lang.congrats_potential);
                    $("#AC_congrats_msg_for_potential").addClass("congrats_msg");
                } else {
                    $("#AC_congrats_msg_for_potential").html("");
                    $("#AC_congrats_msg_for_potential").removeClass("congrats_msg");
                }
                
                // if all errors, likely, potential problems are 0, retrieve seal
                if (arrayNumOfProblems[0] === 0 && arrayNumOfProblems[1] === 0) {
                    // find the number of errors
                    var numOfErrors = $('#AC_num_of_errors').text();

                    if (numOfErrors === 0) {
                        showSeal(btn_make_decision);
                    }
                } else {
                    $('#' + AChecker.output.sealDivID).html("");
                }
            }, 
            
            error: function (xhr, errorType, exception) {
                // display error message
                displayErrorMsg(btn_make_decision, $(xhr.responseText).text());
		//Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
		var selectAllCheckBoxId = $(btn_make_decision).parents('tr:eq(0)').parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('id');
		$(btn_make_decision).parents('.gd_one_check').siblings().find('input[id^="AC_btn_make_decision"]').each( function(){	
			var currSelectAllCheckBoxId = $(this).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('id');
			if(selectAllCheckBoxId === currSelectAllCheckBoxId){
				displayErrorMsg(this, $(xhr.responseText).text());
			}
		});		
		//Added by Anirudh Subramanian for AChecker Manual Evaluations End
            }
        });
    };

    $(document).ready(
        function () {
	    
            //clicking the "select all" checkbox should check or uncheck all child checkboxes	    
	    //Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
            $(".AC_selectAllCheckBox").click(function () {
		var selectAllCheckId = $(this).attr('id');
                var table = $(this).parents('table:eq(0)');
                //$(table).find('.AC_childCheckBox').attr('checked', this.checked);
                $(table).find('.AC_childCheckBox').each(function(){
			if($(this).is(':hidden') === false){
				$(this).attr('checked', this.checked);			  
			}
			
		});
		if (this.checked) {
                    $(table).find('tr').addClass("selected");
                } else {
                    $(table).find('tr').removeClass("selected");
                }
                //Changed temporarily
                var checkState = this.checked;
		//Changed temporarily
                $(this).parents('.gd_one_check').parent().find(".AC_selectAllCheckBox").each(function() {
			if ($(this).attr('id') === selectAllCheckId) {
				table = $(this).parents('table:eq(0)');
				$(this).attr('checked',checkState);
				//Modified temporarily
				//$(table).find('.AC_childCheckBox').attr('checked', checkState);
				$(table).find('.AC_childCheckBox').each(function(){
					if($(this).is(':hidden') === false){
						$(this).attr('checked', checkState);			  
					}
				});
				//Modified temporarily
				if (this.checked) {
					$(table).find('tr').addClass("selected");
				} else {
					$(table).find('tr').removeClass("selected");
				}
			}
		});
            });
	    //Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	    
            //clicking the last unchecked or checked checkbox should check or uncheck the parent "select all" checkbox        
	    //Modified by Anirudh Subramanian for AChecker Manual Evaluations Begin
	    $(".AC_childCheckBox").click(function() {
		var childCheckBoxId = $(this).attr('id');
		var childCheckBoxIsChecked = $(this).attr('checked');
		var selectAllCheckBoxId = $(this).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('id');
		$(this).parents('table:eq(0)').parents('.gd_one_check').parent().find('.AC_selectAllCheckBox').each( function() {
			if ( selectAllCheckBoxId === $(this).attr('id')) {
				$(this).parents('table:eq(0)').find('.AC_childCheckBox').each( function() {
					if( $(this).attr('id') === childCheckBoxId ) {
						$(this).attr('checked',childCheckBoxIsChecked);
						undoSelectAll(this);
					}
				});
			}
		});
		
	    });
	    
	    $(".AC_problem_detail").click(function() {
		var childCheckBoxId = "";
		var childCheckBoxIsChecked = "";
		$(this).siblings().find('.AC_childCheckBox').each(
			function () {
				childCheckBoxId = $(this).attr('id');
				$(this).attr('checked', !this.checked);
				childCheckBoxIsChecked = $(this).attr('checked');
				undoSelectAll(this);
			}
                );
		var selectAllCheckBoxId = $(this).parents('table:eq(0)').find('.AC_selectAllCheckBox').attr('id');
		$(this).parents('table:eq(0)').parents('.gd_one_check').parent().find('.AC_selectAllCheckBox').each( function() {			
			if ( selectAllCheckBoxId === $(this).attr('id') ) {
				$(this).parents('table:eq(0)').find('.AC_childCheckBox').each( function() {
					if( $(this).attr('id') === childCheckBoxId ) {
						if($(this).attr('checked') != childCheckBoxIsChecked) {
							$(this).attr('checked',childCheckBoxIsChecked);
						}
						undoSelectAll(this);
					}
				});
			}
		});
		
	    });	    
	    //Modified by Anirudh Subramanian for AChecker Manual Evaluations End
	    
            // clicking on "make decision" button
            $('input[id^="AC_btn_make_decision"]').click(function () {
		 makeDecision(this);
            });

	    //Added by Anirudh Subramanian for AChecker Manual Evaluations Begin
	    
	    $('input[id^="AC_btn_reverse_decision"]').click(function () {
		 reverseDecision(this);
            });
	    
	    //Added by Anirudh Subramanian for AChecker Manual Evaluations End
        }
    );
})(jQuery);
