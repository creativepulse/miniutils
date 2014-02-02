<?php

/**
 * File Manager - Mini Utils
 *
 * @version 1.0
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

	// configurable variables

	public $text_file_extensions = 'txt,ini,md,markdown,js,css,less,sass,scss,php,php3,htm,html,xml,atom,rss,xsl,dtd,h,c,cpp,c++,m,as,py,rb,pl,tcl,pas,svg,vb,asp,aspx,cgi,bat';
	public $image_file_extensions = 'jpg,jpeg,png,gif,bmp';


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

	public function perms_to_string($filename) {
		$perms = fileperms($filename);

		if (($perms & 0xC000) == 0xC000) {
			$info = 's'; // Socket
		}
		else if (($perms & 0xA000) == 0xA000) {
			$info = 'l'; // Symbolic Link
		}
		else if (($perms & 0x8000) == 0x8000) {
			$info = '-'; // Regular
		}
		else if (($perms & 0x6000) == 0x6000) {
			$info = 'b'; // Block special
		}
		else if (($perms & 0x4000) == 0x4000) {
			$info = 'd'; // Directory
		}
		else if (($perms & 0x2000) == 0x2000) {
			$info = 'c'; // Character special
		}
		else if (($perms & 0x1000) == 0x1000) {
			$info = 'p'; // FIFO pipe
		}
		else {
			$info = 'u'; // Unknown
		}

		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

		$num_info = substr(sprintf('%o', $perms), -4);

		return $info . ' (' . $num_info . ')';
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
				$this->body .=
'<table align="center" class="list">
	<tr>
		<th>&nbsp;</th>
		<th>File name</th>
		<th>Size</th>
		<th>Permissions</th>
	</tr>
';

				$link_fmt = '<a href="' . basename(__FILE__) . '?path=%s">%s</a>';

				foreach ($dirs as $file) {
					$filename = $path . $file;
					$this->body .=
'	<tr class="dir">
		<td>[Dir]</td>
		<td>' . sprintf($link_fmt, urlencode($filename), htmlspecialchars($file)) . '</td>
		<td>&nbsp;</td>
		<td>' . $this->perms_to_string($filename) . '</td>
	</tr>
';
				}

				foreach ($files as $file) {
					$filename = $path . $file;
					$this->body .=
'	<tr class="file">
		<td>[File]</td>
		<td>' . sprintf($link_fmt, urlencode($filename), htmlspecialchars($file)) . '</td>
		<td style="text-align:right">' . number_format(filesize($filename)) . '</td>
		<td>' . $this->perms_to_string($filename) . '</td>
	</tr>
';
				}

				foreach ($other as $file) {
					$filename = $path . $file;
					$this->body .=
'	<tr class="special">
		<td>[Special]</td>
		<td>' . htmlspecialchars($file) . '</td>
		<td>&nbsp;</td>
		<td>' . $this->perms_to_string($filename) . '</td>
	</tr>
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
	padding: 20px 0 5px 0;
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