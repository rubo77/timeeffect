NOTES.1_1_0_13.
=================

TIMEEFFECT 1.1.0, Rev. 13

This release is the first official production release.

----------------------------------------------------
UPDATE:

Beta 1.1.g and 1.1.h:
simply make a backup copy of the files
	'include/aperetiv.inc.php'
and 
	'include/layout.inc.php'
Untar the TIMEEFFECT package to the appropriate directory.


Former releases:
Since we made major changes to the TIMEEFFECT data model there is no accurate way to
update from any prior versions of TIMEEFFECT.
Please install TIMEEFFECT from scratch.


NOTES.1_1_1_23.
=================

TIMEEFFECT 1.1.1, Rev. 23

This release is the first official production release.

----------------------------------------------------
UPDATE:

1.1.0, beta 1.1.g and beta 1.1.h:
simply make a backup copy of the files
	'include/aperetiv.inc.php'
and 
	'include/layout.inc.php'
Untar the TIMEEFFECT package to the appropriate directory.


Former releases:
Since we made major changes to the TIMEEFFECT data model there is no accurate way to
update from any prior versions of TIMEEFFECT.
Please install TIMEEFFECT from scratch.


NOTES.beta_1_1.
=================

This is the first official beta release of TIMEEFFECT.

There is currently only installation documentation.

----------------------------------------------------
KNOWN ISSUES:
- logon as agent does not show the desired results.
  There are some strange effects when logging on as
  user who only belongs to the group 'agent'.


NOTES.beta_1_1_b.
=================

This release fixes a bug in using the needed PEAR packages. Since this release the
needed PEAR packages are included within the TIMEEFFECT tar ball.

----------------------------------------------------
There are two different possibilties to update to from beta 1.1 to beta 1.1.b:

+++++++++++++++++++++++++++++++++
A.
 - download the file 'timeeffect.patch.beta_1_1-beta_1_1_b.gz'.
 - Copy it to the directory below the timeeffect install directory (e.g. if your
   TIMEEFFECT is located in 'srv/www/htdocs/timeeffect' copy the file to '/srv/www/htdocs')
 - gunzip and apply the patch


+++++++++++++++++++++++++++++++++
B.
 - download the file timeeffect.PEAR.tgz. Copy it to the TIMEEFFECT
   include directory (timeeffect/include) untar it.
 - edit the file 'aperetiv.inc.php' and add the following changes
   (where '-' means remove the appropriate lines and '+' means add the lines):
```
*** 71,87 ****
                exit;
        }
  
-       require_once ('PEAR.php');
-       // let timeefect complain when any PEAR error occurs
-       PEAR::setErrorHandling(PEAR_ERROR_TRIGGER, E_USER_WARNING);
- 
-       define('FPDF_FONTPATH', $_PJ_root . '/include/font/');
- 
        // the following two lines must be activated if the PEAR packages
        // are located within the timeeffect include path
        $include_path = ini_get('include_path');
        ini_set('include_path', $_PJ_root . '/include/pear/:./:' . $include_path);
  
  
        $_PJ_css_path           = $_PJ_http_root . "/css";
        $_PJ_icon_path          = $_PJ_http_root . "/icons";
--- 71,86 ----
                exit;
        }
  
        // the following two lines must be activated if the PEAR packages
        // are located within the timeeffect include path
        $include_path = ini_get('include_path');
        ini_set('include_path', $_PJ_root . '/include/pear/:./:' . $include_path);
  
+       require_once ('PEAR.php');
+       // let timeefect complain when any PEAR error occurs
+       PEAR::setErrorHandling(PEAR_ERROR_TRIGGER, E_USER_WARNING);
+ 
+       define('FPDF_FONTPATH', $_PJ_root . '/include/font/');
  
        $_PJ_css_path           = $_PJ_http_root . "/css";
        $_PJ_icon_path          = $_PJ_http_root . "/icons";

```

NOTES.beta_1_1_c.
=================

This release adds an installation routine to TIMEEFFECT and offers the possibilty to
display a logo image on top of each page of an PDF report. Furthermore the PDF
layout control has been enhanced and centralized in on file.

----------------------------------------------------
There are two different possibilties to update to from beta 1.1 or beta 1.1.b to beta 1.1.c:

