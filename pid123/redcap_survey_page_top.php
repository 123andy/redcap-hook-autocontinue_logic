<?php

/**
Project-specific Survey Page Top

THIS IS AN EXAMPLE PROJECT-SPECIFIC HOOK FOR ADDING CONDITIONAL LOGIC TO SURVEYS USING THE 'AUTO-CONTINUE' FEATURE

CURRENTLY THE LOGIC IS NOT EVENT-AWARE, BUT YOU COULD MODIFY YOUR LOGIC TO GET THE EVENTNAME FROM THE $event_id IN THE HOOK (check out LogicTester::logicPrependEventName if you're interested...)

VARIABLES IN SCOPE:
$project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id

**/

// THIS IS AN ARRAY OF FORM_NAMES FOLLOWED BY CONDIITONAL LOGIC - SIMILAR TO THE SURVEY QUEUE
$auto_continue_logic = array(
	//instrument name => //logic
	'pregnancy_form' => "[enrollment_arm_1][pregnancy] = '1' AND [enrollment_arm_1][gender] = '2'",
	'family_members_your_generation_siblings' => "[enrollment_arm_1][siblings_exist] = '1'",
	'family_members_children' => "[enrollment_arm_1][have_children] = '1'",
	'family_members_other_affected_relatives' =>  "[enrollment_arm_1][have_family_info]='1'",
	'family_members_grandchildren' => "[enrollment_arm_1][grandchildren_exist]='1'"
);

// NEED TO OBTAIN THESE VARIABLES FROM THE NORMAL PROJECT SCOPE
global $end_survey_redirect_next_survey, $end_survey_redirect_url;

// Check if custom logic is applied to this instrument
if (isset($auto_continue_logic[$instrument])) {
	
	// Get the logic and evaluate it
	$raw_logic = $auto_continue_logic[$instrument];
	$isValid = LogicTester::isValid($raw_logic);
	
	if (!$isValid) {
		print "<div class='red'><h3><center>Supplied survey auto-continue logic is invalid:<br>$raw_logic</center></h3></div>";
	}
	$logic_result = LogicTester::evaluateLogicSingleRecord($raw_logic, $record);
	
	if ($logic_result == false) {
		// This instrument should not be taken!
		
		// If autocontinue is enabled - then redirect to next instrument
		if($end_survey_redirect_next_survey) {
			// Try to get the next survey url
			$next_survey_url = Survey::getAutoContinueSurveyUrl($record, $instrument, $event_id);
			redirect($next_survey_url);
		} else {
			// If there is a normal end-of-survey url - go there
			if ($end_survey_redirect_url != "") {
				redirect($end_survey_redirect_url);
			} 
			// Display the normal end-of-survey message with an additional note
			else {
				$custom_text = "<div class='yellow'><h3><center>This survey does not apply.</center></h3></div>";
				exitSurvey($custom_text . $full_acknowledgement_text, false);
			}
		}
		exit();
	} else {
		// administer the instrument
	}
}
