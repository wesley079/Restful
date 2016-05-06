<?php
//variable to check how many empty fields
$empty = 0;

//get the ID
if(isset($_GET["id"])) {
    $id = $_GET["id"];


    $data = json_decode(file_get_contents('php://input'), true);

    $title = $data["title"];
    $author = $data["author"];
    $length = $data["length"];

//check title
    if ($data["title"] == null) {
        $query = "SELECT title FROM songs where id='$id'";
        $title = $mysqli->query($query);

        //empty field
        $empty++;
    } else {
        $title = $data["title"];
    }

//check author
    if ($data["author"] == null) {
        $query = "SELECT author FROM songs where id='$id'";
        $author = $mysqli->query($query);

        //empty field
        $empty++;
    } else {
        $author = $data["title"];
    }

//check length
    if ($data["length"] == null) {
        $query = "SELECT 'length' FROM songs where id='$id'";
        $title = $mysqli->query($query);

        //empty field
        $empty++;
    } else {
        $length = $data["title"];
    }

//If no new information
    if ($empty == 3) {
        http_response_code(400);
        exit;
    } else {

        $query = "UPDATE songs SET title='$title', author='$author', length='$length' WHERE id='$id'";
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

        $mysqli->query($query);
        exit;

    }
}
else{
    http_response_code(405);
}