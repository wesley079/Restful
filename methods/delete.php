<?php
//delete in the collection - deny access
if(!isset($_GET["id"])){
    http_response_code(405);
}
else{
    //delete in detailpage - show item
    $id = $_GET["id"];
    $query = "DELETE FROM songs where id='$id'";
    if($mysqli->query($query) === true) {
        http_response_code(204);
    }
}