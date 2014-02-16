File Manager Manual
===================

Upload this script to a site to view and edit files. 

Once uploaded access it from your web-browser.

You can use this program to browse files in your web server and edit text files.


## 1. Configuration variables

The following are variables that give you ways to control the file manager.

Knowledge of the programming language PHP is required.


### 1.1. $columns

Variable type: String  
Default value: "sizehuman,mtime,perm"

Comma separated list of column types. The following is a list of available options:

- **size** - Shows the size of files in a human readable form (for example 123KiB) and in a purely numeric form [1].
- **sizehuman** - Shows the size of files in a human readable form (for example 123KiB) [1].
- **sizebytes** - Shows the size of files in a purely numeric form.
- **ctime** - Shows the creation time of the file.
- **ctimets** - Shows the creation time of the file as a Unix Timestamp.
- **mtime** - Shows the last modification time of the file.
- **mtimets** - Shows the last modification time of the file as a Unix Timestamp.
- **atime** - Shows the last access time of the file.
- **atimets** - Shows the last access time of the file as a Unix Timestamp.
- **owner** - Shows the user-name of the owner of the file [2].
- **ownernum** - Shows the ID of the owner of the file [3].
- **group** - Shows the user-name of the group of the file [2].
- **groupnum** - Shows the ID of the group of the file [3].
- **perm** - Shows the file's permissions in a human readable form and in an octal number format [3].
- **permstr** - Shows the file's permissions in a human readable form [3].
- **permnum** - Shows the file's permissions in an octal number format [3].

Notice [1]: We use the kibi- (KiB) prefix standard instead of the commonly used kilo- (KB) prefix as it is more compliant to the way byte sizes are truly measured in computers.

Notice [2]: Works on Unix systems with the POSIX library installed.

Notice [3]: Works on Unix systems.


### 1.2. $text_file_extensions

Variable type: String  
Default value: "txt,ini,md,markdown,js,css,less,sass,scss,php,php3,htm,html,xml,atom,rss,xsl,dtd,h,c,cpp,c++,m,as,py,rb,pl,tcl,pas,svg,vb,asp,aspx,cgi,bat,htaccess"

Comma separated list of text file extensions in lower case. When text files are opened, a text editor becomes available to allow you to edit them.


### 1.3. $image_file_extensions

Variable type: String  
Default value: "jpg,jpeg,png,gif,bmp"

Comma separated list of image file extensions in lower case. When image files are opened the system uses the web-browser's native image controls to display them.


### 1.4. $date_time_zone

Variable type: String  
Default value: \<empty\>

If set, the system shows file dates/times according to that timezone.

Read http://www.php.net/manual/en/timezones.php for a full list of supported timezones.


### 1.5. $date_format_same_day

Variable type: String  
Default value: "H:i"

Format for dates when the date to be shown is in the current day.

Read http://www.php.net/manual/en/function.date.php for a full list of symbols for the date format.


### 1.6. $date_format_same_year

Variable type: String  
Default value: "j M, H:i"

Format for dates when the date to be shown is in the current year.

Read http://www.php.net/manual/en/function.date.php for a full list of symbols for the date format.


### 1.7. $date_format_global

Variable type: String  
Default value: "j M Y, H:i"

Format for dates when the date to be shown is older than the current year.

Read http://www.php.net/manual/en/function.date.php for a full list of symbols for the date format.
