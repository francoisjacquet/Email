<?php
/**
 * Email Users
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
		$cc = '';

		if ( User( 'EMAIL' ) )
		{
			$cc = User( 'EMAIL' );
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

		$extra['WHERE'] = " AND s.STAFF_ID IN (" . $st_list . ")";

		$extra['SELECT'] .= ",s.USERNAME,s.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME";

		// Select Email.
		$extra['SELECT'] .= ",s.EMAIL";

		SaveTemplate( $REQUEST_email_text );

		$RET = GetStaffList( $extra );

		$LO_result = array( 0 => array() );

		$i = 0;

		foreach ( (array) $RET as $user )
		{
			$email_text = $REQUEST_email_text;

			foreach ( (array) $user as $column => $value )
			{
				$email_text = str_replace( '__' . $column . '__', $value, $email_text );
			}

			$to = empty( $test_email ) ? $user['EMAIL'] : $test_email;

			// FJ send email from rosariosis@[domain].
			$result = SendEmail( $to, $subject, $email_text, null, $cc );

			$LO_result[] = array(
				'STAFF' => $user['FULL_NAME'],
				'USERNAME' => $user['USERNAME'],
				'EMAIL' => $to,
				'RESULT' => $result ? _( 'Success' ) : _( 'Fail' ),
			);

			$i++;
		}

		unset( $LO_result[0] );

		$columns = array(
			'STAFF' => _( 'Student' ),
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

		$extra['header_right'] = SubmitButton( dgettext( 'Email', 'Send Email to Selected Users' ) );

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
		$extra['extra_header_left'] .= '<td>__STAFF_ID__</td><td>= ' . sprintf( _( '%s ID' ), Config( 'NAME' ) ) . '</td>';
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
	$extra['SELECT'] .= ",s.EMAIL";

	// Add Email column.
	$extra['columns_after']['EMAIL'] = _( 'Email' );

	$extra['SELECT'] .= ",s.STAFF_ID AS CHECKBOX";
	$extra['link'] = array( 'FULL_NAME' => false );
	$extra['functions'] = array( 'CHECKBOX' => '_makeChooseCheckbox' );
	$extra['columns_before'] = array( 'CHECKBOX' => '</a><input type="checkbox" value="Y" name="controller" checked onclick="checkAll(this.form,this.checked,\'st_arr\');"><A>' );

	$extra['options']['search'] = false;
	$extra['new'] = true;

	Search( 'staff_id', $extra );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<br /><div class="center">' .
			SubmitButton( dgettext( 'Email', 'Send Email to Selected Users' ) ) . '</div></form>';
	}
}

function _makeChooseCheckbox( $value, $title )
{
	global $THIS_RET;

	if ( filter_var( $THIS_RET['EMAIL'], FILTER_VALIDATE_EMAIL ) ) {

		// Has email and is valid email, show checkbox.
		return '<input type="checkbox" name="st_arr[]" value="' . $value . '" checked />';
	}
}
