<?php

/**
 * MySQL CLI - Mini Utils
 *
 * @version 1.1
 * @author Creative Pulse
 * @copyright Creative Pulse 2013
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://www.creativepulse.gr
 */


// configuration //////////////////////////////////////////

$config = array();

$config['db_type'] = 'mysqli'; // values: mysql, mysqli -- default: mysqli

// end of configuration ///////////////////////////////////


class DB_mysql {

    public $res;

    public function connect($host, $username, $password, $db_name) {
        mysql_connect($host, $username, $password) or die('ERROR: Could not connect to database');

        if ($db_name != '') {
            mysql_select_db($db_name) or die('ERROR: Could not open database');
        }
    }

    public function query($sql) {
        $this->res = mysql_query($sql) or die('ERROR: ' . mysql_error());
    }

    public function fetch() {
        return mysql_fetch_assoc($this->res);
    }

}

class DB_mysqli {

    public $handle;
    public $res;

    public function connect($host, $username, $password, $db_name) {
        $this->handle = new mysqli($host, $username, $password, $db_name);
        if (mysqli_connect_error()) {
            die('ERROR: Could not access database. Error (' . mysqli_connect_errno() . '): '   . mysqli_connect_error());
        }
    }

    public function query($sql) {
        $this->res = $this->handle->query($sql);
        if (mysqli_connect_error()) {
            die('ERROR (' . mysqli_connect_errno() . '): '   . mysqli_connect_error());
        }
    }

    public function fetch() {
        return $this->res->fetch_assoc();
    }

}

$db_class = 'DB_' . $config['db_type'];
$db = new $db_class();


if (isset($_GET['req'])) {
    if (get_magic_quotes_gpc()) {
        $_GET['req'] = stripslashes($_GET['req']);
    }

    $request = '';
    for ($i = 0, $len = strlen($_GET['req']); $i < $len; $i++) {
        $request .= chr(ord($_GET['req'][$i]) ^ ($i % 2 == 0 ? 5 : 7));
    }

    $vars = array();
    $elements = explode("\t", $request, 5);
    foreach ($elements as $element) {
        $e = explode('=', $element, 2);
        $vars[$e[0]] = $e[1];
    }

    $db->connect((string) @$vars['host'], (string) @$vars['username'], (string) @$vars['password'], (string) @$vars['database']);

    if (!empty($vars['sql'])) {
        $db->query($vars['sql']);

        $i = 0;
        while ($rec = $db->fetch()) {
            $i++;
            echo "== Record $i ==============\n";
            foreach ($rec as $k => $v) {
                echo "$k: $v\n";
            }
        }
        
        echo "---\n";
    }
    
    return;
}

?>
<!DOCTYPE html>

<html>
<head>
<title>MySQL CLI</title>
<meta charset="utf-8">

<style type="text/css">
* {
    margin: 0;
}
body {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 11px;
    margin: 0;
}
html, body {
    height: 100%;
}
.top {
    min-height: 100%;
    height: auto !important;
    height: 100%;
    margin: 0 auto -90px auto;
}
.footer, .push {
    height: 90px;
}
.footer {
    background-color: #ccc;
    padding: 0 15px;
}
.footer_gap {
    height: 15px;
}
.footer input {
    margin-right: 10px;
}
.request {
    background: #cc9;
    padding: 10px;
}
.response {
    padding: 10px;
    font-size: 1.2em;
}
</style>

<script type="text/javascript">

function h_ajax_response() {
    if (ajax.readyState == 4) {
        if (ajax.status == 200) {
            document.getElementById("response_" + response_id).innerHTML = ajax.responseText.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\x22/g, '&quot;');
            window.location.hash = "request_" + response_id;
        }
        else {
            alert('HTTP error status ' + ajax.status + ':\n' + ajax.responseText);
        }

        ajax = null;
    }
}

var ajax = null, response_id = 0;

function send_sql() {
    if (ajax != null) {
        alert("Another request is in progress");
        return;
    }
    
    var frm = document.forms.frm;

    var sql = frm.sql.value.replace(/^(\s+|\s+)$/g, "");
    
    var request = "host=" + frm.host.value.replace(/^(\s+|\s+)$/g, "")
        + "\t" + "username=" + frm.username.value.replace(/^(\s+|\s+)$/g, "")
        + "\t" + "password=" + frm.password.value.replace(/^(\s+|\s+)$/g, "")
        + "\t" + "database=" + frm.database.value.replace(/^(\s+|\s+)$/g, "")
        + "\t" + "sql=" + sql;

    var req = "";
    for (var i = 0, len = request.length; i < len; i++) {
        req += String.fromCharCode(request.charCodeAt(i) ^ (i % 2 == 0 ? 5 : 7));
    }

    if (window.XMLHttpRequest) {
        ajax = new window.XMLHttpRequest();
    }
    else if (window.ActiveXObject) {
        ajax = new window.ActiveXObject('Microsoft.XMLHTTP');
    }

    if (!ajax) {
        alert("Critical Error: Your browser was unable to initialize the AJAX sub-system");
    }
    else {
        var responses = document.getElementById("responses");
        response_id++;
        
        var div = document.createElement("div");
        div.className = "request";
        div.setAttribute("id", "request_" + response_id);
        div.innerHTML = sql.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\x22/g, '&quot;');
        responses.appendChild(div);
        
        var div = document.createElement("div");
        div.className = "response";
        div.setAttribute("id", "response_" + response_id);
        responses.appendChild(div);
        
        ajax.open("GET", "mysql_cli.php?req=" + encodeURIComponent(req) + "&n=" + Math.random(), true);
        ajax.onreadystatechange = h_ajax_response;
        ajax.send(null);
    }

    return false;
}

</script>

</head>

<body>

<div class="top"> 
    <pre id="responses"></pre>

    <div class="push"></div> 
</div> 

<div class="footer">
    <div class="footer_gap"></div>
    <form name="frm" onsubmit="return send_sql()">
            Host <input name="host" type="text" value="localhost" />
            Username <input name="username" type="text" />
            Password <input name="password" type="password" />
            Database <input name="database" type="text" />
        <br/>
        <br/>SQL <input name="sql" type="text" size="100" /> <input type="submit" name="send" value="Send" />
    </form>
</div> 

</body>

</html>
