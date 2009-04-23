<?php

header('Content-Type: text/javascript');

print "\$Kumbia = new Object();
\$Kumbia.app = \"".urldecode($_REQUEST['app']) ."\";
\$Kumbia.path = \"".urldecode($_REQUEST['path']) ."\";
\$Kumbia.controller = \"".$_REQUEST['controller'] ."\";
\$Kumbia.action = \"".$_REQUEST['action'] ."\";
\$Kumbia.id = \"".$_REQUEST['id']."\";\n";
