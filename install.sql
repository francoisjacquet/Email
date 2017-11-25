
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

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Email/ReportCardsEmailParents.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/ReportCardsEmailParents.php'
    AND profile_id=1);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Email/DisciplineLogEmailParents.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/DisciplineLogEmailParents.php'
    AND profile_id=1);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Email/StudentBalancesEmailParents.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/StudentBalancesEmailParents.php'
    AND profile_id=1);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Email/EmailStudents.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/EmailStudents.php'
    AND profile_id=1);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Email/EmailUsers.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/EmailUsers.php'
    AND profile_id=1);


/*********************************************************
 Add Email template
**********************************************************/
--
-- Data for Name: templates; Type: TABLE DATA;
--

INSERT INTO templates (modname, staff_id, template)
SELECT 'Email/ReportCardsEmailParents.php', 0, 'Dear __PARENT_NAME__,

Please find the Report Card for __FIRST_NAME__ __LAST_NAME__ attached to this email.'
WHERE NOT EXISTS (SELECT modname
    FROM templates
    WHERE modname='Email/ReportCardsEmailParents.php'
    AND staff_id=0);


INSERT INTO templates (modname, staff_id, template)
SELECT 'Email/DisciplineLogEmailParents.php', 0, 'Dear __PARENT_NAME__,

Please find the Discipline Log for __FIRST_NAME__ __LAST_NAME__ attached to this email.'
WHERE NOT EXISTS (SELECT modname
    FROM templates
    WHERE modname='Email/DisciplineLogEmailParents.php'
    AND staff_id=0);


INSERT INTO templates (modname, staff_id, template)
SELECT 'Email/StudentBalancesEmailParents.php', 0, 'Dear __PARENT_NAME__,

Your child, __FIRST_NAME__ __LAST_NAME__ has a Balance of:

__BALANCE__'
WHERE NOT EXISTS (SELECT modname
    FROM templates
    WHERE modname='Email/StudentBalancesEmailParents.php'
    AND staff_id=0);

INSERT INTO templates (modname, staff_id, template)
SELECT 'Email/EmailStudents.php', 0, ''
WHERE NOT EXISTS (SELECT modname
    FROM templates
    WHERE modname='Email/EmailStudents.php'
    AND staff_id=0);

INSERT INTO templates (modname, staff_id, template)
SELECT 'Email/EmailUsers.php', 0, ''
WHERE NOT EXISTS (SELECT modname
    FROM templates
    WHERE modname='Email/EmailUsers.php'
    AND staff_id=0);
