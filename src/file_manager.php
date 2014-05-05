<?php

/**
 * File Manager - Mini Utils
 *
 * @version 1.4
 * @author Creative Pulse
 * @copyright Creative Pulse 2014
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


class CpMiniUtils_FileManager {

	// -------------------------- //
	//   configurable variables   //
	// -------------------------- //

	// available options: size,sizehuman,sizebytes,ctime,ctimets,mtime,mtimets,atime,atimets,owner,ownernum,group,groupnum,perm,permstr,permnum
	// default: sizehuman,mtime,perm
	public $columns = 'sizehuman,mtime,perm';

	// default: txt,ini,md,markdown,js,css,less,sass,scss,php,php3,htm,html,xml,atom,rss,xsl,dtd,h,c,cpp,c++,m,as,py,rb,pl,tcl,pas,svg,vb,asp,aspx,cgi,bat,htaccess,log,conf,cfg
	public $text_file_extensions = 'txt,ini,md,markdown,js,css,less,sass,scss,php,php3,htm,html,xml,atom,rss,xsl,dtd,h,c,cpp,c++,m,as,py,rb,pl,tcl,pas,svg,vb,asp,aspx,cgi,bat,htaccess,log,conf,cfg';

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

	public $version = '1.4';
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
			return '[' . tt('No path specified') . ']';
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
			$html .= ' &nbsp; [ ' . sprintf($link_fmt, urlencode($previous_link), tt('Up')) . ' ]';
		}

		$this->body .=
'<div class="breadcrumbs_header">' . tt('Path %s', $html) . '</div>
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
'<div class="error">' . tt('Cannot show image from a different drive') . '</div>
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
'<div class="show_image"><img src="' . $relative_path . basename($this->path) . '" alt="' . tt('Image out of reach') . '"></div>
';
			}
		}
		else if (strpos(',' . $this->text_file_extensions . ',', $ext) !== false) {
			// show text file

			if (!is_readable($this->path) && !is_writable($this->path)) {
				$this->body .=
'<div class="error">' . tt('File neither readable or writable') . '</div>
';
			}
			else if (!is_readable($this->path)) {
				$this->body .=
'<div class="error">' . tt('File is not readable') . '</div>
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
'<div class="notice">' . tt('File loaded') . '</div>
';
					}
					else {
						$content = $_POST['content'];
						file_put_contents($this->path, $content);
						$this->body .=
'<div class="notice">' . tt('File saved') . '</div>
';
					}
				}

				$this->body .=
'<div class="text_editor">
	<form name="frm" method="post" action="">

		<textarea name="content" rows="30">' . htmlspecialchars($content) . '</textarea>

		<input type="submit" name="load" value="' . tt('Load') . '">
		<input type="submit" name="save" value="' . tt('Save') . '"' . (is_writable($this->path) ? '' : ' disabled') . '>

	</form>
</div>
';
			}
		}
		else {
			// unhandled file
			$this->body .=
'<div class="notice">' . tt('Unable to handle &quot;%s&quot; files', htmlspecialchars(substr($ext, 1, -1))) . '</div>
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

	private function list_files_attr($filename, $column_type, $is_dir) {
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

		if (!$is_dir && ($column_type == 'size' || $column_type == 'sizehuman' || $column_type == 'sizebytes')) {
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
				$result = $is_dir ? '&nbsp;' : $filesize_str . ' [' . number_format(filesize($filename)) . ']';
				break;

			case 'sizehuman':
				$result = $is_dir ? '&nbsp;' : $filesize_str;
				break;

			case 'sizebytes':
				$result = $is_dir ? '&nbsp;' : number_format(filesize($filename));
				break;

			case 'sizeplain':
				$result = $is_dir ? '' : filesize($filename);
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
					$result = '&lt;' . tt('N/A') . '&gt; (' . $result . ')';
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
					$result = '&lt;' . tt('N/A') . '&gt; (' . $result . ')';
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

	public function list_files_get_composite_message($dirs_count, $files_count, $msg_single, $msg_composite) {
		$dirs_msg = '';
		if ($dirs_count == 1) {
			$dirs_msg = tt('1 directory');
		}
		else if ($dirs_count > 1) {
			$dirs_msg = tt('%d directories', $dirs_count);
		}

		$files_msg = '';
		if ($files_count == 1) {
			$files_msg = tt('1 file');
		}
		else if ($files_count > 1) {
			$files_msg = tt('%d files', $files_count);
		}

		if ($dirs_msg != '' && $files_msg != '') {
			return tt($msg_composite, $dirs_msg, $files_msg);
		}
		else {
			return tt($msg_single, $dirs_msg != '' ? $dirs_msg : $files_msg);
		}
	}

	public function list_files_delete_execute($dir, &$affected_files_count, &$affected_directories_count) {
		if ($dp = @opendir($dir)) {
			while (false !== ($file = readdir($dp))) {
				if ($file != '.' && $file != '..') {
					$filename = $dir . '/' . $file;
					if (is_dir($filename)) {
						$error = $this->list_files_delete_execute($filename, $affected_files_count, $affected_directories_count);
						if ($error != '') {
							return $error;
						}
					}
					else {
						if (@unlink($filename)) {
							$affected_files_count++;
						}
						else {
							return tt('Unable to delete file <br/>%s', htmlspecialchars($filename));
						}
					}
				}
			}
			closedir($dp);

			if (@rmdir($dir)) {
				$affected_directories_count++;
			}
			else {
				return tt('Unable to delete directory <br/>%s', htmlspecialchars($dir));
			}

			return '';
		}
		else {
			return tt('Unable to open directory <br/>%s', htmlspecialchars($dir));
		}
	}

	public function list_files_delete($return_url) {
		if (empty($_POST['cb']) && empty($_POST['files'])) {
			$this->body .=
'<div class="error">
	<p>' . tt('No files selected') . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', htmlspecialchars($return_url)) . '</p>
</div>
';
			return;
		}

		$dirs_count = 0;
		$files_count = 0;
		$files = !empty($_POST['cb']) ? $_POST['cb'] : explode('/', $_POST['files']);
		foreach ($files as $file) {
			$filename = $this->path . '/' . $file;
			if (file_exists($filename)) {
				if (is_dir($filename)) {
					$dirs_count++;
				}
				else {
					$files_count++;
				}
			}
		}

		if ($dirs_count == 0 && $files_count == 0) {
			$this->body .=
'<div class="error">
	<p>' . tt('No files found') . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', htmlspecialchars($return_url)) . '</p>
</div>
';
			return;
		}

		if (empty($_POST['files'])) {
			// show confirmation dialog

			$this->body .=
'<form name="frm" method="post" action="">
	<input type="hidden" name="files" value="' . htmlspecialchars(implode('/', $files)) . '">
	<div class="notice">
		<p>' . $this->list_files_get_composite_message($dirs_count, $files_count, 'Delete %s ?', 'Delete %s and %s ?') . '</p>
		<p><input type="button" name="btn_cancel" value="' . tt('Cancel') . '" onclick="window.location=\'' . htmlspecialchars($return_url) . '\'">
			&nbsp; &nbsp; <input type="submit" name="btn_delete" value="' . tt('Delete') . '"></p>
	</div>
</form>
';
		}
		else {
			// delete directories and files

			$affected_files_count = 0;
			$affected_directories_count = 0;
			$error = '';
			foreach ($files as $file) {
				$filename = $this->path . '/' . $file;
				if (file_exists($filename)) {
					if (is_dir($filename)) {
						$error = $this->list_files_delete_execute($filename, $affected_files_count, $affected_directories_count);
						if ($error != '') {
							break;
						}
					}
					else {
						if (@unlink($filename)) {
							$affected_files_count++;
						}
						else {
							$error = tt('Unable to delete file <br/>%s', htmlspecialchars($filename));
							break;
						}
					}
				}
			}

			if ($error == '') {
				$this->body .=
'<div class="notice">
	<p>' . $this->list_files_get_composite_message($affected_directories_count, $affected_files_count, 'Deleted %s', 'Deleted %s and %s') . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', $return_url) . '</p>
</div>
';
			}
			else {
				$this->body .=
'<div class="error">
	<p>' . $error . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', htmlspecialchars($return_url)) . '</p>
</div>
';
			}
		}
	}

	public function list_files_permissions($return_url) {
		if (empty($_POST['cb']) && empty($_POST['files'])) {
			$this->body .=
'<div class="error">
	<p>' . tt('No files selected') . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', htmlspecialchars($return_url)) . '</p>
</div>
';
			return;
		}

		$dirs_count = 0;
		$files_count = 0;
		$files = !empty($_POST['cb']) ? $_POST['cb'] : explode('/', $_POST['files']);
		foreach ($files as $file) {
			$filename = $this->path . '/' . $file;
			if (file_exists($filename)) {
				if (is_dir($filename)) {
					$dirs_count++;
				}
				else {
					$files_count++;
				}
			}
		}

		if ($dirs_count == 0 && $files_count == 0) {
			$this->body .=
'<div class="error">
	<p>' . tt('No files found') . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', htmlspecialchars($return_url)) . '</p>
</div>
';
			return;
		}

		// validate new permissions
		$form_error = '';
		if (!empty($_POST['new_permissions']) && !preg_match('/^[0-7]{3}$/', $_POST['new_permissions'])) {
			$form_error = tt('Error: Invalid permissions');
		}

		if (empty($_POST['files']) || $form_error != '') {
			// show input dialog

			$this->body .=
'<form name="frm" method="post" action="">
	<input type="hidden" name="files" value="' . htmlspecialchars(implode('/', $files)) . '">
	<div class="notice">
		<p>' . $this->list_files_get_composite_message($dirs_count, $files_count, 'Set new permissions for %s', 'Set new permissions for %s and %s') . '</p>
		' . ($form_error == '' ? '' : '<p style="color:red">' . $form_error . '</p>') . '
		<p>' . tt('New permissions') . ' <input type="text" name="new_permissions" value="' . htmlspecialchars(@$_POST['new_permissions']) . '" size="5"> (' . tt('Example: 755') . ')</p>
		<p><input type="button" name="btn_cancel" value="' . tt('Cancel') . '" onclick="window.location=\'' . htmlspecialchars($return_url) . '\'">
			&nbsp; &nbsp; <input type="submit" name="btn_permissions" value="' . tt('Set new permissions') . '"></p>
	</div>
</form>
';
		}
		else {
			// set new permissions for directories and files

			$new_permissions = octdec($_POST['new_permissions']);
			$affected_files_count = 0;
			$affected_directories_count = 0;
			$error = '';
			foreach ($files as $file) {
				$filename = $this->path . '/' . $file;
				if (file_exists($filename)) {
					if (is_dir($filename)) {
						if (@chmod($filename, $new_permissions)) {
							$affected_directories_count++;
						}
						else {
							$error = tt('Unable to set permissions for the directory <br/>%s', htmlspecialchars($filename));
							break;
						}
					}
					else {
						if (@chmod($filename, $new_permissions)) {
							$affected_files_count++;
						}
						else {
							$error = tt('Unable to set permissions for the file <br/>%s', htmlspecialchars($filename));
							break;
						}
					}
				}
			}

			if ($error == '') {
				$this->body .=
'<div class="notice">
	<p>' . $this->list_files_get_composite_message($affected_directories_count, $affected_files_count, 'Set new permissions for %s', 'Set new permissions for %s and %s') . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', $return_url) . '</p>
</div>
';
			}
			else {
				$this->body .=
'<div class="error">
	<p>' . $error . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', htmlspecialchars($return_url)) . '</p>
</div>
';
			}
		}
	}

	public function list_files_action() {
		$this->show_breadcrumbs_header();

		$return_url = '';
		foreach ($_GET as $k => $v) {
			if ($k != 'action') {
				$return_url .= ($return_url == '' ? '?' : '&') . urlencode($k) . '=' . urlencode($v);
			}
		}
		$return_url = basename(__FILE__) . $return_url;

		if (isset($_POST['btn_delete']) || isset($_POST['hfrm_delete'])) {
			$this->list_files_delete($return_url);
		}
		else if (isset($_POST['btn_permissions']) || isset($_POST['hfrm_set_permissions'])) {
			$this->list_files_permissions($return_url);
		}
		else {
			$this->body .=
'<div class="error">
	<p>' . tt('Direct access not allowed') . '</p>
	<p>' . tt('Go back to the <a href="%s">file list</a>', $return_url) . '</p>
</div>
';
		}
	}

	private function list_files_cmp($a, $b) {
		$order = $this->current_order == 'desc' ? -1 : 1;

		$a2 = false;
		$b2 = false;

		if ($this->current_sort == 'size' || $this->current_sort == 'sizehuman' || $this->current_sort == 'sizebytes') {
			$a2 = intval($a['sizeplain']);
			$b2 = intval($b['sizeplain']);
		}
		else if ($this->current_sort == 'ctime' || $this->current_sort == 'ctimets') {
			$a2 = intval($a['ctimets']);
			$b2 = intval($b['ctimets']);
		}
		else if ($this->current_sort == 'mtime' || $this->current_sort == 'mtimets') {
			$a2 = intval($a['mtimets']);
			$b2 = intval($b['mtimets']);
		}
		else if ($this->current_sort == 'atime' || $this->current_sort == 'atimets') {
			$a2 = intval($a['atimets']);
			$b2 = intval($b['atimets']);
		}
		else if ($this->current_sort == 'ownernum' || $this->current_sort == 'groupnum') {
			$a2 = intval($a[$this->current_sort]);
			$b2 = intval($b[$this->current_sort]);
		}
		else if ($this->current_sort == 'owner' || $this->current_sort == 'group' || $this->current_sort == 'perm' || $this->current_sort == 'permstr' || $this->current_sort == 'permnum') {
			$a2 = $a[$this->current_sort];
			$b2 = $b[$this->current_sort];
		}

		if ($a2 !== false) {
			if ($a2 < $b2) {
				return $order * -1;
			}
			else if ($a2 > $b2) {
				return $order;
			}
		}

		$a2 = mb_strtolower($a['filename']);
		$b2 = mb_strtolower($b['filename']);

		if ($a2[0] == '.' && $b2[0] != '.') {
			return $order * -1;
		}
		else if ($a2[0] != '.' && $b2[0] == '.') {
			return $order;
		}
		else if ($a2 < $b2) {
			return $order * -1;
		}
		else if ($a2 > $b2) {
			return $order;
		}
		else {
			return 0;
		}
	}

	public function list_files() {
		$this->show_breadcrumbs_header();

		$this->title = '[D] ' . $this->path;

		$path = $this->path;
		if (substr($path, -1) != '/') {
			$path .= '/';
		}

		// collect fields
		$allowed_columns = array('size', 'sizehuman', 'sizebytes', 'perm', 'permstr', 'permnum', 'ctime', 'ctimets', 'mtime', 'mtimets', 'atime', 'atimets', 'owner', 'ownernum', 'group', 'groupnum');
		$show_columns = array('type', 'filename');
		$load_columns = $show_columns;
		foreach (explode(',', $this->columns) as $column) {
			$column = trim($column);
			if ($column == '') {
				continue;
			}

			if (!in_array($column, $allowed_columns)) {
				$this->body .=
'<div class="notice">' . tt('Warning: Columns configuration <br/>contains unknown values') . '</div>
';
				continue;
			}

			if (!in_array($column, $show_columns)) {
				$show_columns[] = $column;
				$load_columns[] = $column;
			}

			if (($column == 'size' || $column == 'sizehuman' || $column == 'sizebytes') && !in_array('sizeplain', $load_columns)) {
				$load_columns[] = 'sizeplain';
			}

			if ($column == 'ctime' && !in_array('ctimets', $load_columns)) {
				$load_columns[] = 'ctimets';
			}

			if ($column == 'mtime' && !in_array('mtimets', $load_columns)) {
				$load_columns[] = 'mtimets';
			}

			if ($column == 'atime' && !in_array('atimets', $load_columns)) {
				$load_columns[] = 'atimets';
			}
		}

		// handle upload
		if (!empty($_FILES['file'])) {
			$error = '';
			$upload_tmp_name = (string) @$_FILES['file']['tmp_name'];
			$upload_name = (string) @$_FILES['file']['name'];
			$upload_error = intval(@$_FILES['file']['error']);

			if ($upload_tmp_name == '' || !file_exists($upload_tmp_name) || !is_uploaded_file($upload_tmp_name)) {
				$error = tt('Unsafe file upload attempt');
				$upload_tmp_name = '';
			}
			else if ($upload_name == '' || strpos($upload_name, "\0") !== false || strpos($upload_name, '/') !== false || strpos($upload_name, '\\') !== false) {
				$error = tt('Unsafe file upload attempt');
			}
			else if (!file_exists($path) || !is_dir($path)) {
				$error = tt('Current directory does not exist anymore');
			}
			else if (!is_writable($path)) {
				$error = tt('Current directory is not writable');
			}
			else if ($upload_error != 0) {
				switch ($upload_error) {
					case 1: // UPLOAD_ERR_INI_SIZE
						$error = tt('Attempt to upload file larger than system limits');
						break;

					case 2: // UPLOAD_ERR_FORM_SIZE
						$error = tt('Attempt to upload file larger than website limits');
						break;

					case 3: // UPLOAD_ERR_PARTIAL
						$error = tt('Upload process was interrupted');
						break;

					case 4: // UPLOAD_ERR_NO_FILE
						$error = tt('No file was uploaded');
						break;

					case 6: // UPLOAD_ERR_NO_TMP_DIR
						$error = tt('Temporary directory is missing');
						break;

					case 7: // UPLOAD_ERR_CANT_WRITE
						$error = tt('Cannot write temporary upload file');
						break;

					case 8: // UPLOAD_ERR_EXTENSION
						$error = tt('An extension does not allow this upload');
						break;

					default:
						$error = tt('Unhandled UploadHelper error [%d]', $upload_error);
				}
			}
			else if (!move_uploaded_file($upload_tmp_name, $path . $upload_name)) {
				$error = tt('Unable to move the uploaded file to current directory. Perhaps there is not enough space?');
			}

			if ($error == '') {
				$this->body .=
'<div class="notice">' . tt('File &quot;%s&quot; uploaded successfully', htmlspecialchars($upload_name)) . '</div>
';
			}
			else {
				$this->body .=
'<div class="error">' . tt('Error on file upload') . ': <br/>' . $error . '</div>
';

				if (file_exists($upload_tmp_name)) {
					@unlink($upload_tmp_name);
				}
			}
		}

		// list files
		if ($dp = @opendir($this->path)) {
			$all_files = array();
			while (false !== ($file = readdir($dp))) {
				if ($file != '.' && $file != '..') {
					$data = array('filename' => $path . $file);

					if (is_dir($data['filename'])) {
						$data['type'] = 'dir';
					}
					else if (is_file($data['filename'])) {
						$data['type'] = 'file';
					}
					else {
						$data['type'] = 'other';
					}

					$all_files[] = $data;
				}
			}
			closedir($dp);

			if (empty($all_files)) {
				$this->body .=
'<div class="notice">' . tt('Directory is empty') . '</div>

<table align="center"><tr><td>
	<form name="frm_upload" action="" method="post" enctype="multipart/form-data">
		' . tt('Upload file') . ': <input type="file" name="file"' . (is_writable($this->path) ? '' : ' disabled') . '>
		<input type="submit" name="btn_upload" value="' . tt('Upload') . '"' . (is_writable($this->path) ? '' : ' disabled') . '>
	</form>
</td></tr></table>
';
			}
			else {
				// populate data
				foreach ($all_files as $k_file => $file) {
					foreach ($load_columns as $column) {
						if ($column == 'type' || $column == 'filename') {
							continue;
						}

						$all_files[$k_file][$column] = $this->list_files_attr($file['filename'], $column, $file['type'] == 'dir');
					}
				}

				$this->current_sort = (string) @$_GET['sort'];
				if ($this->current_sort == '' || $this->current_sort == 'type' || !in_array($this->current_sort, $show_columns)) {
					$this->current_sort = 'filename';
				}

				$this->current_order = (string) @$_GET['order'];
				if ($this->current_order != 'asc' && $this->current_order != 'desc') {
					$this->current_order = 'asc';
				}

				usort($all_files, array(get_class($this), 'list_files_cmp'));

				$script_filename = basename(__FILE__);

				$link_fmt = '<a href="' . str_replace('%', '%%', $script_filename) . '?path=%s&sort=%s&order=%s">%s</a>';

				$action_url = $script_filename . '?';
				foreach ($_GET as $k => $v) {
					$action_url .= urlencode($k) . '=' . urlencode($v) . '&';
				}
				$action_url .= 'action=ctrl';

				// show table header
				$this->body .=
'<table align="center"><tr><td>

	<form name="frm_list" method="post" action="' . $action_url . '">
		<table class="list">
			<tr>
';

				$last_column_name = '';
				foreach ($show_columns as $column) {
					if ($column == 'type') {
						$column_name = '<input type="checkbox" id="cb_all">';
					}
					else {
						$column_name = tt($column);

						if ($last_column_name == $column_name) {
							$column_name = '&nbsp;';
						}
						else {
							$last_column_name = $column_name;
						}

						if ($column_name != '&nbsp;') {
							$order = 'asc';
							$order_symbol = '';
							if ($this->current_sort == $column) {
								if ($this->current_order == 'asc') {
									$order_symbol = ' &darr;';
									$order = 'desc';
								}
								else {
									$order_symbol = ' &uarr;';
									$order = 'asc';
								}
							}

							$column_name = sprintf($link_fmt, urlencode($this->path), $column, $order, $column_name . $order_symbol);
						}
					}

					$this->body .=
'				<th>' . $column_name . '</th>
';
				}

				$this->body .=
'			</tr>
';

				// show file data
				$idx = -1;
				foreach (array('dir', 'file', 'special') as $file_type) {
					foreach ($all_files as $file) {
						if ($file['type'] == $file_type) {
							$idx++;
							$this->body .=
'			<tr class="' . $file['type'] . '">
				<td><input type="checkbox" name="cb[]" value="' . htmlspecialchars(basename($file['filename'])) . '" id="cb_' . $idx . '"> <label for="cb_' . $idx . '">[' . tt($file['type']) . ']</label></td>
				<td>' . sprintf($link_fmt, urlencode($file['filename']), $this->current_sort, $this->current_order, htmlspecialchars(basename($file['filename']))) . '</td>
';

							foreach ($show_columns as $column) {
								if ($column != 'type' && $column != 'filename') {
									$class = $column == 'sizehuman' || $column == 'size' || $column == 'sizebytes' ? ' class="sz"' : '';
									$this->body .=
'				<td' . $class . '>' . $file[$column] . '</td>
';
								}
							}

							$this->body .=
'			</tr>
';
						}
					}
				}

				$this->body .=
'		</table>

		<div class="ctrl">
			' . tt('Selection actions') . ':
			<input type="submit" name="btn_delete" id="btn_delete" value="' . tt('Delete') . '" disabled>
			<input type="submit" name="btn_permissions" id="btn_permissions" value="' . tt('Set permissions') . '" disabled>
		</div>
	</form>

	<form name="frm_upload" action="" method="post" enctype="multipart/form-data">
		<div class="ctrl">
			' . tt('Upload file') . ': <input type="file" name="file"' . (is_writable($this->path) ? '' : ' disabled') . '>
			<input type="submit" name="btn_upload" value="' . tt('Upload') . '"' . (is_writable($this->path) ? '' : ' disabled') . '>
		</div>
	</form>

</td></tr></table>

<script type="text/javascript">
(function (document) {
	document.cbs = [];
	document.cbs_autoset = false;
	var elements = document.getElementsByTagName("input");
	for (var i = 0, len = elements.length; i < len; i++) {
		var element = elements[i];
		if (element.id && element.id.substr(0, 3) == "cb_") {
			if (element.id == "cb_all") {
				element.onchange = function () {
					if (!document.cbs_autoset) {
						document.cbs_autoset = true;
						for (var i = 0, len = document.cbs.length; i < len; i++) {
							document.cbs[i].checked = this.checked;
						}
						document.getElementById("btn_delete").disabled = !this.checked;
						document.getElementById("btn_permissions").disabled = !this.checked;
						document.cbs_autoset = false;
					}
				}
			}
			else {
				document.cbs.push(element);
				element.onchange = function () {
					if (!document.cbs_autoset) {
						var checked_count = 0, unchecked_count = 0;
						for (var i = 0, len = document.cbs.length; i < len; i++) {
							if (document.cbs[i].checked) {
								checked_count++;
							}
							else {
								unchecked_count++;
							}
						}

						document.cbs_autoset = true;
						document.getElementById("cb_all").checked = checked_count > 0 && unchecked_count == 0;
						document.getElementById("btn_delete").disabled = checked_count == 0;
						document.getElementById("btn_permissions").disabled = checked_count == 0;
						document.cbs_autoset = false;
					}
				}
			}
		}
	}
})(document);
</script>
';
			}
		}
		else {
			$this->body .=
'<div class="error">' . tt('Unable to open directory') . '</div>
';
		}
	}

	public function run() {
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

		clearstatcache();

		set_time_limit(0);

		if (!empty($this->date_time_zone)) {
			date_default_timezone_set($this->date_time_zone);
		}


		$this->path = (string) @$_GET['path'];
		if ($this->path == '') {
			$this->path = dirname(__FILE__);
		}
		$this->path = str_replace('\\', '/', $this->path);


		if ($this->date_format_same_day == '') {
			$this->date_format_same_day = 'Y-m-d H:i:s';
		}

		if ($this->date_format_same_year == '') {
			$this->date_format_same_year = 'Y-m-d H:i:s';
		}

		if ($this->date_format_global == '') {
			$this->date_format_global = 'Y-m-d H:i:s';
		}


		if (strpos($this->path, "\0") !== false || strpos($this->path, '../') !== false || strpos($this->path, '://') !== false) {
			$this->body .=
'<div class="error">' . tt('Path &quot;%s&quot; in invalid', htmlspecialchars($this->path)) . '</div>
';
		}
		else if (!file_exists($this->path)) {
			$this->body .=
'<div class="error">' . tt('Path &quot;%s&quot; does not exist', htmlspecialchars($this->path)) . '</div>
';
		}
		else if (is_dir($this->path)) {
			if (@$_GET['action'] == 'ctrl') {
				$this->list_files_action();
			}
			else {
				$this->list_files();
			}
		}
		else if (is_file($this->path)) {
			$this->show_file();
		}
		else {
			$this->body .=
'<div class="error">' . tt('Path &quot;%s&quot; does not point to a directory or a regular file', htmlspecialchars($this->path)) . '</div>
';
		}

		$this->title = $this->title == '' ? tt('File Manager') : sprintf('%s | File Manager', htmlspecialchars($this->title));

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
	background-color: #e2efc0;
	width: 400px;
	margin: 50px auto 50px auto;
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
.list th a, .list th a:link, .list th a:visited {
	color: #000;
}
.list {
	border-collapse: collapse;
}
.list td {
	border-top: 3px solid #fff;
	padding: 2px 10px;
	font-family: monospace, "Courier New", Courier;
}
.list tr {
	transition: background-color 0.3s;
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
	transition: background-color 0s;
}
.ctrl {
	padding: 25px 0 0 0;
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


if (!function_exists('mb_strtolower')) {
	function mb_strtolower($str) {
		return strtolower($str);
	}
}


function tt() {

	$messages = array(

		// main
		'File Manager' => 'File Manager', // plain title
		'%s | File Manager' => '%s | File Manager', // title with path
		'Path &quot;%s&quot; in invalid' => 'Path &quot;%s&quot; in invalid',
		'Path &quot;%s&quot; does not exist' => 'Path &quot;%s&quot; does not exist',
		'Path &quot;%s&quot; does not point to a directory or a regular file' => 'Path &quot;%s&quot; does not point to a directory or a regular file',

		// breadcrumbs
		'No path specified' => 'No path specified',
		'Up' => 'Up',
		'Path %s' => 'Path %s',

		// file show
		'Cannot show image from a different drive' => 'Cannot show image from a different drive',
		'Image out of reach' => 'Image out of reach',
		'File neither readable or writable' => 'File neither readable or writable',
		'File is not readable' => 'File is not readable',
		'File loaded' => 'File loaded',
		'File saved' => 'File saved',
		'Load' => 'Load',
		'Save' => 'Save',
		'Unable to handle &quot;%s&quot; files' => 'Unable to handle &quot;%s&quot; files',

		// file list
		'Unable to open directory' => 'Unable to open directory',
		'N/A' => 'N/A', // Not Available
		'Directory is empty' => 'Directory is empty',
		'Warning: Columns configuration <br/>contains unknown values' => 'Warning: Columns configuration <br/>contains unknown values',
		'dir' => 'Dir',
		'file' => 'File',
		'special' => 'Special',
		'Selection actions' => 'Selection actions',

		// files list action
		'Cancel' => 'Cancel',
		'Delete' => 'Delete',
		'Set permissions' => 'Set permissions',
		'Go back to the <a href="%s">file list</a>' => 'Go back to the <a href="%s">file list</a>',
		'No files selected' => 'No files selected',
		'Direct access not allowed' => 'Direct access not allowed',
		'No files found' => 'No files found',
		'1 directory' => '1 directory',
		'%d directories' => '%d directories',
		'1 file' => '1 file',
		'%d files' => '%d files',
		'Delete %s ?' => 'Delete %s ?',
		'Delete %s and %s ?' => 'Delete %s and %s ?',
		'Deleted %s' => 'Deleted %s',
		'Deleted %s and %s' => 'Deleted %s and %s',
		'Unable to delete file <br/>%s' => 'Unable to delete file <br/>%s',
		'Unable to delete directory <br/>%s' => 'Unable to delete directory <br/>%s',
		'Unable to open directory <br/>%s' => 'Unable to open directory <br/>%s',
		'Set new permissions for %s' => 'Set new permissions for %s',
		'Set new permissions for %s and %s' => 'Set new permissions for %s and %s',
		'New permissions' => 'New permissions',
		'Example: 755' => 'Example: 755',
		'Set new permissions' => 'Set new permissions',
		'Error: Invalid permissions' => 'Error: Invalid permissions',
		'Unable to set permissions for the directory <br/>%s' => 'Unable to set permissions for the directory <br/>%s',
		'Unable to set permissions for the file <br/>%s' => 'Unable to set permissions for the file <br/>%s',

		// file upload
		'Upload file' => 'Upload file',
		'Upload' => 'Upload',
		'Unsafe file upload attempt' => 'Unsafe file upload attempt',
		'Current directory does not exist anymore' => 'Current directory does not exist anymore',
		'Current directory is not writable' => 'Current directory is not writable',
		'Attempt to upload file larger than system limits' => 'Attempt to upload file larger than system limits',
		'Attempt to upload file larger than website limits' => 'Attempt to upload file larger than website limits',
		'Upload process was interrupted' => 'Upload process was interrupted',
		'No file was uploaded' => 'No file was uploaded',
		'Temporary directory is missing' => 'Temporary directory is missing',
		'Cannot write temporary upload file' => 'Cannot write temporary upload file',
		'An extension does not allow this upload' => 'An extension does not allow this upload',
		'Unhandled UploadHelper error [%d]' => 'Unhandled UploadHelper error [%d]',
		'Unable to move the uploaded file to current directory. Perhaps there is not enough space?' => 'Unable to move the uploaded file to current directory. Perhaps there is not enough space?',
		'File &quot;%s&quot; uploaded successfully' => 'File &quot;%s&quot; uploaded successfully',
		'Error on file upload' => 'Error on file upload',

		// file list - column names
		'filename' => 'File name',
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


	$args = func_get_args();
	$msg = array_shift($args);
	$msg = isset($messages[$msg]) ? $messages[$msg] : $msg;
	return empty($args) ? $msg : vsprintf($msg, $args);
}


$proc = new CpMiniUtils_FileManager();

try {
	$proc->run();
}
catch (Exception $e) {
	echo 'Error: ' . htmlspecialchars($e->getMessage());
}

?>