
/**********************************************************************
 install.sql file
 Required as the module adds programs to other modules
 - Add profile exceptions for the module to appear in the menu
 - Add Email templates
***********************************************************************/

/*******************************************************
 profile_id:
 	- 0: student
 	- 1: admin
 	- 2: teacher
 	- 3: parent
 modname: should match the Menu.php entries
 can_use: 'Y'
 can_edit: 'Y' or null (generally null for non admins)
*******************************************************/
--
-- Data for Name: profile_exceptions; Type: TABLE DATA; 
--

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit) VALUES (
1, 'Email/ReportCardsEmailParents.php', 'Y', 'Y');

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit) VALUES (
1, 'Email/DisciplineLogEmailParents.php', 'Y', 'Y');

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit) VALUES (
1, 'Email/StudentBalancesEmailParents.php', 'Y', 'Y');



/*********************************************************
 Add Email template
**********************************************************/
--
-- Data for Name: templates; Type: TABLE DATA;
--

INSERT INTO templates VALUES ('Email/ReportCardsEmailParents.php', 0, 'Dear __PARENT_NAME__,

Please find the Report Card for __FIRST_NAME__ __LAST_NAME__ attached to this email.');

INSERT INTO templates VALUES ('Email/DisciplineLogEmailParents.php', 0, 'Dear __PARENT_NAME__,

Please find the Discipline Log for __FIRST_NAME__ __LAST_NAME__ attached to this email.');

INSERT INTO templates VALUES ('Email/StudentBalancesEmailParents.php', 0, 'Dear __PARENT_NAME__,

Your child, __FIRST_NAME__ __LAST_NAME__ has a Balance of:

__BALANCE__');
