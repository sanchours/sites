<?php

session_start();

header('HTTP/1.1 404');
header('Status: 404 Not Found');
header('Content-Type: text/html;  charset=utf-8');

echo 'File not found!';
