Find in files Manual
====================

Upload this script to a site to search the contents of files.

Once uploaded access it from your web-browser.

You can perform searches in two ways. You can use the URL to set the search terms or edit the script itself.


## 1. Find in files from the URL

Once the file is uploaded to your web site, you can use the "q" variable (stands for Query) to set the search term.

Example search for "term":  
`http://www.example.com/find_in_files.php?q=term`

Example search for a phrase that contains spaces like "to do item":  
`http://www.example.com/find_in_files.php?q=to+do+item`

URLs have rules on how to escape special characters (characters other than alphabetical letters or numbers). Spaces for example, must be written either as `%20` or as `+` like in the second example above. The URL functionality of this script is designed to facilitate simple searches.

If you need to search for more complicated terms you should either use a tool to properly escape the URL, like for example <http://www.webatic.com/run/convert/url.php>, or edit the file and write your complex searches inside the script.


## 2. Find in files from the script

The script gives you a number of configurable variables to make complex searches. The next section of this manual explains all the available variables in detail.

When you write your search terms in the script instead of the URL, the way to perform the search is a simple visit to the main file. So, editing the script and then visiting `http://www.example.com/find_in_files.php` with your browser is the second way to perform a search.


## 3. Getting results

The results from this script are outputted to the page if the script runs for the web, or to stdout if it runs from a terminal.

In cases that results are too many for the web page, you can use the configuration variable `$output_filename` to make the script write them in a text file. You may optionally enable the configuration variable `$output_gzip` to compress the output file.

At the end of the search process, the script shows you statistics on the results. You may disable them by modifying the `$show_statistics` configuration variable.

Whatever the case, the script will collect statistics. If you just need the statistics and no output at all, you may completely disable the results output by setting `$output_enabled` and `$show_statistics` to `false`. Then you can use variables described in section 5 to read the statistics.


## 4. Configuration variables

The following are variables that give you ways to control your search.

Knowledge of the programming language PHP is required.


### 4.1. $msg_html_format

Variable type: Boolean  
Default value: true

If set to true, the scripts outputs messages (search results, alerts, errors) in HTML format. It is useful when you access the script from a web browser. Set to false if you access the script from the command line.


### 4.2. $msg_show_alerts

Variable type: Boolean  
Default value: true

If set to true, it shows alerts. Alerts are messages whose importance is somewhere between a notice and a warning. Alerts can inform you that a certain directory will be ignored, or that a file was not a regular file, or that a file is over-sized.


### 4.3. $msg_show_errors

Variable type: Boolean  
Default value: true

If set to true, it shows errors.


### 4.4. $opt_in_dirs

Variable type: Array  
Default value: \<empty\>

When this variable is empty, the script searches in all the sub-directories except those mentioned in `$opt_out_dirs`. If this variable is set, the script searches only in the directories specified.

Example 1: Add opt-in directory for WordPress content files  
`$proc->opt_in_dirs[] = 'wp-content';`

Example 2: Add opt-in directories for Joomla modules, front-end components, back-end components, plugins  
`$proc->opt_in_dirs = array('modules', 'components', 'administrator/components', 'plugins');`


### 4.5. $opt_out_dirs

Variable type: Array  
Default value: \<empty\>

Use this variable to add directories that should be ignored in the search process.

Example: Add opt-out directory for a Git database  
`$proc->opt_out_dirs[] = '.git';`


### 4.6. $extension_list

Variable type: String  
Default value: `html,htm,php,css,js,json,xml,ini,txt,inc`

This variable is a comma separated list of file extensions that the script is allowed to search.

If you want to search only a specific type of files you can exit this variable accordingly. For example if you want to search only in CSS files you can change it to `css`.

A more complete list of text file extensions is:  
`html,htm,php,css,js,json,xml,ini,txt,inc,sql,log,csv,svg,xsl,pdf,php3`


### 4.7. $search_text

Variable type: Array  
Default value: \<empty\>

Add your plain text queries in this array. You can set only one search term, or more for multiple queries.

This variable performs case sensitive queries. Use the `$search_regex` for case insensitive or more advanced queries.

