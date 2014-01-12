EXIF Analyzer Manual
====================

Upload this script to a site to list the EXIF meta data of JPEG and TIFF image files. You may also use this script to search for images whose EXIF meta data have been injected with malware code such as viruses or botnet scripts.

Once uploaded access it from your web-browser.

You may use the configuration variables of this script to achieve different things. Read the following sections to learn how to accomplish common tasks with this utility.


## 1. List EXIF meta data from images

If you need to have a complete list of EXIF meta data from the images in your website, simply make sure the configuration variable `$show_analysis` is enabled.

You can use the variable `$output_filename` to make the script output the results to the page or to a plain text file. If you want them on a file you may also use the variable `$output_gzip` to compress it.


## 2. Detect infected images

If you need to scan the image files for malware, make sure the configuration variable `infection_scan` is enabled. When this variable is enabled, the script will show you an additional message letting you know that it found traces of common infections (searches for strings "eval" and "base64").

It is recommended to make this search manually using a full meta data list as described in section 1. The reason is that there are many ways malware can alter EXIF meta data that may not be anticipated by this script. A human specialist can use the results from this software to offer you a safer evaluation.

If you want to get a list of infected images only, enable the variable `infection_scan` and disable `show_analysis`.


## 3. Getting results

The results from this script are outputted to the page if the script runs for the web, or to stdout if it runs from a terminal.

In cases that results are too many for the web page, you can use the configuration variable `$output_filename` to make the script write them in a text file. You may optionally enable the configuration variable `$output_gzip` to compress the output file.

At the end of the analysis, the script shows you statistics on the results. You may disable them by modifying the `$show_statistics` configuration variable.


## 4. Configuration variables

The following sections describe the configurable variables of the script.

Knowledge of the programming language PHP is required.


### 4.1. $msg_html_format

Variable type: Boolean  
Default value: true

If set to true, the scripts outputs messages (search results, alerts, errors) in HTML format. It is useful when you access the script from a web browser. Set to false if you access the script from the command line.


### 4.2. $msg_show_alerts

Variable type: Boolean  
Default value: true

If set to true, it shows alerts. Alerts are messages whose importance is somewhere between a notice and a warning. Alerts can inform you that a certain directory will be ignored, or that a file was not a regular file.


### 4.3. $msg_show_errors

Variable type: Boolean  
Default value: true

If set to true, it shows errors.


### 4.4. $process_links

Variable type: Boolean  
Default value: true

If set to true, the script will process symlinks to files or directories.


### 4.5. $opt_in_dirs

Variable type: Array  
Default value: \<empty\>

When this variable is empty, the script processes all sub-directories except those mentioned in `$opt_out_dirs`. If this variable is set, the script processes only the directories specified.

Example 1: Add opt-in directory for WordPress content files  
`$proc->opt_in_dirs[] = 'wp-content';`

Example 2: Add opt-in directories for Joomla modules, front-end components, back-end components, plugins  
`$proc->opt_in_dirs = array('modules', 'components', 'administrator/components', 'plugins');`


### 4.6. $opt_out_dirs

Variable type: Array  
Default value: \<empty\>

Use this variable to add directories that should be ignored.

Example: Add opt-out directory for a Git database  
`$proc->opt_out_dirs[] = '.git';`


### 4.7. $time_limit

Variable type: Integer  
Default value: 0

This variable represents the time limit the script is allowed to run. The value represents seconds. If the value is 0, the script will not time-out. Hint: 60 seconds is 1 minute, 300 seconds is 5 minutes.

Usually web servers set a time limit of 30 seconds for preparing and serving a web page. In our case the script may have to deal with a large number of files so it may exceed this limit. That is why the default time-out feature is disabled in this script by default. If you want to set a time-out, set a number of seconds in this variable.


### 4.8. $output_filename

Variable type: String  
Default value: \<empty\>

When this variable is empty, the script will output the search results to the screen. When it's set, the script will write them in that file instead.

You may optionally use this variable in conjunction with `$output_gzip` to compress the output.


### 4.9. $output_gzip

Variable type: Boolean  
Default value: false

This variable works only when `$output_filename` is set. When enabled, the script will gzip (compress) the output file.


### 4.10. $show_analysis

Variable type: Boolean  
Default value: true

When set the script will output the entire EXIF meta data.


### 4.11. $infection_scan

Variable type: Boolean  
Default value: true

When set the script will add a message alerting you that the file is **probably** infected. The script searches for some common keywords ("eval" and "base64") used by malware. That does not guaranty that the script is capable to detect all types of infections. For a more definitive scan, enable the `$show_analysis` variable and let a human specialist analyze it.


### 4.12. $show_statistics

Variable type: Boolean  
Default value: true

When set the script will show statistics at the completion of the analysis.


## 5. Result variables

During the analysis the script gathers the following statistical information.


### 5.1. $stat_dirs_processed

Variable type: Integer

Measures the number of sub-directories the script processed.


### 5.2. $stat_files_processed

Variable type: Integer

Measures the number of JPEG and TIFF files the script processed. These are the file types that support EXIF.


### 5.3. $stat_files_infected

Variable type: Integer

Measures the number of files with traces of infections. This variable requires `$infection_scan` to be enabled.
