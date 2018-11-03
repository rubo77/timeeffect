TIMEEFFECT
==========

# 1. Dependencies
To run TIMEEFFECT you need a working MySQL database server (version 3.23 or higher).

Furthermore you need a running a webserver with PHP 5 support.

# 2. Preparation
2.1 MySQL
Before you actually install the TIMEEFFECT package you have to create a new database and a database user with
SELECT, INSERT, UPDATE, DELETE rights on the created database within your MySQL system .
By default the prepared database name is `timeffect`, the appropriate user
is `timeeffect` with the password `PfTe04`. If you stick to those parameters you won't have to
change the data during the installation of the system.

## 2.2	PHP
To have TIMEEFFECT running you need to set the value of the directive 'short_open_tags' in
your php.ini to `On` (`short_open_tags = On`). You can figure out where your php.ini
is located by creating a php script with the following content: <?php phpinfo(); ?>.
By opening this script in your browser you will get a detailed overview of your PHP settings.

# 3. Installation
For the final installation of TIMEEFFECT now extract the contents of this repository in a directory which
is located under the document root of your web server.
Now open the installation script `http://www.somedoain.com/timeeffect/install/` in your web-browser.

# 4. Customizing
After Installation you should edit the file `aperetiv.inc.php` which is located in the directory `include`.
Enter the data that matches your local installation.
