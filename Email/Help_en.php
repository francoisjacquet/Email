<?php
/**
 * English Help texts - Email module
 *
 * Texts are organized by:
 * - Module
 * - Profile
 *
 * @author François Jacquet
 *
 * @uses Heredoc syntax
 * @see  http://php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
 *
 * @package Email module
 */

// EMAIL ---.
if ( User( 'PROFILE' ) === 'admin' ) :

	$help['Email/ReportCardsEmailParents.php'] = <<<HTML
<p>
	<i>Email Report Cards to Parents</i> allows you to generate PDF Report Cards and send them to the selected Parents email.
</p>
<p>
	The email body is customizable and the Report Card PDF is attached to the email.
</p>
<p>
	If you would like to check the Report Cards before sending them, please use the <i>Grades > Report Cards</i> program.
</p>
HTML;

	$help['Email/DisciplineLogEmailParents.php'] = <<<HTML
<p>
	<i>Email Discipline Log to Parents</i> allows you to generate PDF Discipline Logs and send them to the selected Parents email.
</p>
<p>
	The email body is customizable and the Discipline Log PDF is attached to the email.
</p>
<p>
	If you would like to check the Discipline Logs before sending them, please use the <i>Discipline > Discipline Log</i> program.
</p>
HTML;

	$help['Email/StudentBalancesEmailParents.php'] = <<<HTML
<p>
	<i>Email Student Balances to Parents</i> allows you send the student balances to the selected Parents email.
</p>
<p>
	The email body is customizable.
</p>
<p>
	If you would like to check the Student Balances before sending them, please use the <i>Student Billing > Student Balances</i> program.
</p>
HTML;

endif;
