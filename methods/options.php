<?php
if (isset($_GET["id"])){

    //Detail page -> Single post
    $methods = "single";
}
if (!isset($_GET["id"])){

    //No detail page -> Show all
    $methods = "multiple";
}

switch ($methods) {
    case "single":
        //Detail page -> GET, PUT, DELETE, OPTIONS
        header('Allow: GET, PUT, DELETE, OPTIONS');
        break;
    case "multiple":
        //All results -> GET, POST, OPTIONS
        header('Allow: GET, POST, OPTIONS');
        break;
}


