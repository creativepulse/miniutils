<?php

/**
 * Find in files - Mini Utils
 *
 * @version 1.0
 * @author Creative Pulse
 * @copyright Creative Pulse 2013
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


class CpMiniUtils_FindInFiles {

    // configurable variables

    public $msg_html_format = true;
    public $msg_show_alerts = true;
    public $msg_show_errors = true;

    public $opt_in_dirs = array();
    public $opt_out_dirs = array();

    //public $extension_list = 'html,htm,php,css,js,json,xml,ini,txt,inc,sql,log,csv,svg,xsl,pdf,php3'; // full list
    //public $extension_list = 'html,htm,php,css,js,json,xml,ini,txt,inc'; // popular extensions
    public $extension_list = 'html,htm,php,css,js,json,xml,ini,txt,inc';

    public $search_text = array();
    public $search_regex = array();

    public $max_file_size = 300000;

    public $accept_links = true;

    public $time_limit = 0;

    public $output_enabled = true;
    public $output_filename = '';
    public $output_gzip = false;

    public $show_statistics = true;


    // result variables

    public $stat_dirs = 0;
    public $stat_files = 0;
    public $stat_files_searchable = 0;
    public $stat_matches = 0;


    // system variables - do not edit

    public $basedir;
    public $basedir_len;

    public $fp;
    public $output_filename_fullpath = '';


    function __construct() {
        $this->basedir = dirname(__FILE__);
        $this->basedir_len = strlen($this->basedir) + 1;
    }

    public function msg($msg) {
        if ($this->msg_html_format) {
            $msg = htmlspecialchars($msg) . ' <br/>';
        }
        echo $msg . "\n";
    }

    public function msg_alert($msg) {
        if ($this->msg_show_alerts) {
            $this->msg('!! ' . $msg);
        }
    }

    public function msg_error($msg) {
        if ($this->msg_show_errors) {
            $this->msg('** ' . $msg);
        }
    }

    public function validate_directory($directory, $ensure_exists) {
        $directory = preg_replace('~/{2,}~', '/', str_replace('\\', '/', trim($directory)));
        if ($directory != '') {
            if (strpos($directory, '..') !== false) {
                throw new Exception('Invalid directory: ' . $directory);
            }

            if ($directory[0] != '/') {
                $directory = '/' . $directory;
            }

            if (substr($directory, -1) == '/') {
                $directory = substr($directory, 0, -1);
            }

            $directory = $this->basedir . $directory;
            if ($ensure_exists) {
                if (!file_exists($directory)) {
                    throw new Exception('Directory does not exist: ' . $directory);
                }
                if (!is_dir($directory)) {
                    throw new Exception('Path is not a directory: ' . $directory);
                }
            }
        }

        return $directory;
    }

    public function validate_directories() {
        if (isset($this->opt_in_dirs) && is_string($this->opt_in_dirs)) {
            $this->opt_in_dirs = array($this->opt_in_dirs);
        }
        else if (!is_array($this->opt_in_dirs)) {
            $this->opt_in_dirs = array();
        }

        foreach ($this->opt_in_dirs as $k => $v) {
            if ($v == '') {
                throw new Exception('Base directory not allowed in opt-in list');
            }

            $this->opt_in_dirs[$k] = $this->validate_directory($v, true);
        }

        if (isset($this->opt_out_dirs) && is_string($this->opt_out_dirs)) {
            $this->opt_out_dirs = array($this->opt_out_dirs);
        }
        else if (!is_array($this->opt_out_dirs)) {
            $this->opt_out_dirs = array();
        }

        foreach ($this->opt_out_dirs as $k => $v) {
            if ($v == '') {
                throw new Exception('Base directory not allowed in opt-out list');
            }

            $this->opt_out_dirs[$k] = $this->validate_directory($v, false);
        }
    }

    public function process_dir($dir) {
        if ($dp = @opendir($dir)) {
            $files = array();
            $sub_dirs = array();

            while (false !== ($file = readdir($dp))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $filename = $dir . '/' . $file;
                if ($filename == __FILE__ || $filename == $this->output_filename_fullpath) {
                    continue;
                }

                if (!$this->accept_links && is_link($filename)) {
                    continue;
                }

                if (is_file($filename)) {
                    $this->stat_files++;

                    if (preg_match('~\.([^.]+)$~', $file, $m) && strlen($file) > strlen($m[0]) && in_array(strtolower($m[1]), $this->extensions)) {
                        $files[] = $filename;
                    }
                }
                else if (is_dir($filename)) {
                    if (in_array($filename, $this->opt_out_dirs)) {
                        $this->msg_alert('Ignoring directory: ' . $filename);
                        continue;
                    }

                    $this->stat_dirs++;
                    $sub_dirs[] = $filename;
                }
                else {
                    $this->msg_alert('Ignoring non-regular file: ' . $filename);
                }
            }
            closedir($dp);

            sort($files);
            foreach ($files as $file) {
                $size = filesize($file);
                if ($size > $this->max_file_size) {
                    $this->msg_alert('Ignoring oversized file: ' . $file);
                    continue;
                }

                $this->stat_files_searchable++;

                $found_it = false;
                $content = file_get_contents($file);

                foreach ($this->search_text as $search) {
                    if (stripos($content, $search) !== false) {
                        $found_it = true;
                        break;
                    }
                }

                if (!$found_it) {
                    foreach ($this->search_regex as $search) {
                        if (preg_match($search, $content)) {
                            $found_it = true;
                            break;
                        }
                    }
                }

                if ($found_it) {
                    $this->stat_matches++;

                    if ($this->output_enabled) {
                        if ($this->output_filename == '') {
                            $this->msg($file);
                        }
                        else if ($this->output_gzip) {
                            gzwrite($this->fp, substr($file, $this->basedir_len) . "\r\n");
                        }
                        else {
                            fwrite($this->fp, substr($file, $this->basedir_len) . "\r\n");
                        }
                    }
                }
            }

            sort($sub_dirs);
            foreach ($sub_dirs as $sub_dir) {
                $this->process_dir($sub_dir);
            }
        }
        else {
            $this->msg_error('Inaccessible directory: ' . $dir);
        }
    }

    public function run() {
        $this->validate_directories();

        $this->extensions = explode(',', str_replace(' ', '', strtolower($this->extension_list)));
        if (empty($this->extensions)) {
            throw new Exception('File extensions are not set');
        }

        if (isset($_GET['q']) && $_GET['q'] != '' && !in_array($_GET['q'], $this->search_text)) {
            $this->search_text[] = $q;
        }

        if (empty($this->search_text) && empty($this->search_regex)) {
            throw new Exception('Search terms are not set');
        }

        set_time_limit($this->time_limit);

        if ($this->output_enabled && $this->output_filename != '') {
            $this->output_filename_fullpath = $this->basedir . '/' . $this->output_filename;

            if ($this->output_gzip) {
                $this->fp = @gzopen($this->output_filename_fullpath, 'w9');
            }
            else {
                $this->fp = @fopen($this->output_filename_fullpath, 'w');
            }

            if (!$this->fp) {
                throw new Exception('Error on opening output file: ' . $this->output_filename_fullpath);
            }
        }

        $this->stat_dirs = 0;
        $this->stat_files = 0;
        $this->stat_files_searchable = 0;
        $this->stat_matches = 0;

        if (empty($this->opt_in_dirs)) {
            $this->process_dir($this->basedir);
        }
        else {
            foreach ($this->opt_in_dirs as $directory) {
                $this->process_dir($directory);
            }
        }

        if ($this->output_enabled && $this->output_filename != '') {
            if ($this->output_gzip) {
                gzclose($this->fp);
            }
            else {
                fclose($this->fp);
            }
        }
    }
}


$proc = new CpMiniUtils_FindInFiles();

// Example opt-in for Wordpress content files
//$proc->opt_in_dirs[] = 'wp-content';

// Example opt-in for Joomla modules, front-end components, back-end components, plugins
//$proc->opt_in_dirs = array('modules', 'components', 'administrator/components', 'plugins');

// Example opt-out for a Git database
//$proc->opt_out_dirs[] = '.git';

// Example output in a regular text file
//$proc->output_filename = 'output.txt';

// Example output in a Gnu zipped (gzip) text file
//$proc->output_filename = 'output.txt.gz';
//$proc->output_gzip = true;

// Example search for plain text - You can add more lines for multiple queries
//$proc->search_text[] = 'abc';

// Example search for a regular expression (regex) - You can add more lines for multiple queries - Read http://www.php.net/preg_match for more info on the regex notation
//$proc->search_regex[] = '/abc/i';

try {
    $proc->run();

    if ($proc->show_statistics) {
        if ($proc->output_enabled && $proc->stat_matches > 0) {
            // show divider
            $proc->msg("---");
        }

        $proc->msg("Search statistics for: " . implode(', ', array_merge($proc->search_text, $proc->search_regex)));
        $proc->msg("Found {$proc->stat_matches} matches in {$proc->stat_files_searchable} searchable files. Search performed in {$proc->stat_dirs} sub-directories and {$proc->stat_files} files.");
    }
}
catch (Exception $e) {
    $proc->msg_error('Error: ' . $e->getMessage());
}
