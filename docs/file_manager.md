File Manager Manual
===================

Upload this script to a site to view and edit files. 

Once uploaded access it from your web-browser.

You can use this program to browse files in your web server and edit text files.


## 1. Usage

Upload the file (usually with an FTP client) to your web server and access it with your web-browser.

The first thing you will notice is a list of directories and files, like the one you usually see with MS Windows Explorer or OSX Finder.

You can move into directories, edit and upload files. You can use the controls on the top of the page to move back to parent directories.


## 2. Configuration variables

The following are variables that give you ways to control the file manager.

Knowledge of the programming language PHP is required.


### 2.1. $columns

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


### 2.2. $text_file_extensions

Variable type: String  
Default value: "txt,ini,md,markdown,js,css,less,sass,scss,php,php3,htm,html,xml,atom,rss,xsl,dtd,h,c,cpp,c++,m,as,py,rb,pl,tcl,pas,svg,vb,asp,aspx,cgi,bat,htaccess"

Comma separated list of text file extensions in lower case. When text files are opened, a text editor becomes available to allow you to edit them.


### 2.3. $image_file_extensions

Variable type: String  
Default value: "jpg,jpeg,png,gif,bmp"

Comma separated list of image file extensions in lower case. When image files are opened the system uses the web-browser's native image controls to display them.


### 2.4. $date_time_zone

Variable type: String  
Default value: \<empty\>

If set, the system shows file dates/times according to that timezone.

Read http://www.php.net/manual/en/timezones.php for a full list of supported timezones.


### 2.5. $date_format_same_day

Variable type: String  
Default value: "H:i"

Format for dates when the date to be shown is in the current day.

Read http://www.php.net/manual/en/function.date.php for a full list of symbols for the date format.


### 2.6. $date_format_same_year

Variable type: String  
Default value: "j M, H:i"

Format for dates when the date to be shown is in the current year.

Read http://www.php.net/manual/en/function.date.php for a full list of symbols for the date format.


### 2.7. $date_format_global

Variable type: String  
Default value: "j M Y, H:i"

Format for dates when the date to be shown is older than the current year.

Read http://www.php.net/manual/en/function.date.php for a full list of symbols for the date format.


## 3. Translation

File Manager allows you to translate the messages and captions of the script.

In order to accomplish that, you will simply need to edit the file.

Open the file with your editor and scroll down towards the end. Locate the function tt (stands for TranslaTe). In that function you can find the words and phrases used by the script. Change the second part of each line, not the first one as it is the handle used by the script.

A few important assumptions before you proceed to your translation:

1. Your editor must support UTF-8 without BOM.
2. You need to know HTML so that you translate only the natural language words and not get confused by HTML tags.
3. You need to know some basic PHP syntax so that you won't get confused with PHP strings.