+++++++++++++++++++++++++++++++++
A.
 - download the appropriate patch file ('timeeffect.patch.beta_1_1-beta_1_1_c.gz' or
   'timeeffect.patch.beta_1_1_b-beta_1_1_c.gz').
 - Copy it to the directory beneath the timeeffect install directory (e.g. if your
   TIMEEFFECT is located in 'srv/www/htdocs/timeeffect' copy the file to '/srv/www/htdocs').
 - gunzip and apply the patch.

+++++++++++++++++++++++++++++++++
B.
 - download the file 'timeeffect_beta_1_1_c.tgz'.
 - Copy the file 'timeeffect_beta_1_1_c.tgz' to the directory beneath the timeeffect
   install directory (e.g. if your TIMEEFFECT is located in 'srv/www/htdocs/timeeffect'
   copy the file to '/srv/www/htdocs')
 - untar the file.
 - copy your local copy of 'timeeffect/include/aperetiv.inc.php' to 'timeeffect_beta_1_1_c/include'.
 - remove the sub folder 'timeeffect_beta_1_1_c/install'.
 - the new release is located in the new subfolder 'timeeffect_beta_1_1_c'. To restore your old system
   rename or remove the subfolder where your old installtion was located. Then rename the new subfolder
   'timeeffect_beta_1_1_c' to the name of the prior renamed/removed old subfolder.
 - you may have to redo layout changes you did to your TIMEEFFECT installation (e.g. in
   include/layout.inc.php)


NOTES.beta_1_1_d.
=================

This release fixes some minor bugs on display of currency and adding new users

----------------------------------------------------
There are two different possibilties to update to from beta 1.1 or beta 1.1.b or beta 1.1.c to beta 1.1.d:

+++++++++++++++++++++++++++++++++
A. updating from a beta 1.1.x version
 - download the appropriate patch file ('timeeffect.patch.beta_1_1-beta_1_1_d.gz' or
   'timeeffect.patch.beta_1_1_x-beta_1_1_d.gz' where 'x' marks your installed version).
 - Copy it to the directory beneath the timeeffect install directory (e.g. if your
   TIMEEFFECT is located in 'srv/www/htdocs/timeeffect' copy the file to '/srv/www/htdocs').
 - gunzip and apply the patch.

+++++++++++++++++++++++++++++++++
B. updating all files (recommended)
 - download the file 'timeeffect_beta_1_1_d.tgz'.
 - Copy the file 'timeeffect_beta_1_1_d.tgz' to the directory beneath the timeeffect
   install directory (e.g. if your TIMEEFFECT is located in 'srv/www/htdocs/timeeffect'
   copy the file to '/srv/www/htdocs')
 - untar the file.
 - copy your local copy of 'timeeffect/include/aperetiv.inc.php' and 'timeeffect/include/layout.inc.php' to
   'timeeffect_beta_1_1_d/include'.
 - remove the sub folder 'timeeffect_beta_1_1_d/install'.
 - the new release is located in the new subfolder 'timeeffect_beta_1_1_d'. To restore your old system
   rename or remove the subfolder where your old installtion was located. Then rename the new subfolder
   'timeeffect_beta_1_1_d' to the name of the prior renamed/removed old subfolder.


NOTES.beta_1_1_e.
=================

This release adds a UNIX like user model and a mechanism to charge efforts

----------------------------------------------------
Since we made major changes to the TIMEEFFECT data model there is no accurate way to
update from any prior versions of TIMEEFFECT.
Please install TIMEEFFECT from scratch.


NOTES.beta_1_1_f.
=================

This release adds a UNIX like user model and a mechanism to charge efforts

----------------------------------------------------
Since we made major changes to the TIMEEFFECT data model there is no accurate way to
update from any prior versions of TIMEEFFECT.
Please install TIMEEFFECT from scratch.


NOTES.beta_1_1_g.
=================

This release adds a UNIX like user model and a mechanism to charge efforts

----------------------------------------------------
Since we made major changes to the TIMEEFFECT data model there is no accurate way to
update from any prior versions of TIMEEFFECT.
Please install TIMEEFFECT from scratch.


NOTES.beta_1_1_h.
=================

This release adds CSV reports and the possibilty to filter
reports by user

----------------------------------------------------
UPDATE:

Beta 1.1.g:
simply make a backup copy of the files
	'include/aperetiv.inc.php'
and 
	'include/layout.inc.php'
Untar the TIMEEFFECT package to the appropriate directory and restore the backup copies.


Former releases:
Since we made major changes to the TIMEEFFECT data model there is no accurate way to
update from any prior versions of TIMEEFFECT.
Please install TIMEEFFECT from scratch.
