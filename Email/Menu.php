<?php

/***********************************************************
 Menu.php file
 Required
 - Add Menu entries to other modules
***********************************************************/

//use dgettext() function instead of _() for Module specific strings translation
//see locale/README file for more information

//add a Menu entry to the Grades module
if ( $RosarioModules['Grades'] ) //verify Grades module is activated
{
	$menu['Grades']['admin'] += array(
		5 => dgettext( 'Email', 'Email' ),
		'Email/ReportCardsEmailParents.php' => dgettext( 'Email', 'Email Report Cards to Parents' ), 
	);
}

//add a Menu entry to the Discipline module
if ( $RosarioModules['Discipline'] ) //verify Discipline module is activated
{
	$menu['Discipline']['admin'] += array(
		3 => dgettext( 'Email', 'Email' ),
		'Email/DisciplineLogEmailParents.php' => dgettext( 'Email', 'Email Discipline Log to Parents' ), 
	);
}

//add a Menu entry to the Student_Billing module
if ( $RosarioModules['Student_Billing'] ) //verify Student_Billing module is activated
{
	$menu['Student_Billing']['admin'] += array(
		2 => dgettext( 'Email', 'Email' ),
		'Email/StudentBalancesEmailParents.php' => dgettext( 'Email', 'Email Student Balances to Parents' ), 
	);
}
