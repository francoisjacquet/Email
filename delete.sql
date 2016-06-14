
/**********************************************************
 delete.sql file
 Required as install.sql file present
 - Delete profile exceptions
 - Delete email templates
***********************************************************/

--
-- Delete profile exceptions
--

DELETE FROM profile_exceptions WHERE modname='Email/ReportCardsEmailParents.php';
DELETE FROM profile_exceptions WHERE modname='Email/DisciplineLogEmailParents.php';
DELETE FROM profile_exceptions WHERE modname='Email/StudentBalancesEmailParents.php';


--
-- Delete email template
--

DELETE FROM templates WHERE modname='Email/ReportCardsEmailParents.php' AND staff_id=0;
DELETE FROM templates WHERE modname='Email/DisciplineLogEmailParents.php' AND staff_id=0;
DELETE FROM templates WHERE modname='Email/StudentBalancesEmailParents.php' AND staff_id=0;