If the script is called from a web browser that makes use of the `q` URL variable (as explained in section 1) then the URL variable is automatically added in this array.

Either `$search_text` or `$search_regex` is required to be set.

When you use multiple search terms, the script matches a file if it finds any of them.

Example 1: Add one plain text search term  
`$proc->search_text[] = 'abc';`

Example 2: An alternative way to add one plain text search term  
`$proc->search_text = array('abc');`

Example 3: Add multiple plain text search terms  
`$proc->search_text[] = 'abc';`  
`$proc->search_text[] = 'def';`  
`$proc->search_text[] = 'ghi';`

Example 4: An alternative way to add multiple plain text search terms  
`$proc->search_text = array('abc', 'def', 'ghi');`


### 4.8. $search_regex

Variable type: Array  
Default value: \<empty\>

Add your regular expression queries in this array. You can set only one search term, or more for multiple queries.

This variable performs regular expression queries that conform to the preg_* functions of PHP. Read <http://www.php.net/preg_match> for more information on Perl compatible regular expressions in the PHP language. If you do not want to use regular expressions, use the `$search_text` variable instead.

If the script is called from a web browser that makes use of the `q` URL variable (as explained in section 1) then the URL variable is automatically added in the `$search_text` array.

Either `$search_text` or `$search_regex` is required to be set.

When you use multiple search terms, the script matches a file if it finds any of them.

Example 1: Add one regex search term  
`$proc->search_text[] = '/abc/i';`

Example 2: An alternative way to add one regex search term  
`$proc->search_text = array('/abc/i');`

Example 3: Add multiple regex search terms  
`$proc->search_text[] = '/abc/i';`  
`$proc->search_text[] = '/def/i';`  
`$proc->search_text[] = '/ghi/i';`

Example 4: An alternative way to add multiple regex search terms  
`$proc->search_text = array('/abc/i', '/def/i', '/ghi/i');`


### 4.9. $max_file_size

Variable type: Integer  
Default value: 300000

This is the maximum file size the script is allowed to access. The value represents bytes. Hint: 1024 bytes is 1 KiB.

Text files are usually much smaller than images or other type of media files. The use of this variable ensures that there will not be a memory overuse by searching in large media files.


### 4.10. $accept_links

Variable type: Boolean  
Default value: true

If set to true, the script will process symlinks to files.


### 4.11. $time_limit

Variable type: Integer  
Default value: 0

This variable represents the time limit the script is allowed to run. The value represents seconds. If the value is 0, the script will not time-out. Hint: 60 seconds is 1 minute, 300 seconds is 5 minutes.

Usually web servers set a time limit of 30 seconds for preparing and serving a web page. In our case the script may have to deal with a large number of files so it may exceed this limit. That is why the default time-out feature is disabled in this script by default. If you want to set a time-out, set a number of seconds in this variable.


### 4.12. $output_enabled

Variable type: Boolean  
Default value: true

When set, the script will publish search results. Disable this feature if you only need the search results.


### 4.13. $output_filename

Variable type: String  
Default value: \<empty\>

When this variable is empty, the script will output the search results in the screen. When it's set, the script will write them in that file instead.

You may optionally use this variable in conjunction with `$output_gzip` to compress the output.


### 4.14. $output_gzip

Variable type: Boolean  
Default value: false

This variable works only when `$output_filename` is set. When enabled, the script will gzip (compress) the output file.


### 4.15. $show_statistics

Variable type: Boolean  
Default value: true

When set the script will show statistics at the completion of the search process.


## 5. Result variables

During the search process the script gathers the following statistical information.


### 5.1. $stat_dirs

Variable type: Integer

Measures the number of sub-directories the script found.


### 5.2. $stat_files

Variable type: Integer

Measures the number of all files in all directories and sub-directories the script found.


### 5.3. $stat_files_searchable

Variable type: Integer

Measures the number of searchable files in all directories and sub-directories the script found.

Searchable files are the ones file extensions defined in the configuration variable `$extension_list`.


### 5.4. $stat_matches

Variable type: Integer

Measures the number of positive file matches.

Notice: The script ignores multiple matches in a file. If there are several matches in one file, the script counts them as one.
