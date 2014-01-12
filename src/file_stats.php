<?php

/**
 * File Stats - Mini Utils
 *
 * @version 1.1
 * @author Creative Pulse
 * @copyright Creative Pulse 2013-2014
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


class CpMiniUtils_FileStats {

	// configurable variables

	public $msg_html_format = true;
	public $msg_show_alerts = true;
	public $msg_show_errors = true;

	public $query_type = 'full'; // values: full, fast

	public $process_links = true;

	public $opt_in_dirs = array();
	public $opt_out_dirs = array();

	public $time_limit = 0;

	public $show_statistics = true;


	// result variables

	public $stats_dir_count = 0;
	public $stats_dir_link_count = 0;
	public $stats_dir_accessible_count = 0;
	public $stats_file_count = 0;
	public $stats_file_size = 0;
	public $stats_file_link_count = 0;
	public $stats_file_extension_count = array();
	public $stats_file_readable_count = 0;
	public $stats_file_executable_count = 0;


	// system variables - do not edit

	public $basedir;


	function __construct() {
		$this->basedir = dirname(__FILE__);
	}

	public function msg($msg) {
		if ($this->msg_html_format) {
			$msg = htmlspecialchars($msg);
			$msg = str_replace("\t", '&nbsp; &nbsp; ', $msg);
			$msg .= ' <br/>';
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

	function bytes_str($size) {
		if ($size <= 1024) {
			return number_format($size / 1024, 2, '.', ',') . ' KiB';
		}

		$size /= 1024;
		if ($size <= 1024) {
			return number_format($size, 0, '.', ',') . ' KiB';
		}

		$size /= 1024;
		if ($size <= 1024) {
			return number_format($size, 0, '.', ',') . ' MiB';
		}

		$size /= 1024;
		if ($size <= 1024) {
			return number_format($size, 0, '.', ',') . ' GiB';
		}

		$size /= 1024;        
		return number_format($size, 0, '.', ',') . ' TiB';
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

	public function process_dir_fast($dir) {
		if ($dp = @opendir($dir)) {
			$sub_dirs = array();

			while (false !== ($file = readdir($dp))) {
				if ($file == '.' || $file == '..') {
					continue;
				}

				$filename = $dir . '/' . $file;
				if ($filename == __FILE__) {
					continue;
				}

				$is_link = is_link($filename);
				if ($is_link && !$this->process_links) {
					continue;
				}

				if (is_file($filename)) {
					$this->stats_file_count++;

					$this->stats_file_size += filesize($filename);
				}
				else if (is_dir($filename)) {
					if (in_array($filename, $this->opt_out_dirs)) {
						$this->msg_alert('Ignoring directory: ' . $filename);
						continue;
					}

					$this->stats_dir_count++;

					$sub_dirs[] = $filename;
				}
				else {
					$this->msg_alert('Ignoring non-regular file: ' . $filename);
				}
			}
			closedir($dp);

			foreach ($sub_dirs as $sub_dir) {
				$this->process_dir_fast($sub_dir);
			}
		}
		else {
			$this->msg_error('Inaccessible directory: ' . $dir);
		}
	}

	public function process_dir_full($dir) {
		if ($dp = @opendir($dir)) {
			$sub_dirs = array();

			while (false !== ($file = readdir($dp))) {
				if ($file == '.' || $file == '..') {
					continue;
				}

				$filename = $dir . '/' . $file;
				if ($filename == __FILE__) {
					continue;
				}

				$is_link = is_link($filename);
				if ($is_link && !$this->process_links) {
					continue;
				}

				if (is_file($filename)) {
					$this->stats_file_count++;

					if ($is_link) {
						$this->stats_file_link_count++;
					}

					if (is_readable($filename)) {
						$this->stats_file_readable_count++;
					}

					if (is_executable($filename)) {
						$this->stats_file_executable_count++;
					}

					if (preg_match('~\.([^.]+)$~', $file, $m) && strlen($file) > strlen($m[0])) {
						if (!isset($this->stats_file_extension_count[$m[1]])) {
							$this->stats_file_extension_count[$m[1]] = 1;
						}
						else {
							$this->stats_file_extension_count[$m[1]]++;
						}
					}

					$this->stats_file_size += filesize($filename);
				}
				else if (is_dir($filename)) {
					if (in_array($filename, $this->opt_out_dirs)) {
						$this->msg_alert('Ignoring directory: ' . $filename);
						continue;
					}

					$this->stats_dir_count++;

					if ($is_link) {
						$this->stats_dir_link_count++;
					}

					if (is_readable($filename) && is_executable($filename)) {
						$this->stats_dir_accessible_count++;
					}

					$sub_dirs[] = $filename;
				}
				else {
					$this->msg_alert('Ignoring non-regular file: ' . $filename);
				}
			}
			closedir($dp);

			foreach ($sub_dirs as $sub_dir) {
				$this->process_dir_full($sub_dir);
			}
		}
		else {
			$this->msg_error('Inaccessible directory: ' . $dir);
		}
	}

	public function run() {
		$this->validate_directories();

		set_time_limit($this->time_limit);

		$this->stats_dir_count = 0;
		$this->stats_dir_link_count = 0;
		$this->stats_dir_accessible_count = 0;
		$this->stats_file_count = 0;
		$this->stats_file_size = 0;
		$this->stats_file_link_count = 0;
		$this->stats_file_extension_count = array();
		$this->stats_file_readable_count = 0;
		$this->stats_file_executable_count = 0;


		if ($this->query_type == 'full') {
			if (empty($this->opt_in_dirs)) {
				$this->process_dir_full($this->basedir);
			}
			else {
				foreach ($this->opt_in_dirs as $directory) {
					$this->process_dir_full($directory);
				}
			}
		}
		else if ($this->query_type == 'fast') {
			if (empty($this->opt_in_dirs)) {
				$this->process_dir_fast($this->basedir);
			}
			else {
				foreach ($this->opt_in_dirs as $directory) {
					$this->process_dir_fast($directory);
				}
			}
		}
	}
}


$proc = new CpMiniUtils_FileStats();

// Example opt-in for Wordpress content files
//$proc->opt_in_dirs[] = 'wp-content';

// Example opt-in for Joomla modules, front-end components, back-end components, plugins
//$proc->opt_in_dirs = array('modules', 'components', 'administrator/components', 'plugins');

// Example opt-out for a Git database
//$proc->opt_out_dirs[] = '.git';

// Example on how to choose the 'fast' version of the query instead of the default 'full' version
//$proc->query_type = 'fast';

try {
	$proc->run();

	if ($proc->show_statistics) {
		if ($proc->query_type == 'full') {
			$proc->msg("Statistics for {$proc->basedir}");

			$proc->msg("\t{$proc->stats_dir_count} directories found");
			if ($proc->stats_dir_count > 0) {
				$proc->msg("\t\t{$proc->stats_dir_link_count} of them are links (" . number_format(100 * $proc->stats_dir_link_count / $proc->stats_dir_count) . "%)");
				$proc->msg("\t\t{$proc->stats_dir_accessible_count} of them are accessible (" . number_format(100 * $proc->stats_dir_accessible_count / $proc->stats_dir_count) . "%)");
			}

			$proc->msg("\t{$proc->stats_file_count} files found");
			if ($proc->stats_file_count > 0) {
				$proc->msg("\t\tTheir total size is " . number_format($proc->stats_file_size) . " bytes (" . $proc->bytes_str($proc->stats_file_size) . ")");
				$proc->msg("\t\t{$proc->stats_file_link_count} of them are links (" . number_format(100 * $proc->stats_file_link_count / $proc->stats_file_count) . "%)");
				$proc->msg("\t\t{$proc->stats_file_readable_count} of them are readable (" . number_format(100 * $proc->stats_file_readable_count / $proc->stats_file_count) . "%)");
				$proc->msg("\t\t{$proc->stats_file_executable_count} of them are executable (" . number_format(100 * $proc->stats_file_executable_count / $proc->stats_file_count) . "%)");

				$proc->msg("\tFile extensions ordered by volume:");
				arsort($proc->stats_file_extension_count);
				foreach ($proc->stats_file_extension_count as $ext => $count) {
					$proc->msg("\t\t$ext : $count");
				}
			}
		}
		else if ($proc->query_type == 'fast') {
			$proc->msg("Statistics for {$proc->basedir}");
			$proc->msg("\t{$proc->stats_dir_count} directories found");
			$proc->msg("\t{$proc->stats_file_count} files found");
			if ($proc->stats_file_count > 0) {
				$proc->msg("\t\tTheir total size is " . number_format($proc->stats_file_size) . " bytes (" . $proc->bytes_str($proc->stats_file_size) . ")");
			}
		}
	}
}
catch (Exception $e) {
	$proc->msg_error('Error: ' . $e->getMessage());
}

?>