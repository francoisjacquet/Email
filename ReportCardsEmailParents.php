<?php
/**
 * Email Report Cards to Parents
 *
 * @package Email module
 */

require_once 'modules/Grades/includes/ReportCards.fnc.php';

DrawHeader( ProgramTitle() );

// Send emails.
if ( isset( $_REQUEST['modfunc'] )
	&& $_REQUEST['modfunc'] === 'save'
	&& AllowEdit() )
{
	if ( isset( $_POST['mp_arr'] )
		&& isset( $_POST['student'] ) )
	{
		// FJ add Template.
		$template_update = DBGet( DBQuery( "SELECT 1
			FROM TEMPLATES
			WHERE MODNAME = '" . $_REQUEST['modname'] . "'
			AND STAFF_ID = '" . User( 'STAFF_ID' ) . "'" ) );

		// INSERT new template.
		if ( ! $template_update )
		{
			DBQuery( "INSERT INTO TEMPLATES (
					MODNAME,
					STAFF_ID,
					TEMPLATE
				)
				VALUES (
					'" . $_REQUEST['modname'] . "',
					'" . User( 'STAFF_ID' ) . "',
					'" . $_REQUEST['inputreportcardsemailtext'] . "'
				)" );
		}
		// UPDATE template.
		else
		{
			DBQuery( "UPDATE TEMPLATES
				SET TEMPLATE = '" . $_REQUEST['inputreportcardsemailtext'] . "'
				WHERE MODNAME = '" . $_REQUEST['modname'] . "'
				AND STAFF_ID = '" . User( 'STAFF_ID' ) . "'" );
		}

		$message = str_replace( "''", "'", $_REQUEST['inputreportcardsemailtext'] );

		$_REQUEST['_ROSARIO_PDF'] = 'true';

		// Generate and get Report Cards HTML.
		$report_cards = ReportCardsGenerate( $_REQUEST['student'], $_REQUEST['mp_arr'] );

		if ( $report_cards )
		{

			// FJ add SendEmailAttachment function.
			require_once 'modules/Email/includes/SendEmailAttachment.fnc.php';

			$st_list = '\'' . implode( '\',\'', $_REQUEST['student'] ) . '\'';

			// SELECT Staff details.
			$extra['SELECT'] .= ",(SELECT st.FIRST_NAME||' '||st.LAST_NAME
				FROM STAFF st,STUDENTS_JOIN_USERS sju
				WHERE sju.STAFF_ID=st.STAFF_ID
				AND s.STUDENT_ID=sju.STUDENT_ID
				AND st.SYEAR='" . UserSyear() . "' LIMIT 1) AS PARENT_NAME";

			$extra['SELECT'] .= ",(SELECT st.EMAIL
				FROM STAFF st,STUDENTS_JOIN_USERS sju
				WHERE sju.STAFF_ID=st.STAFF_ID
				AND s.STUDENT_ID=sju.STUDENT_ID
				AND st.SYEAR='" . UserSyear() . "' LIMIT 1) AS PARENT_EMAIL";

			$extra['WHERE'] = " AND s.STUDENT_ID IN (" . $st_list . ")";

			$student_RET = GetStuList( $extra );

			// echo '<pre>'; var_dump($student_RET); echo '</pre>';

			$error_email_list = array();

			$pdf_options = array(
				'css' => true,
				'margins' => array(),
				'mode' => 3, // Save.
			);

			foreach ( (array) $student_RET as $student )
			{
				$to = $student['PARENT_EMAIL'];

				$from = $cc = null;

				// FJ send email from rosariosis@[domain] or Staff email.
				if ( filter_var( User( 'EMAIL' ), FILTER_VALIDATE_EMAIL ) )
				{
					$from = User( 'NAME' ) . ' <' . User( 'EMAIL' ) . '>';
				}

				$subject = _( 'Report Cards' ) .
					' - ' . $student['FULL_NAME'];

				// Substitutions.
				$msg = str_replace(
					array(
						'__FIRST_NAME__',
						'__LAST_NAME__',
						'__SCHOOL_ID__',
						'__PARENT_NAME__',
					),
					array(
						$student['FIRST_NAME'],
						$student['LAST_NAME'],
						SchoolInfo( 'TITLE' ),
						$student['PARENT_NAME'],
					),
					$message
				);

				$report_card = $report_cards[ $student['STUDENT_ID'] ];

				if ( $report_card )
				{
					// Generate PDF.
					$handle = PDFStart( $pdf_options );

					echo $report_card;

					$pdf_file = PDFStop( $handle );

					$pdf_name = $subject . '.pdf';

					// Send Email.
					$result = SendEmailAttachment(
						$to,
						$subject,
						$msg,
						$from,
						$cc,
						array( array( $pdf_file, $pdf_name ) )
					);

					// Delete PDF file.
					unlink( $pdf_file );

					if ( ! $result )
					{
						$error_email_list[] = $student['PARENT_NAME'] .
							' (' . $student['PARENT_EMAIL'] . ')';
					}
				}
			}

			if ( ! empty( $error_email_list ) )
			{
				$error_email_list = implode( ', ', $error_email_list );

				$error[] = sprintf(
					dgettext( 'Email', 'Email not sent to: %s' ),
					$error_email_list
				);
			}

			$note[] = dgettext( 'Email', 'The report cards have been sent.' );
		}
		else
			$error[] = _( 'No Students were found.' );
	}
	// No Users / MP selected.
	else
		$error[] = _( 'You must choose at least one student and one marking period.' );

	unset( $_SESSION['_REQUEST_vars']['modfunc'] );

	unset( $_REQUEST['modfunc'] );
}

// Display errors if any.
if ( isset( $error ) )
{
	echo ErrorMessage( $error );
}

// Display notes if any.
if ( isset( $note ) )
{
	echo ErrorMessage( $note, 'note' );
}

// Display Search screen or Student list.
if ( empty( $_REQUEST['modfunc'] )
	|| $_REQUEST['search_modfunc'] === 'list' )
{
	// Open Form & Display Email options.
	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<form action="' . PreparePHP_SELF(
			$_REQUEST,
			array( 'search_modfunc' ),
			array( 'modfunc' => 'save' )
		) . '" method="POST">';

		$extra['header_right'] = SubmitButton( dgettext( 'Email', 'Send Report Cards to Selected Parents' ) );

		$extra['extra_header_left'] = '<table>';

		// FJ add Template.
		$templates = DBGet( DBQuery( "SELECT TEMPLATE, STAFF_ID
			FROM TEMPLATES WHERE MODNAME = '" . $_REQUEST['modname'] . "'
			AND STAFF_ID IN (0,'" . User( 'STAFF_ID' ) . "')" ), array(), array( 'STAFF_ID' ) );

		$template = $templates[ ( isset( $templates[ User( 'STAFF_ID' ) ] ) ? User( 'STAFF_ID' ) : 0 ) ][1]['TEMPLATE'];

		// Email Template Textarea.
		$extra['extra_header_left'] .= '<tr class="st"><td>
			<label><textarea name="inputreportcardsemailtext" cols="97" rows="5">' . $template . '</textarea>
			<span class="legend-gray">' . _( 'Report Cards' ) . ' - ' . _( 'Email Text' ) . '</span></label>
			</td></tr>';

		// Spacing.
		$extra['extra_header_left'] .= '<tr><td>&nbsp;</td></tr>';

		// Substitutions.
		$extra['extra_header_left'] .= '<tr class="st">
			<td><table><tr class="st">';

		$extra['extra_header_left'] .= '<td>__PARENT_NAME__</td>
			<td>= ' . _( 'Parent Name' ) . '</td>
			<td colspan="3">&nbsp;</td>';

		$extra['extra_header_left'] .= '</tr><tr class="st">';

		$extra['extra_header_left'] .= '<td>__FIRST_NAME__</td>
			<td>= ' . _( 'First Name' ) . '</td><td>&nbsp;</td>';

		$extra['extra_header_left'] .= '<td>__LAST_NAME__</td>
			<td>= ' . _( 'Last Name' ) . '</td>';

		$extra['extra_header_left'] .= '</tr><tr class="st">';

		$extra['extra_header_left'] .= '<td>__SCHOOL_ID__</td>
			<td>= ' . _( 'School' ) . '</td>
			<td>&nbsp;</td>';

		$extra['extra_header_left'] .= '</tr></table>
			<span class="legend-gray">' . _( 'Substitutions' ) . '</span></td></tr>';

		$extra['extra_header_left'] .= '</table>';

		// Add Include in Discipline Log form.
		$extra['extra_header_left'] .= ReportCardsIncludeForm();

	}

	$extra['SELECT'] = ",s.STUDENT_ID AS CHECKBOX";

	// SELECT Staff details.
	$extra['SELECT'] .= ",(SELECT st.FIRST_NAME||' '||st.LAST_NAME
		FROM STAFF st,STUDENTS_JOIN_USERS sju
		WHERE sju.STAFF_ID=st.STAFF_ID
		AND s.STUDENT_ID=sju.STUDENT_ID
		AND st.SYEAR='" . UserSyear() . "' LIMIT 1) AS PARENT_NAME";

	$extra['SELECT'] .= ",(SELECT st.EMAIL
		FROM STAFF st,STUDENTS_JOIN_USERS sju
		WHERE sju.STAFF_ID=st.STAFF_ID
		AND s.STUDENT_ID=sju.STUDENT_ID
		AND st.SYEAR='" . UserSyear() . "' LIMIT 1) AS PARENT_EMAIL";

	// ORDER BY Name.
	$extra['ORDER_BY'] = 'FULL_NAME';

	// Call functions to format Columns.
	$extra['functions'] = array( 'CHECKBOX' => '_makeChooseCheckbox' );

	// Columns Titles.
	$extra['columns_before'] = array(
		'CHECKBOX' => '</a><input type="checkbox" value="Y" name="controller" onclick="checkAll(this.form,this.form.controller.checked,\'student\');" /><A>',
	);

	$extra['columns_after'] = array(
		'PARENT_NAME' => _( 'Parent Name' ),
		'PARENT_EMAIL' => _( 'Email' ),
	);

	// No link for Student's name.
	$extra['link'] = array( 'FULL_NAME' => false );

	// Remove Current Student if any.
	$extra['new'] = true;

	// Display Search screen or Search Students.
	Search( 'student_id', $extra );

	// Submit & Close Form.
	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<br /><div class="center">' .
			SubmitButton( dgettext( 'Email', 'Send Report Cards to Selected Parents' ) ) . '</div>';
		echo '</form>';
	}
}


/**
 * Make Choose Checkbox
 *
 * Local function
 *
 * @param  string $value  STUDENT_ID value.
 * @param  string $column 'CHECKBOX'.
 *
 * @return string Checkbox or empty string if no Email or no Referrals
 */
function _makeChooseCheckbox( $value, $column )
{
	global $THIS_RET;

	// If valid email & has Referrals.
	if ( filter_var( $THIS_RET['PARENT_EMAIL'], FILTER_VALIDATE_EMAIL ) )
	{
		return '<input type="checkbox" name="student[' . $value . ']" value="' . $value . '" />';
	}
	else
		return '';
}
