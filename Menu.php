<?php
/**
 * Menu.php file
 * Required
 * - Add Menu entries to other modules
 *
 * @package Email module
 */

// Use dgettext() function instead of _() for Module specific strings translation
// See locale/README file for more information.

// Add a Menu entry to the Grades module.
if ( $RosarioModules['Grades'] ) // Verify Grades module is activated.
{
	$menu['Grades']['admin'] += array(
		5 => dgettext( 'Email', 'Email' ),
		'Email/ReportCardsEmailParents.php' => dgettext( 'Email', 'Email Report Cards to Parents' ),
	);
}

// Add a Menu entry to the Discipline module.
if ( $RosarioModules['Discipline'] ) // Verify Discipline module is activated.
{
	$menu['Discipline']['admin'] += array(
		3 => dgettext( 'Email', 'Email' ),
		'Email/DisciplineLogEmailParents.php' => dgettext( 'Email', 'Email Discipline Log to Parents' ),
	);
}

// Add a Menu entry to the Student_Billing module.
if ( $RosarioModules['Student_Billing'] ) // Verify Student_Billing module is activated.
{
	$menu['Student_Billing']['admin'] += array(
		2 => dgettext( 'Email', 'Email' ),
		'Email/StudentBalancesEmailParents.php' => dgettext( 'Email', 'Email Student Balances to Parents' ),
	);
}

// Add a Menu entry to the Students module.
if ( $RosarioModules['Students'] ) // Verify Students module is activated.
{
	$menu['Studens']['admin'] += array(
		2 => dgettext( 'Email', 'Email' ),
		'Email/Letters.php' => _( 'Send Emails' ),
	);

	$menu['Studens']['teacher'] += array(
		2 => dgettext( 'Email', 'Email' ),
		'Email/Letters.php' => _( 'Send Emails' ),
	);
}
