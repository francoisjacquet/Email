<?php
/**
 * Email Students
 *
 * @package Email module
 */

require_once 'ProgramFunctions/MarkDownHTML.fnc.php';
require_once 'ProgramFunctions/SendEmail.fnc.php';

if ( file_exists( 'ProgramFunctions/Template.fnc.php' ) )
{
	// @since 3.6.
	require_once 'ProgramFunctions/Template.fnc.php';
}
else
{
	// @deprecated.
	require_once 'modules/Email/includes/Template.fnc.php';
}

DrawHeader( ProgramTitle() );

if ( ! Config( 'STUDENTS_EMAIL_FIELD' ) )
{
	$student_email_field = '<b>' . _( 'Students Email Field' ) . '</b>';

	if ( AllowEdit( 'School_Setup/Configuration.php' ) ) {

		$student_email_field = '<a href="Modules.php?modname=School_Setup/Configuration.php">' .
			$student_email_field . '</a>';
	}

	$error[] = sprintf(
		dgettext( 'Email', 'You must configure the %s to use this script.' ),
		$student_email_field
	);

	ErrorMessage( $error, 'fatal' );
}

$email_field_key = Config( 'STUDENTS_EMAIL_FIELD' );

if ( $email_field_key !== 'USERNAME' )
{
	$email_field_key = 'CUSTOM_' . Config( 'STUDENTS_EMAIL_FIELD' );
}

if ( User( 'PROFILE' ) === 'teacher' )
{
	$_ROSARIO['allow_edit'] = true;
}

