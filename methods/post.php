<?php

//get Content-type and save for switch
$methods = $_SERVER["CONTENT_TYPE"];

//Check accept header - If none ERROR & EXIT
If(!isset($_SERVER["HTTP_ACCEPT"])){
    error(415);
}

switch ($methods) {
    case "application/json":
        jsonCoded();
        break;

    case "application/x-www-form-urlencoded":
        urlEncoded();
        break;
    default;
        //No supported content-type ERROR & EXIT
        error(400);
        break;

}
//content type json
function jsonCoded()
{
    $data = json_decode(file_get_contents('php://input'), true);

    $title  = $data["title"];
    $author = $data["author"];
    $length = $data["length"];

    //check if empty
    if($title == null || $author == null || $length == null){
        emptyPost();
    }

    //insert posted data
    $query = "INSERT INTO songs (title, author, length) VALUES ('$title', '$author', '$length')";
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

    if($mysqli->query($query) === true){
        //item posted
        http_response_code(201);
    }

}

//content type x-www-form-urlencoded
function urlEncoded()
{
    $title  = $_POST["title"];
    $author = $_POST["author"];
    $length = $_POST["length"];

    //check if empty
    if($title == null || $author == null || $length == null){
        emptyPost();
    }

    //insert posted data
    $query = "INSERT INTO songs (title, author, length) VALUES ('$title', '$author', '$length')";
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

    if($mysqli->query($query) === true){
        //item posted
        http_response_code(201);
    }

}

/**
 * Send error code to header and exit code
 * @param $errorCode
 */
function error($errorCode){

    if($errorCode == 400){
        http_response_code(400);
        exit;
    }
    if($errorCode == 415){
        http_response_code(415);
        exit;
    }
}


function emptyPost(){
    http_response_code(400);
    exit;
}

function result(){

}