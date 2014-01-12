<?php

/**
 * EXIF Analyzer - Mini Utils
 *
 * @version 1.0
 * @author Creative Pulse
 * @copyright Creative Pulse 2014
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


if (!function_exists('exif_read_data')) {
	die('Error: The required function exif_read_data() is not available in your system, probably because you need to have the EXIF library installed.');
}

class CpMiniUtils_ExifAnalyzer {

	// configurable variables

	public $msg_html_format = true;
	public $msg_show_alerts = true;
	public $msg_show_errors = true;

	public $process_links = true;

	public $opt_in_dirs = array();
	public $opt_out_dirs = array();

	public $time_limit = 0;

	public $output_filename = '';
	public $output_gzip = false;

	public $show_analysis = true;
	public $infection_scan = true;
	public $show_statistics = true;


	// result variables

	public $stat_dirs_processed;
	public $stat_files_processed;
	public $stat_files_infected;


	// system variables - do not edit

	public $fp;
	public $basedir;
	public $basedir_len;
	private $output_filename_fullpath = '';


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
			if (strpos($directory, '..') !== false || strpos($directory, "\0") !== false) {
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

	public function report($msg) {
		if ($this->output_filename == '') {
			$this->msg($msg);
		}
		else if ($this->output_gzip) {
			gzwrite($this->fp, $msg . "\n");
		}
		else {
			fwrite($this->fp, $msg . "\n");
		}
	}

	public function process_dir($dir) {
		$this->stat_dirs_processed++;

		$dirs = array();
		$files = array();
		if ($dp = @opendir($dir)) {
			while (false !== ($file = readdir($dp))) {
				if ($file == '.' || $file == '..') {
					continue;
				}

				$filename = $dir . '/' . $file;

				if (!$this->process_links && is_link($filename)) {
					continue;
				}

				if (is_dir($filename)) {
					if (in_array($filename, $this->opt_out_dirs)) {
						$this->msg_alert('Ignoring directory: ' . $filename);
						continue;
					}

					$dirs[] = $filename;
				}
				else if (is_file($filename)) {
					if (preg_match('/(\.jpg|\.jpeg|\.tif|\.tiff)$/i', $file)) {
						$files[] = $filename;
					}
				}
				else {
					$this->msg_alert('Ignoring non-regular file: ' . $filename);
				}
			}
			closedir($dp);


			sort($files);
			foreach ($files as $file) {
				$this->stat_files_processed++;

				if ($this->show_analysis || $this->infection_scan) {
					$data = var_export(exif_read_data($file), true);
					$file = substr($file, $this->basedir_len);
				}

				$infected = false;
				if ($this->infection_scan && preg_match('/eval|base64/i', $data)) {
					$infected = true;
				}

				if ($this->show_analysis || $infected) {
					$this->report($file);
					$this->report(str_repeat('=', strlen($file)));

					if ($this->show_analysis) {
						$this->report($data);
					}

					if ($this->show_analysis && $infected) {
						$this->report('');
					}

					if ($infected) {
						$this->report('Infection traces found. File seems to be corrupted.');
						$this->stat_files_infected++;
					}

					$this->report("\n");
				}
			}


			sort($dirs);
			foreach ($dirs as $sub_dir) {
				$this->process_dir($sub_dir);
			}
		}
		else {
			$this->msg_error('Inaccessible directory: ' . $dir);
		}
	}

	public function run() {
		$this->validate_directories();

		set_time_limit($this->time_limit);

		if ($this->output_filename != '') {
			$this->output_filename_fullpath = $this->basedir . '/' . $this->output_filename;

			if ($this->output_gzip) {
				$this->fp = @gzopen($this->output_filename_fullpath, 'w9');
			}
			else {
				$this->fp = @fopen($this->output_filename_fullpath, 'w');
			}

			if (!$this->fp) {
				throw new CpSimpleUtils_ListFiles_Exception('Error on opening output file: ' . $this->output_filename_fullpath);
			}
		}

		$this->stat_dirs_processed = 0;
		$this->stat_files_processed = 0;
		$this->stat_files_infected = 0;

		if (empty($this->opt_in_dirs)) {
			$this->process_dir($this->basedir);
		}
		else {
			foreach ($this->opt_in_dirs as $directory) {
				$this->process_dir($directory);
			}
		}

		if ($this->output_filename != '') {
			if ($this->output_gzip) {
				gzclose($this->fp);
			}
			else {
				fclose($this->fp);
			}
		}
	}

}

$proc = new CpMiniUtils_ExifAnalyzer();

// Example opt-in for Wordpress content files
//$proc->opt_in_dirs[] = 'wp-content';

// Example opt-in for Joomla modules, front-end components, back-end components, plugins
//$proc->opt_in_dirs = array('modules', 'components', 'administrator/components', 'plugins');

// Example opt-out for a Git database
//$proc->opt_out_dirs[] = '.git';

try {
	$proc->run();

	if ($proc->show_statistics) {
		$infection_msg = '';

		if ($proc->infection_scan) {
			$infection_msg = " Found {$proc->stat_files_infected} files that are probably infected.";
		}

		$proc->msg("Processed {$proc->stat_dirs_processed} directories and {$proc->stat_files_processed} files." . $infection_msg);
	}
}
catch (Exception $e) {
	$proc->msg_error('Error: ' . $e->getMessage());
}

?>