if ( $_REQUEST['modfunc'] === 'save'
	&& AllowEdit() )
{
	if ( count( $_REQUEST['st_arr'] ) )
	{
		// If $test email is set then this script will only 'go through the motions'
		// and email the results to the $test_email address instead of parents.
		$test_email = $_REQUEST['test_email'];

		// Set the from and cc emails here - the emails can be comma separated list of emails.
		$reply_to = '';

		if ( filter_var( User( 'EMAIL' ), FILTER_VALIDATE_EMAIL ) )
		{
			$reply_to = User( 'NAME' ) . ' <' . User( 'EMAIL' ) . '>';
		}
		elseif ( ! filter_var( $test_email, FILTER_VALIDATE_EMAIL ) )
		{
			$error[] = _( 'You must set the <b>test mode email</b> or have a user email address to use this script.' );

			ErrorMessage( $error, 'fatal' );
		}

		$subject = isset( $_REQUEST['subject'] ) ?
			str_replace( "''", "'", $_REQUEST['subject'] ) :
			'';

		// FJ bypass strip_tags on the $_REQUEST vars.
		$REQUEST_email_text = SanitizeHTML( $_POST['email_text'] );

		$st_list = "'" . implode( "','", $_REQUEST['st_arr'] ) . "'";

		$extra['WHERE'] = " AND s.STUDENT_ID IN (" . $st_list . ")";

		$extra['SELECT'] .= ",s.FIRST_NAME AS NICK_NAME";

		// Select Email.
		$extra['SELECT'] .= ",s." . DBEscapeIdentifier( $email_field_key ) . " AS EMAIL";

		if ( $email_field_key !== 'USERNAME' )
		{
			$extra['SELECT'] .= ",s.USERNAME";
		}

		if ( User( 'PROFILE' ) === 'admin' )
		{
			if ( $_REQUEST['w_course_period_id_which'] === 'course_period'
				&& $_REQUEST['w_course_period_id'] )
			{
				$extra['SELECT'] .= ",(SELECT st.FIRST_NAME||' '||st.LAST_NAME FROM STAFF st,COURSE_PERIODS cp WHERE st.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_PERIOD_ID='".$_REQUEST['w_course_period_id']."') AS TEACHER";
				$extra['SELECT'] .= ",(SELECT cp.ROOM FROM COURSE_PERIODS cp WHERE cp.COURSE_PERIOD_ID='".$_REQUEST['w_course_period_id']."') AS ROOM";
			}
			else
			{
				// FJ multiple school periods for a course period.
				$extra['SELECT'] .= ",(SELECT st.FIRST_NAME||' '||st.LAST_NAME FROM STAFF st,COURSE_PERIODS cp,SCHOOL_PERIODS p,SCHEDULE ss,COURSE_PERIOD_SCHOOL_PERIODS cpsp WHERE cp.COURSE_PERIOD_ID=cpsp.COURSE_PERIOD_ID AND st.STAFF_ID=cp.TEACHER_ID AND cpsp.PERIOD_id=p.PERIOD_ID AND p.ATTENDANCE='Y' AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR='".UserSyear()."' AND ss.MARKING_PERIOD_ID IN (".GetAllMP('QTR',GetCurrentMP('QTR',DBDate(),false)).") AND (ss.START_DATE<='".DBDate()."' AND (ss.END_DATE>='".DBDate()."' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS TEACHER";

				$extra['SELECT'] .= ",(SELECT cp.ROOM FROM COURSE_PERIODS cp,SCHOOL_PERIODS p,SCHEDULE ss,COURSE_PERIOD_SCHOOL_PERIODS cpsp WHERE cp.COURSE_PERIOD_ID=cpsp.COURSE_PERIOD_ID AND cpsp.PERIOD_id=p.PERIOD_ID AND p.ATTENDANCE='Y' AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR='".UserSyear()."' AND ss.MARKING_PERIOD_ID IN (".GetAllMP('QTR',GetCurrentMP('QTR',DBDate(),false)).") AND (ss.START_DATE<='".DBDate()."' AND (ss.END_DATE>='".DBDate()."' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS ROOM";
			}
		}
		else
		{
			$extra['SELECT'] .= ",(SELECT st.FIRST_NAME||' '||st.LAST_NAME FROM STAFF st,COURSE_PERIODS cp WHERE st.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_PERIOD_ID='".UserCoursePeriod()."') AS TEACHER";
			$extra['SELECT'] .= ",(SELECT cp.ROOM FROM COURSE_PERIODS cp WHERE cp.COURSE_PERIOD_ID='".UserCoursePeriod()."') AS ROOM";
		}

		SaveTemplate( $REQUEST_email_text );

		$RET = GetStuList( $extra );

		$LO_result = array( 0 => array() );

		$i = 0;

		foreach ( (array) $RET as $student )
		{
			$email_text = $REQUEST_email_text;

			foreach ( (array) $student as $column => $value )
			{
				$email_text = str_replace( '__' . $column . '__', $value, $email_text );
			}

			$to = empty( $test_email ) ? $student['EMAIL'] : $test_email;

			$result = SendEmail( $to, $subject, $email_text, $reply_to );

			$LO_result[] = array(
				'STUDENT' => $student['FULL_NAME'],
				'USERNAME' => $student['USERNAME'],
				'EMAIL' => $to,
				'RESULT' => $result ? _( 'Success' ) : _( 'Fail' ),
			);

			$i++;
		}

		unset( $LO_result[0] );

		$columns = array(
			'STUDENT' => _( 'Student' ),
		);

		if ( $email_field_key !== 'USERNAME' )
		{
			$columns['USERNAME'] = _( 'Username' );
		}

		$columns += array(
			'EMAIL' => _( 'Email' ),
			'RESULT' => _( 'Result' ),
		);

		ListOutput( $LO_result, $columns, 'Sending Result', 'Sending Results' );
	}
	else
	{
		$error[] = _( 'You must choose at least one student.' );

		// Unset modfunc & redirect URL.
		RedirectURL( 'modfunc' );
	}
}

