MySQL CLI Manual
================

Upload this script to a site to send raw MySQL commands.

Once uploaded access it from your web-browser.

Use the input boxes to fill in the site's MySQL credentials and start typing in commands.


## Configuration

On the top of the script there are several available configuration variables

### db_type
Variable type: String  
Default value: mysqli  
Acceptable values: "mysql", "mysqli"

Defines the version of the MySQL driver. Latest versions of PHP do not support the old "mysql" driver, so the newer version "mysqli" is selected by default.

