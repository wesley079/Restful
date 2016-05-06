<?php
//global vars
$BASE_URI = "/restfullEindopdracht/"; //BASE_URI (ending with "/")


//Load database & DEFINED VARIABLES
require_once("settings.php");

//connect to database - define at "Settings.php"
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

//check connection
if ($mysqli->connect_errno) {
    printf("DB connect failed: %s\n", $mysqli->connect_error);
    exit;
}

//get method
$methods = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

//Switch to include right file
switch ($methods) {
    case "GET":
        include_once("methods/get.php");
        break;
    case "POST":
        include_once("methods/post.php");
        break;
    case "OPTIONS":
        include_once("methods/options.php");
        break;
    case "DELETE":
        include_once("methods/delete.php");
        break;
    case "PUT":
        include_once("methods/put.php");
        break;
}