if ( ! $_REQUEST['modfunc'] )
{
	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] .
			'&modfunc=save&include_inactive=' . $_REQUEST['include_inactive'] .
			'&_search_all_schools=' . $_REQUEST['_search_all_schools'] .
			'" method="POST">';

		$extra['header_right'] = SubmitButton( dgettext( 'Email', 'Send Email to Selected Students' ) );

		$extra['extra_header_left'] = '<table>' . $extra['search'] . '</table>';

		$extra['search'] = '';

		// Subject field.
		$extra['extra_header_left'] .= '<table class="width-100p"><tr><td>' .
			TextInput(
				$subject,
				'subject',
				dgettext( 'Email', 'Subject' ),
				'required maxlength="100" class="width-100p"',
				false
			) .
			'</td></tr>';

		// FJ add TinyMCE to the textarea.
		$extra['extra_header_left'] .= '<tr><td>' .
			TinyMCEInput(
				GetTemplate(),
				'email_text',
				_( 'Email' )
			) .
			'</textarea></td></tr>';

		$extra['extra_header_left'] .= '<tr><td><hr />' . _( 'Substitutions' ) . ':<br /><table><tr class="st">';
		$extra['extra_header_left'] .= '<td>__FULL_NAME__</td><td>= ' . _( 'Last, First M' ) . '</td><td>&nbsp;</td>';
		$extra['extra_header_left'] .= '</tr><tr class="st">';
		$extra['extra_header_left'] .= '<td>__FIRST_NAME__</td><td>= ' . _( 'First Name' ) . '</td><td>&nbsp;</td>';
		$extra['extra_header_left'] .= '<td>__LAST_NAME__</td><td>= ' . _( 'Last Name' ) . '</td>';
		$extra['extra_header_left'] .= '</tr><tr class="st">';
		$extra['extra_header_left'] .= '<td>__MIDDLE_NAME__</td><td>= ' . _( 'Middle Name' ) . '</td><td>&nbsp;</td>';
		$extra['extra_header_left'] .= '<td>__STUDENT_ID__</td><td>= ' . sprintf( _( '%s ID' ), Config( 'NAME' ) ) . '</td>';
		$extra['extra_header_left'] .= '</tr><tr class="st">';
		$extra['extra_header_left'] .= '<td>__SCHOOL_TITLE__</td><td>= ' . _( 'School' ) . '</td><td>&nbsp;</td>';
		$extra['extra_header_left'] .= '<td>__GRADE_ID__</td><td>= ' . _( 'Grade Level' ) . '</td>';
		$extra['extra_header_left'] .= '</tr><tr class="st">';

		if ( User( 'PROFILE' ) === 'admin' )
		{
			$extra['extra_header_left'] .= '<td>__TEACHER__</td><td>= ' . _( 'Attendance Teacher' ) . '</td><td></td>';
			$extra['extra_header_left'] .= '<td>__ROOM__</td><td>= ' . _( 'Attendance Room' ) . '</td>';
		}
		else
		{
			$extra['extra_header_left'] .= '<td>__TEACHER__</td><td>= ' . _( 'Your Name' ) . '</td><td></td>';
			$extra['extra_header_left'] .= '<td>__ROOM__</td><td>= ' . _( 'Your Room' ) . '</td>';
		}

		$extra['extra_header_left'] .= '</tr></table></td></tr>';

		$extra['extra_header_left'] .= '<tr class="st"><td class="valign-top"><hr />' .
			_( 'Test Mode' ) . ':' . '<br />' .
			TextInput(
				'',
				'test_email',
				_( 'Email' ),
				'',
				false
			) . '</td></tr>';

		$extra['extra_header_left'] .= '</table>';
	}


	// Select Email.
	$extra['SELECT'] .= ",s." . DBEscapeIdentifier( $email_field_key ) . " AS EMAIL";

	// Add Email column.
	$extra['columns_after']['EMAIL'] = _( 'Email' );

	$extra['SELECT'] .= ",s.STUDENT_ID AS CHECKBOX";
	$extra['link'] = array( 'FULL_NAME' => false );
	$extra['functions'] = array( 'CHECKBOX' => '_makeChooseCheckbox' );
	$extra['columns_before'] = array( 'CHECKBOX' => '</a><input type="checkbox" value="Y" name="controller" checked onclick="checkAll(this.form,this.checked,\'st_arr\');"><A>' );

	$extra['options']['search'] = false;
	$extra['new'] = true;

	Search( 'student_id', $extra );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<br /><div class="center">' .
			SubmitButton( dgettext( 'Email', 'Send Email to Selected Students' ) ) . '</div></form>';
	}
}

function _makeChooseCheckbox( $value, $title )
{
	global $THIS_RET;

	if ( isset( $THIS_RET['EMAIL'] )
		&& filter_var( $THIS_RET['EMAIL'], FILTER_VALIDATE_EMAIL ) ) {

		// Has email and is valid email, show checkbox.
		return '<input type="checkbox" name="st_arr[]" value="' . $value . '" checked />';
	}
}
