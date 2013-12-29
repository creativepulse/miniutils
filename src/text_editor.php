<?php

/**
 * Text Editor - Mini Utils
 *
 * @version 1.0
 * @author Creative Pulse
 * @copyright Creative Pulse 2013
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


if (get_magic_quotes_gpc()) {
    $post = array();
    foreach ($_POST as $k => $v) {
        $post[$k] = stripslashes($v);
    }
    $_POST = $post;
    unset($post);
}

$file = trim(@$_POST['file']);
if ($file != '') {
    $file = str_replace('\\', '/', $file);
    if ($file[0] != '/') {
        $file = '/' . $file;
    }
}

$file_full = dirname(__FILE__) . $file;

$msg = '';
$content = '';

if (!empty($_POST)) {
    if ($file == '') {
        $msg = '<p class="error">Error: File name is not set</p>';
    }

    if ($msg == '' && !file_exists($file_full)) {
        $msg = '<p class="error">Error: File &mdash; ' . htmlspecialchars($file) . ' &mdash; does not exist</p>';
    }

    if ($msg == '') {
        if (isset($_POST['load'])) {
            $content = file_get_contents($file_full);
            $msg = '<p class="success">File &mdash; ' . htmlspecialchars($file) . ' &mdash; loaded</p>';
        }
        else {
            $content = $_POST['content'];
            file_put_contents($file_full, $content);
            $msg = '<p class="success">File &mdash; ' . htmlspecialchars($file) . ' &mdash; saved</p>';
        }
    }
}

echo
'<!DOCTYPE html>
<html>
<head>
<title>Text editor</title>

<meta charset="utf-8">

<style type="text/css">
body {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 14px;
}
input[type=text], textarea {
    width: 98%;
    padding: 5px;
}
.success {
    color: #070;
    font-weight: bold;
}
.error {
    color: red;
    font-weight: bold;
}
</style>

</head>

<body>
    ' . $msg . '
    <form name="frm" method="post" action="">

        <p>File</p>
        <input type="text" name="file" value="' . htmlspecialchars($file) . '"></p>

        <p>Content</p>
        <textarea name="content" rows="30">' . htmlspecialchars($content) . '</textarea>

        <input type="submit" name="load" value="Load">
        <input type="submit" name="save" value="Save">

    </form>
</body>

</html>
';

?>