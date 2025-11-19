<?php
$data = [ 'ip' =>  $_SERVER['REMOTE_ADDR'] ];
header('Content-type: application/json');
echo json_encode( $data );
?>
