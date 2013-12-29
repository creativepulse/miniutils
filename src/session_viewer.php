<?php

/**
 * Session Viewer - Mini Utils
 *
 * @version 1.0
 * @author Creative Pulse
 * @copyright Creative Pulse 2013
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


session_start();

if (!empty($_POST['clear_data'])) {
    $_SESSION = array();
}

?><!DOCTYPE html>
<html>
<head>
<title>Session Viewer</title>
<meta charset="utf-8">
<style type="text/css">
body {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 14px;
}
h1 {
    font-size: 1em;
}
.empty {
    display: inline;
    background-color: #777;
    color: #fff;
    border-radius: 4px;
    padding: 1px 7px 2px 7px;
    line-height: 3em;
}
</style>
</head>

<body>

<h1>Existing session data</h1>

<?php

if (empty($_SESSION)) {
    echo
'<p class="empty">Session is empty</p>
';
}
else {
    echo
'<pre>' . htmlspecialchars(var_export($_SESSION, true)) . '</pre>

<form name="frm" method="post" action="">
<input type="submit" name="clear_data" value="Clear all">
</form>
';
}

?>
</body>

</html>

