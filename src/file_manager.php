<?php

/**
 * File Manager - Mini Utils
 *
 * @version 1.1
 * @author Creative Pulse
 * @copyright Creative Pulse 2014
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


if (get_magic_quotes_gpc()) {
	$vars = array();
	foreach ($_GET as $k => $v) {
		$vars[$k] = stripslashes($v);
	}
	$_GET = $vars;

	$vars = array();
	foreach ($_POST as $k => $v) {
		$vars[$k] = stripslashes($v);
	}
	$_POST = $vars;

	unset($vars);
}

class CpMiniUtils_FileManager {

	// -------------------------- //
	//   configurable variables   //
	// -------------------------- //

	// available options: size,sizehuman,sizebytes,ctime,ctimets,mtime,mtimets,atime,atimets,owner,ownernum,group,groupnum,perm,permstr,permnum
	// default: sizehuman,mtime,perm
	public $columns = 'sizehuman,mtime,perm';

	// default: txt,ini,md,markdown,js,css,less,sass,scss,php,php3,htm,html,xml,atom,rss,xsl,dtd,h,c,cpp,c++,m,as,py,rb,pl,tcl,pas,svg,vb,asp,aspx,cgi,bat,htaccess
	public $text_file_extensions = 'txt,ini,md,markdown,js,css,less,sass,scss,php,php3,htm,html,xml,atom,rss,xsl,dtd,h,c,cpp,c++,m,as,py,rb,pl,tcl,pas,svg,vb,asp,aspx,cgi,bat,htaccess';

	// default: jpg,jpeg,png,gif,bmp
	public $image_file_extensions = 'jpg,jpeg,png,gif,bmp';

	public $date_time_zone = '';

	// default: H:i
	public $date_format_same_day = 'H:i';

	// default: j M, H:i
	public $date_format_same_year = 'j M, H:i';

	// default: j M Y, H:i
	public $date_format_global = 'j M Y, H:i';

	// -------------------------- //


	// system variables - do not edit

	public $version = '1.0';
	public $path = '';

	public $title = '';
	public $css = '';
	public $body = '';

	public function get_path_root(&$path) {
		if ($path == '') {
			return '';
		}

		if ($path[0] == '/') {
			// Unix root path
			$result = '/';
			$path = substr($path, 1);
		}
		else if (preg_match('~^[a-z]:/~i', $path, $m)) {
			// MS Windows root path
			$result = $m[0];
			$path = substr($path, strlen($result));
		}

		return $result;
	}

	public function show_breadcrumbs_header() {
		$path = preg_replace('~/{2,}~', '/', $this->path);
		if ($path == '') {
			return '[No path specified]';
		}

		$html = '';
		$link_fmt = '<a href="' . basename(__FILE__) . '?path=%s">%s</a>';
		$link = '';
		$previous_link = '';

		$link = $this->get_path_root($path);
		$html = sprintf($link_fmt, urlencode($link), htmlspecialchars($link));

		if ($path == '') {
			// ignore case
		}
		else if (strpos($path, '/') === false) {
			$previous_link = $link;
			$link .= $path;
			$html .= sprintf($link_fmt, urlencode($link), htmlspecialchars($path));
		}
		else {
			$path = explode('/', $path);
			for ($i = 0, $len = count($path) - 1; $i <= $len; $i++) {
				$previous_link = $link;
				$caption = ($i == 0 ? '' : '/') . $path[$i];
				$link .= $caption;
				$html .= sprintf($link_fmt, urlencode($link), htmlspecialchars($caption));
			}
		}

		if ($previous_link != '') {
			$html .= ' &nbsp; [ ' . sprintf($link_fmt, urlencode($previous_link), htmlspecialchars('Up')) . ' ]';
		}

		$this->body .=
'<div class="breadcrumbs_header">Path ' . $html . '</div>
';
	}

	public function show_file() {
		$this->show_breadcrumbs_header();

		$this->title = '[F] ' . $this->path;

		$handle = 'none';
		$basename = basename($this->path);
		$ext = strtolower(strrchr(basename($this->path), '.'));
		if ($ext != '') {
			$ext = ',' . substr($ext, 1) . ',';
		}

		if (strpos(',' . $this->image_file_extensions . ',', $ext) !== false) {
			// show image

			$script_path = preg_replace('~/{2,}~', '/', str_replace('\\', '/', dirname(__FILE__)));
			$image_path = preg_replace('~/{2,}~', '/', str_replace('\\', '/', dirname($this->path)));
			$relative_path = false;

			if ($script_path == $image_path) {
				$relative_path = '';
			}
			else {
				$script_path_root = $this->get_path_root($script_path);
				$image_path_root = $this->get_path_root($image_path);

				if ($script_path_root != $image_path_root) {
					$this->body .=
'<div class="error">Cannot show image from a different drive</div>
';
				}
				else {
					$script_branches = explode('/', $script_path);
					$image_branches = explode('/', $image_path);

					while (!empty($script_branches) && !empty($image_branches) && $script_branches[0] == $image_branches[0]) {
						array_shift($script_branches);
						array_shift($image_branches);
					}

					for ($i = 0, $len = count($script_branches); $i < $len; $i++) {
						array_unshift($image_branches, '..');
					}

					$relative_path = implode('/', $image_branches) . '/';
				}
			}

			if ($relative_path !== false) {				
				$this->body .=
'<div class="show_image"><img src="' . $relative_path . basename($this->path) . '" alt="Image out of reach"></div>
';
			}
		}
		else if (strpos(',' . $this->text_file_extensions . ',', $ext) !== false) {
			// show text file

			if (!is_readable($this->path) && !is_writable($this->path)) {
				$this->body .=
'<div class="error">File neither readable or writable</div>
';
			}
			else if (!is_readable($this->path)) {
				$this->body .=
'<div class="error">File is not readable</div>
';
			}
			else {
				if (empty($_POST)) {
					$content = file_get_contents($this->path);
				}
				else {
					if (isset($_POST['load'])) {
						$content = file_get_contents($this->path);
						$this->body .=
'<div class="notice">File loaded</div>
';
					}
					else {
						$content = $_POST['content'];
						file_put_contents($this->path, $content);
						$this->body .=
'<div class="notice">File saved</div>
';
					}
				}

				$this->body .=
'<div class="text_editor">
	<form name="frm" method="post" action="">

		<textarea name="content" rows="30">' . htmlspecialchars($content) . '</textarea>

		<input type="submit" name="load" value="Load">
		<input type="submit" name="save" value="Save"' . (is_writable($this->path) ? '' : ' disabled') . '>

	</form>
</div>
';
			}
		}
		else {
			// unhandled file
			$this->body .=
'<div class="notice">Unable to handle &quot;' . htmlspecialchars(substr($ext, 1, -1)) . '&quot; files</div>
';
		}
	}

	private function list_files_attr_date($dt) {
		static $current_day = null;
		static $current_year = null;

		if ($current_day === null) {
			$now = time();
			$current_day = date('Y-m-d', $now);
			$current_year = date('Y', $now);
		}

		if (date('Y-m-d', $dt) == $current_day) {
			return date($this->date_format_same_day, $dt);
		}
		else if (date('Y', $dt) == $current_year) {
			return date($this->date_format_same_year, $dt);
		}
		else {
			return date($this->date_format_global, $dt);
		}
	}

	private function list_files_attr($filename, $column_type, $regular_file) {
		$perms = 0;
		$perms_str = '';

		if ($column_type == 'perm' || $column_type == 'permstr' || $column_type == 'permnum') {
			$perms = fileperms($filename);

			if ($column_type == 'perm' || $column_type == 'permstr') {
				if (($perms & 0xC000) == 0xC000) {
					$perms_str = 's'; // Socket
				}
				else if (($perms & 0xA000) == 0xA000) {
					$perms_str = 'l'; // Symbolic Link
				}
				else if (($perms & 0x8000) == 0x8000) {
					$perms_str = '-'; // Regular
				}
				else if (($perms & 0x6000) == 0x6000) {
					$perms_str = 'b'; // Block special
				}
				else if (($perms & 0x4000) == 0x4000) {
					$perms_str = 'd'; // Directory
				}
				else if (($perms & 0x2000) == 0x2000) {
					$perms_str = 'c'; // Character special
				}
				else if (($perms & 0x1000) == 0x1000) {
					$perms_str = 'p'; // FIFO pipe
				}
				else {
					$perms_str = 'u'; // Unknown
				}

				// Owner
				$perms_str .= (($perms & 0x0100) ? 'r' : '-');
				$perms_str .= (($perms & 0x0080) ? 'w' : '-');
				$perms_str .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

				// Group
				$perms_str .= (($perms & 0x0020) ? 'r' : '-');
				$perms_str .= (($perms & 0x0010) ? 'w' : '-');
				$perms_str .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

				// World
				$perms_str .= (($perms & 0x0004) ? 'r' : '-');
				$perms_str .= (($perms & 0x0002) ? 'w' : '-');
				$perms_str .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
			}
		}


		$filesize = 0;
		$filesize_str = '';

		if ($regular_file && ($column_type == 'size' || $column_type == 'sizehuman' || $column_type == 'sizebytes')) {
			$filesize = filesize($filename);

			if ($column_type == 'size' || $column_type == 'sizehuman') {
				$size = $filesize;
				if ($size <= 1024) {
					$filesize_str = number_format($size / 1024, 2, '.', ',') . ' KiB';
				}
				else {
					$size /= 1024;
					if ($size <= 1024) {
						$filesize_str = number_format($size, 0, '.', ',') . ' KiB';
					}
					else {
						$size /= 1024;
						if ($size <= 1024) {
							$filesize_str = number_format($size, 0, '.', ',') . ' MiB';
						}
						else {
							$size /= 1024;
							if ($size <= 1024) {
								$filesize_str = number_format($size, 0, '.', ',') . ' GiB';
							}
							else {
								$size /= 1024;
								$filesize_str = number_format($size, 0, '.', ',') . ' TiB';
							}
						}
					}
				}
			}
		}


		$result = '';
		switch ($column_type) {
			case 'size':
				if ($regular_file) {
					$result = '<div class="sz">' . $filesize_str . ' [' . number_format(filesize($filename)) . ']</div>';
				}
				else {
					$result = '&nbsp;';
				}
				break;

			case 'sizehuman':
				if ($regular_file) {
					$result = '<div class="sz">' . $filesize_str . '</div>';
				}
				else {
					$result = '&nbsp;';
				}
				break;

			case 'sizebytes':
				if ($regular_file) {
					$result = '<div class="sz">' . number_format(filesize($filename)) . '</div>';
				}
				else {
					$result = '&nbsp;';
				}
				break;

			case 'perm':
				$result = $perms_str . ' (' . substr(sprintf('%o', $perms), -4) . ')';
				break;

			case 'permstr':
				$result = $perms_str;
				break;

			case 'permnum':
				$result = substr(sprintf('%o', $perms), -4);
				break;

			case 'ctime':
				$result = $this->list_files_attr_date(filectime($filename));
				break;

			case 'ctimets':
				$result = filectime($filename);
				break;

			case 'mtime':
				$result = $this->list_files_attr_date(filemtime($filename));
				break;

			case 'mtimets':
				$result = filemtime($filename);
				break;

			case 'atime':
				$result = $this->list_files_attr_date(fileatime($filename));
				break;

			case 'atimets':
				$result = fileatime($filename);
				break;

			case 'owner':
				$result = @fileowner($filename);
				if ($result === false) {
					$result = '?';
				}
				else if (function_exists('posix_getpwuid')) {
					$data = posix_getpwuid($result);
					$result = htmlspecialchars($data['name']);
				}
				else {
					$result = '&lt;N/A&gt; (' . $result . ')';
				}
				break;

			case 'ownernum':
				$result = @fileowner($filename);
				if ($result === false) {
					$result = '?';
				}
				break;

			case 'group':
				$result = @filegroup($filename);
				if ($result === false) {
					$result = '?';
				}
				else if (function_exists('posix_getgrgid')) {
					$data = posix_getgrgid($result);
					$result = htmlspecialchars($data['name']);
				}
				else {
					$result = '&lt;N/A&gt; (' . $result . ')';
				}
				break;

			case 'groupnum':
				$result = @filegroup($filename);
				if ($result === false) {
					$result = '?';
				}
				break;

			default:
				$result = '?';
		}
		return $result;
	}

	public function list_files() {
		$this->show_breadcrumbs_header();

		$this->title = '[D] ' . $this->path;

		if ($dp = @opendir($this->path)) {
			$path = $this->path;
			if (substr($path, -1) != '/') {
				$path .= '/';
			}

			$dirs = array();
			$files = array();
			$other = array();
			while (false !== ($file = readdir($dp))) {
				if ($file != '.' && $file != '..') {
					$filename = $path . $file;
					if (is_dir($filename)) {
						$dirs[] = $file;
					}
					else if (is_file($filename)) {
						$files[] = $file;
					}
					else {
						$other[] = $file;
					}
				}
			}
			closedir($dp);

			sort($dirs);
			sort($files);
			sort($other);

			if (empty($dirs) && empty($files) && empty($other)) {
				$this->body .=
'<div class="notice">Directory is empty</div>
';
			}
			else {
				$columns = explode(',', $this->columns);

				$column_names = array(
					'size' => 'Size',
					'sizehuman' => 'Size',
					'sizebytes' => 'Size',
					'perm' => 'Permissions',
					'permstr' => 'Permissions',
					'permnum' => 'Permissions',
					'ctime' => 'Created',
					'ctimets' => 'Created',
					'mtime' => 'Modified',
					'mtimets' => 'Modified',
					'atime' => 'Accessed',
					'atimets' => 'Accessed',
					'owner' => 'Owner',
					'ownernum' => 'Owner',
					'group' => 'Group',
					'groupnum' => 'Group',
				);

				// check if all columns are known
				foreach ($columns as $column_type) {
					$column_type = trim($column_type);
					if ($column_type != '' && !isset($column_names[$column_type])) {
						$this->body .=
'<div class="notice">Warning: Columns configuration <br/>contains unknown values</div>
';
						break;
					}
				}

				$this->body .=
'<table align="center" class="list">
	<tr>
		<th>&nbsp;</th>
		<th>File name</th>
';

				$last_column_name = '';
				foreach ($columns as $column_type) {
					$column_type = trim($column_type);
					if ($column_type == '') {
						continue;
					}

					$column_name = @$column_names[$column_type];
					if ($column_name === null) {
						continue;
					}

					if ($last_column_name == $column_name) {
						$column_name = '&nbsp;';
					}
					else {
						$last_column_name = $column_name;
					}

					$this->body .=
'		<th>' . $column_name . '</th>
';
				}


				$this->body .=
'	</tr>
';

				$link_fmt = '<a href="' . basename(__FILE__) . '?path=%s">%s</a>';

				if ($this->date_format_same_day == '') {
					$this->date_format_same_day = 'Y-m-d H:i:s';
				}

				if ($this->date_format_same_year == '') {
					$this->date_format_same_year = 'Y-m-d H:i:s';
				}

				if ($this->date_format_global == '') {
					$this->date_format_global = 'Y-m-d H:i:s';
				}

				foreach ($dirs as $file) {
					$filename = $path . $file;
					$this->body .=
'	<tr class="dir">
		<td>[Dir]</td>
		<td>' . sprintf($link_fmt, urlencode($filename), htmlspecialchars($file)) . '</td>
';

					foreach ($columns as $column_type) {
						$this->body .=
'		<td>' . $this->list_files_attr($filename, $column_type, false) . '</td>
';
					}

					$this->body .=
'	</tr>
';
				}

				foreach ($files as $file) {
					$filename = $path . $file;
					$this->body .=
'	<tr class="file">
		<td>[File]</td>
		<td>' . sprintf($link_fmt, urlencode($filename), htmlspecialchars($file)) . '</td>
';

					foreach ($columns as $column_type) {
						$this->body .=
'		<td>' . $this->list_files_attr($filename, $column_type, true) . '</td>
';
					}


					$this->body .=
'	</tr>
';
				}

				foreach ($other as $file) {
					$filename = $path . $file;
					$this->body .=
'	<tr class="special">
		<td>[Special]</td>
		<td>' . htmlspecialchars($file) . '</td>
';

					foreach ($columns as $column_type) {
						$this->body .=
'		<td>' . $this->list_files_attr($filename, $column_type, false) . '</td>
';
					}


					$this->body .=
'	</tr>
';
				}

				$this->body .=
'</table>
';			
			}
		}
		else {
			$this->body .=
'<div class="error">Unable to open directory</div>
';
		}
	}

	public function run() {
		clearstatcache();

		if (!empty($this->date_time_zone)) {
			date_default_timezone_set($this->date_time_zone);
		}

		$this->path = (string) @$_GET['path'];
		if ($this->path == '') {
			$this->path = dirname(__FILE__);
		}
		$this->path = str_replace('\\', '/', $this->path);

		if (strpos($this->path, "\0") !== false || strpos($this->path, '../') !== false || strpos($this->path, '://') !== false) {
			$this->body .=
'<div class="error">Path &quot;' . htmlspecialchars($this->path) . '&quot; in invalid</div>
';
		}
		else if (!file_exists($this->path)) {
			$this->body .=
'<div class="error">Path &quot;' . htmlspecialchars($this->path) . '&quot; does not exist</div>
';
		}
		else if (is_dir($this->path)) {
			$this->list_files();
		}
		else if (is_file($this->path)) {
			$this->show_file();
		}
		else {
			$this->body .=
'<div class="error">Path &quot;' . htmlspecialchars($this->path) . '&quot; does not point to a directory or a regular file</div>
';
		}

		$this->title = $this->title == '' ? 'File Manager' : htmlspecialchars($this->title) . ' | File Manager';

		echo
'<!DOCTYPE html>

<html>
<head>
<title>' . $this->title . '</title>
<meta charset="utf-8">

<style type="text/css">
body {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 13px;
	margin: 0;
}
a, a:visited, a:link {
	color: #00f;
	text-decoration: none;
}
a:hover {
	text-decoration: underline;
}
.notice, .error {
	background-color: #cefbf6;
	width: 300px;
	margin: 50px auto 0 auto;
	text-align: center;
	padding: 10px 0;
}
.error {
	background-color: #fcc;
}
.list th {
	text-align: left;
	font-size: 0.9em;
	padding: 20px 10px 5px 10px;
}
.list {
	border-collapse: collapse;
}
.list td {
	border-top: 3px solid #fff;
	padding: 2px 10px;
	font-family: monospace, "Courier New", Courier;
}
.list tr.dir:nth-child(even) {
	background-color: #fcfadc;
}
.list tr.dir:nth-child(odd) {
	background-color: #f6f4d5;
}
.list tr.file:nth-child(even) {
	background-color: #f9f9f9;
}
.list tr.file:nth-child(odd) {
	background-color: #f0f0f0;
}
.list tr.special:nth-child(even) {
	background-color: #f7f2fe;
}
.list tr.special:nth-child(odd) {
	background-color: #f2ebfc;
}
.list tr.dir:hover,
.list tr.file:hover,
.list tr.special:hover {
	background-color: #fff;
}
.sz {
	text-align: right;
}
.show_image {
	text-align: center;
	margin-top: 40px;
}
.text_editor {
	text-align: center;
}
.text_editor input[type=submit] {
	cursor: pointer;
}
.text_editor textarea {
	display: block;
	width: 98%;
	margin: 20px auto 20px auto;
}
.breadcrumbs_header {
	background-color: #eee;
	text-align: center;
	padding: 3px 0;
}
.footer {
	background-color: #eee;
	text-align: center;
	padding: 3px 0;
	margin-top: 50px;
}
</style>

</head>

<body>
' . $this->body . '

	<div class="footer">Mini Utils - <a href="http://www.creativepulse.gr/en/appstore/miniutils" target="_blank">File Manager</a> v' . $this->version . ' &mdash; &copy;2014 Creative Pulse</div>
</body>

</html>
';
	}

}

$proc = new CpMiniUtils_FileManager();

try {
	$proc->run();
}
catch (Exception $e) {
	echo 'Error: ' . htmlspecialchars($e->getMessage());
}

?>