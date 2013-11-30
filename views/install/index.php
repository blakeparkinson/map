<?php


/**
 * Bare minimum install handler: return success.
 */
$arr = array ('status'=>"success");

header('Content-type: application/json');
echo json_encode($arr);

