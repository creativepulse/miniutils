File Stats Manual
=================

Upload this script to a site to get summarized statistics of files and sub-directories.

Once uploaded access it from your web-browser.


## 1. Full queries vs. Fast queries

By default the script will show you the entire set of statistics it can collect from your files and sub-directories.

You can use the configuration variable `$query_type` to change to the fast version of the query. In that case the script will show you fewer statistics but it will run faster. That can be helpful in case your website has too many files and/or sub-directories and you only need to know how many files you have in total and what is their accumulated size.


## 2. Getting results

The results from this script are outputted to the page if the script runs for the web, or to stdout if it runs from a terminal.

You may completely disable the results output by setting `$show_statistics` to `false`. Then you can use variables described in section 3 to read the statistics.


## 3. Configuration variables

The following are variables that give you ways to control your search.

Knowledge of the programming language PHP is required.


### 3.1. $msg_html_format

Variable type: Boolean  
Default value: true

If set to true, the scripts outputs messages (search results, alerts, errors) in HTML format. It is useful when you access the script from a web browser. Set to false if you access the script from the command line.


### 3.2. $msg_show_alerts

Variable type: Boolean  
Default value: true

If set to true, it shows alerts. Alerts are messages whose importance is somewhere between a notice and a warning. Alerts can inform you that a certain directory will be ignored, or that a file was not a regular file.


### 3.3. $msg_show_errors

Variable type: Boolean  
Default value: true

If set to true, it shows errors.


### 3.4. $query_type

Variable type: String  
Default value: "full"  
Acceptable values: "full", "fast"

When set to "full" the script will show full statistics and it will run relatively slower.

When set to "fast" the script will show fewer statistics (total file count, total file size, total sub-directories) and it will run relatively faster.


### 3.5. $process_links

Variable type: Boolean  
Default value: true

If set to true, the script will process symlinks to files or directories.


### 3.6. $opt_in_dirs

Variable type: Array  
Default value: \<empty\>

When this variable is empty, the script searches in all the sub-directories except those mentioned in `$opt_out_dirs`. If this variable is set, the script searches only in the directories specified.

Example 1: Add opt-in directory for Wordpress content files  
`$proc->opt_in_dirs[] = 'wp-content';`

Example 2: Add opt-in directories for Joomla modules, front-end components, back-end components, plugins  
`$proc->opt_in_dirs = array('modules', 'components', 'administrator/components', 'plugins');`


### 3.5. $opt_out_dirs

Variable type: Array  
Default value: \<empty\>

Use this variable to add directories that should be ignored in the search process.

Example: Add opt-out directory for a Git database  
`$proc->opt_out_dirs[] = '.git';`


### 3.6. $time_limit

Variable type: Integer  
Default value: 0

This variable represents the time limit the script is allowed to run. The value represents seconds. If the value is 0, the script will not time-out. Hint: 60 seconds is 1 minute, 300 seconds is 5 minutes.

Usually web servers set a time limit of 30 seconds for preparing and serving a web page. In our case the script may have to deal with a large number of files so it may exceed this limit. That is why the default time-out feature is disabled in this script by default. If you want to set a time-out, set a number of seconds in this variable.


### 3.7. $show_statistics

Variable type: Boolean  
Default value: true

When set the script will show statistics at the completion of the search process.


## 4. Result variables


### 4.1. $stats_dir_count

Variable type: Integer  
Available in full queries: Yes  
Available in fast queries: Yes

Measures the number of sub-directories the script found.


### 4.2. $stats_dir_link_count

Variable type: Integer  
Available in full queries: Yes  
Available in fast queries: No

Measures the number of symlinks to sub-directories. This statistic is a subset of the `$stats_dir_count` result.


### 4.3. $stats_dir_accessible_count

Variable type: Integer  
Available in full queries: Yes  
Available in fast queries: No

Measures the number of sub-directories having read and execute permissions, therefore accessible. This statistic is a subset of the `$stats_dir_count` result.


### 4.4. $stats_file_count

Variable type: Integer  
Available in full queries: Yes  
Available in fast queries: Yes

Measures the number of files the script found.


### 4.5. $stats_file_size

Variable type: Integer  
Available in full queries: Yes  
Available in fast queries: Yes

Measures the total size of files the script found.


### 4.6. $stats_file_link_count

Variable type: Integer  
Available in full queries: Yes  
Available in fast queries: No

Measures the number of symlinks to files. This statistic is a subset of the `$stats_file_count` result.


### 4.7. $stats_file_extension_count

Variable type: Array  
Available in full queries: Yes  
Available in fast queries: No

Contains an associative array of file extensions to total count. You can use this statistic to see what kind of file extensions there are and at what volume.


### 4.8. $stats_file_readable_count

Variable type: Integer  
Available in full queries: Yes  
Available in fast queries: No

Measures the number of files with read permissions. This statistic is a subset of the `$stats_file_count` result.


### 4.9. $stats_file_executable_count

Variable type: Integer  
Available in full queries: Yes  
Available in fast queries: No

Measures the number of files with execute permissions. This statistic is a subset of the `$stats_file_count` result.
