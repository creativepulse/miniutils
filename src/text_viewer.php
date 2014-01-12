<?php

/**
 * Text Viewer - Mini Utils
 *
 * @version 1.1
 * @author Creative Pulse
 * @copyright Creative Pulse 2013-2014
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


$file = trim(@$_GET['file']);
$file = str_replace('\\', '/', $file);

if ($file == '') {
	echo 'Use the URL variable &quot;file&quot; to set a filename';
	return;
}

if ($file[0] != '/') {
	$file = '/' . $file;
}

$file = dirname(__FILE__) . $file;

if (!file_exists($file)) {
	echo 'Requested file does not exist';
	return;
}

echo '<pre>' . htmlspecialchars(file_get_contents($file)) . '</pre>';

?>