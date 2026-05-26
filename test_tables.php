<?php
$c=new mysqli('localhost','root','','campustrack');
$r=$c->query('SHOW TABLES');
while($row=$r->fetch_array()) echo $row[0] . "\n";
