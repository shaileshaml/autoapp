<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
$data = [ 'ip' =>  $_SERVER['REMOTE_ADDR'] ];
header('Content-type: application/json');
echo json_encode( $data );
?>
