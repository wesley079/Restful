<?php

//Check accept header
if (!$_SERVER["HTTP_ACCEPT"] == null) {
    //custom header
    $headerType = $_SERVER["HTTP_ACCEPT"];
} else {
    //No accept sended
    http_response_code(415);
    exit;
}

if(isset($_GET["id"])){

    $id = $_GET["id"];
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);
    $query = "SELECT * FROM songs where id='$id'";

    $result = $mysqli->query($query);

    if ($result->num_rows == null) {
        //Detail doesn't exist. ERROR
        http_response_code(404);
        exit;
    }


}

//Switch to include right file
switch ($headerType) {
    case "application/json":
        jsonView();
        break;
    case "application/xml":
        xmlView();
        break;
    default;
        error();
        break;
}

/*
 * Create json for the visitor
 */
function jsonView()
{
    //check if there is a limit
    if (isset($_GET["limit"])) {
        jsonLimited();
    }

    //create std class to fill with json data
    $songs = new stdClass();

    //Check amount of items
    if (isset($_GET["id"])) {        //only 1 get item
        $id = $_GET["id"];
        $query = "SELECT * FROM songs where id='$id'";

    } else {                        //multiple get items
        $query = "SELECT * FROM songs";

    }

    //Get items from the
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

    //Get all database items
    $songData = $mysqli->query($query);

    //Get amount of items
    $i = 0;
    $items = [];

    //count items in database
    $dbCountQuery = "SELECT COUNT(*) FROM Orders";

    $dbCount = $mysqli->query($dbCountQuery);

    //Foreach every item in $SongData
    foreach ($songData as $songItem) {

        //New item, ++ item amount
        $i++;

        //creating an object for the information

        $songInfo = new stdClass();
        //Fill links
        $linksItem = [];

        //Fill self
        $self = new stdClass();
        $self->rel = "self";
        $self->href = COLLECTION . $songItem["id"];   //COLLECTION defined at 'settings.php'.

        //Fill collection
        $collection = new stdClass();
        $collection->rel = "collection";
        $collection->href = COLLECTION;             //COLLECTION defined at 'settings.php'.

        //add Self & Collection to array
        $linksItem[] = $self;
        $linksItem[] = $collection;

        //add information to the object
        $songInfo->id = $songItem["id"];            //id
        $songInfo->title = $songItem["title"];      //Song title
        $songInfo->author = $songItem["author"];    //Artist
        $songInfo->length = $songItem["length"];    //Duration of song2
        $songInfo->links = $linksItem;              //Links (created above)


        //add the object to an array
        if (isset($_GET["id"])) {

            $songs = $songInfo;         //single item - No array
        } else {
            $items[] = $songInfo;       //multiple items - Array
        }
    }

    if (!isset($_GET["id"])) {
        //put items in array songs
        $songs->items = $items;

        //Create links
        $links = [];

        //self
        $selfLink = new stdClass();
        $selfLink->rel = "self";
        $selfLink->href = COLLECTION;

        //Fill object links
        $links[] = $selfLink;

        $songs->links = $links;

        //Pagination for object
        $pagination = new stdClass();

        $pagination->currentPage = 1;
        $pagination->currentItems = $i;
        $pagination->totalPages = 1;
        $pagination->totalItems = "$i";

        //create array for links
        $paginationLinks = [];

        //First page
        $first = new stdClass();
        $first->rel = "first";
        $first->page = 1;
        $first->link = COLLECTION;
        //last page
        $last = new stdClass();
        $last->rel = "last";
        $last->page = 1;
        $last->link = COLLECTION;
        //previous page
        $previous = new stdClass();
        $previous->rel = "previous";
        $previous->page = 1;
        $previous->link = COLLECTION;
        //next page
        $next = new stdClass();
        $next->rel = "next";
        $next->page = 1;
        $next->link = COLLECTION;

        //add links to paginationLinks array
        $paginationLinks[] = $first;
        $paginationLinks[] = $last;
        $paginationLinks[] = $previous;
        $paginationLinks[] = $next;

        //add to songs
        $songs->pagination = $pagination;
        $songs->pagination->links = $paginationLinks;
    }


//echo the json code and set header to "JSON"
    header('Content-Type: application/json');
    echo json_encode($songs);
    exit;

}

/**
 * Create XML for the visitor
 */
function xmlView()
{
    //check if there is a limit
    if (isset($_GET["limit"])) {
        xmlLimited();
    }

    //Check amount of items

    if (isset($_GET["id"])) {        //only 1 get item
        $id = $_GET["id"];
        $query = "SELECT * FROM songs where id='$id'";
        $amount = "single";

    } else {                        //multiple get items
        $query = "SELECT * FROM songs";
        $amount = "multiple";

    }
    //Database connection
    //get database
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

    //Get database items
    $songData = $mysqli->query($query);

    if($songData == null){
        http_response_code(404);
        exit;
    }

    /**
     * If ID is set, only one item will be shown, create this XML version
     */
    if ($amount == "single") {
        $oXMLout = new XMLWriter();
        $oXMLout->openMemory();

        $oXMLout->text("<?xml version=\"1.0\" encoding=\"utf-8\"?>");

        foreach ($songData as $songItem) {
            $oXMLout->startElement("item");
            $oXMLout->writeElement("id", $songItem["id"]);
            $oXMLout->writeElement("title", $songItem["title"]);
            $oXMLout->writeElement("author", $songItem["author"]);
            $oXMLout->writeElement("length", $songItem["length"]);

            //Create all links
            $oXMLout->startElement("links");

            //self
            $oXMLout->startElement("link");
            $oXMLout->writeElement("rel", "self");
            $oXMLout->writeElement("href", COLLECTION . $songItem['id']);
            $oXMLout->endElement();

            //Collection
            $oXMLout->startElement("link");
            $oXMLout->writeElement("rel", "collection");
            $oXMLout->writeElement("href", COLLECTION);
            $oXMLout->endElement();
            $oXMLout->endElement();

            $oXMLout->endElement();
        }


    }
    /**
     * If ID is not set, only one item will be shown, create this XML version
     */
    if ($amount == "multiple") {

        $oXMLout = new XMLWriter();
        $oXMLout->openMemory();

        $oXMLout->text("<?xml version=\"1.0\" encoding=\"utf-8\"?>");

        //Start XML sheet
        $oXMLout->startElement("songs");

        //Create all items
        $oXMLout->startElement("items");


        //count number of items
        $i = 0;

        //count items in database
        $dbCountQuery = "SELECT COUNT(*) FROM Orders";

        $dbCount = $mysqli->query($dbCountQuery);

        foreach ($songData as $songItem) {

            $i++;
            $oXMLout->startElement("item");
            $oXMLout->writeElement("id", $songItem["id"]);
            $oXMLout->writeElement("title", $songItem["title"]);
            $oXMLout->writeElement("author", $songItem["author"]);
            $oXMLout->writeElement("length", $songItem["length"]);

            //Create all links
            $oXMLout->startElement("links");

            //self
            $oXMLout->startElement("link");
            $oXMLout->writeElement("rel", "self");
            $oXMLout->writeElement("href", COLLECTION . $songItem['id']);
            $oXMLout->endElement();

            //Collection
            $oXMLout->startElement("link");
            $oXMLout->writeElement("rel", "collection");
            $oXMLout->writeElement("href", COLLECTION);
            $oXMLout->endElement();
            $oXMLout->endElement();

            $oXMLout->endElement();
        }
        $oXMLout->endElement();

        //Create all links
        $oXMLout->startElement("links");
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "self");
        $oXMLout->writeElement("href", COLLECTION);
        $oXMLout->endElement();
        $oXMLout->endElement();

        //Create Pagination
        $oXMLout->startElement("pagination");
        $oXMLout->writeElement("currentPage", 1);
        $oXMLout->writeElement("currentItems", $dbCount);
        $oXMLout->writeElement("totalPages", 1);
        $oXMLout->writeElement("totalItems", $dbCount);

        $oXMLout->startElement("links");
        //first
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "first");
        $oXMLout->writeElement("page", "1");
        $oXMLout->writeElement("href", COLLECTION);
        $oXMLout->endElement();

        //last
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "last");
        $oXMLout->writeElement("page", "1");
        $oXMLout->writeElement("href", COLLECTION);
        $oXMLout->endElement();

        //previous
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "previous");
        $oXMLout->writeElement("page", "1");
        $oXMLout->writeElement("href", COLLECTION);
        $oXMLout->endElement();

        //next
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "next");
        $oXMLout->writeElement("page", "1");
        $oXMLout->writeElement("href", COLLECTION);
        $oXMLout->endElement();
        $oXMLout->endElement();

        $oXMLout->endElement();

        //end <SONGS>
        $oXMLout->endElement();
    }

    //print
    header('Content-Type: application/xml');
    print $oXMLout->outputMemory();
    exit;
}

function jsonLimited()
{
    //Print with correct pagination

    //check if there is a limit
    $limit = $_GET["limit"];

    if(isset($_GET["start"])){
        $start = $_GET["start"];
    }
    else{
        $start = 1;
    }

    //create std class to fill with json data
    $songs = new stdClass();

    //multiple get items
    $query = "SELECT * FROM songs";


    //Get items from the
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

    //Get all database items
    $songData = $mysqli->query($query);

    //Get amount of items
    $i = 0;
    $limitCount = 1;
    $items = [];

    //count items in database

    //Foreach every item in $SongData
    foreach ($songData as $songItem) {

        //itemAmount ++, item gone
        $limitCount++;

        if($limitCount > $start && $i < $limit) {
            //New item, ++ item amount
            $i++;

            //creating an object for the information

            $songInfo = new stdClass();
            //Fill links
            $linksItem = [];

            //Fill self
            $self = new stdClass();
            $self->rel = "self";
            $self->href = COLLECTION . $songItem["id"];   //COLLECTION defined at 'settings.php'.

            //Fill collection
            $collection = new stdClass();
            $collection->rel = "collection";
            $collection->href = COLLECTION;             //COLLECTION defined at 'settings.php'.

            //add Self & Collection to array
            $linksItem[] = $self;
            $linksItem[] = $collection;

            //add information to the object
            $songInfo->id = $songItem["id"];            //id
            $songInfo->title = $songItem["title"];      //Song title
            $songInfo->author = $songItem["author"];    //Artist
            $songInfo->length = $songItem["length"];    //Duration of song2
            $songInfo->links = $linksItem;              //Links (created above)


            //add the object to an array
            if (isset($_GET["id"])) {

                $songs = $songInfo;         //single item - No array
            } else {
                $items[] = $songInfo;       //multiple items - Array
            }
        }
        else{

        }
    }

    if (!isset($_GET["id"])) {
        //put items in array songs
        $songs->items = $items;

        //Create links
        $links = [];

        //self
        $selfLink = new stdClass();
        $selfLink->rel = "self";
        $selfLink->href = COLLECTION;

        //Fill object links
        $links[] = $selfLink;

        $songs->links = $links;

        //Pagination for object
        $pagination = new stdClass();
        $currentPage = intval(($start-1+$limit) / 2);
        $pagination->currentPage = $currentPage;
        $pagination->currentItems = $i;
        $pagination->totalPages = ceil((($limitCount-1)/ $limit));
        $pagination->totalItems = $limitCount-1;

        //create array for links
        $paginationLinks = [];

        //First page
        $first = new stdClass();
        $first->rel = "first";
        $first->page = 1;
        $first->href = COLLECTION."?start=1"."&limit=".$limit;
        //last page
        $last = new stdClass();
        $last->rel = "last";
        $lastNumber = ceil(($limitCount-1) / $limit);
        $last->page = $lastNumber;
        $last->href = COLLECTION."?start=".($limitCount-$limit)."&limit=".$limit;
        //previous page
        $previous = new stdClass();
        $previous->rel = "previous";
        $prev = $currentPage-1;
        if($prev <= 0){
            $prev = 1;
        }
        $previous->page = $prev;
        $previous->href = COLLECTION."?start=".($start-$limit)."&limit=".$limit;

        //next page
        $next = new stdClass();
        $next->rel = "next";
        $nextPage = $currentPage+1;
        if($nextPage >= $lastNumber){
            $nextPage = $lastNumber;
        }
        $next->page = $nextPage;
        $next->href = COLLECTION."?start=".($start+$limit)."&limit=".$limit;

        //add links to paginationLinks array
        $paginationLinks[] = $first;
        $paginationLinks[] = $last;
        $paginationLinks[] = $previous;
        $paginationLinks[] = $next;

        //add to songs
        $songs->pagination = $pagination;
        $songs->pagination->links = $paginationLinks;
    }


//echo the json code and set header to "JSON"
    header('Content-Type: application/json');
    echo json_encode($songs);
    exit;


}


function xmlLimited()
{

    //Check amount of items

    if (isset($_GET["id"])) {        //only 1 get item
        $id = $_GET["id"];
        $query = "SELECT * FROM songs where id='$id'";
        $amount = "single";

    } else {                        //multiple get items
        $query = "SELECT * FROM songs";
        $amount = "multiple";

    }
    //Database connection
    //get database
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

    //Get database items
    $songData = $mysqli->query($query);

    /**
     * If ID is not set, only one item will be shown, create this XML version
     */
    if ($amount == "multiple") {

        $oXMLout = new XMLWriter();
        $oXMLout->openMemory();

        $oXMLout->text("<?xml version=\"1.0\" encoding=\"utf-8\"?>");

        //Start XML sheet
        $oXMLout->startElement("songs");

        //Create all items
        $oXMLout->startElement("items");

        //check if there is a limit
        $limit = $_GET["limit"];

        if(isset($_GET["start"])){
            $start = $_GET["start"];
        }
        else{
            $start = 1;
        }

        $i = 0;
        $limitCount = 1;

        foreach ($songData as $songItem) {

            //itemAmount ++, item gone
            $limitCount++;

            if($limitCount > $start && $i < $limit) {

                $i++;
                $oXMLout->startElement("item");
                $oXMLout->writeElement("id", $songItem["id"]);
                $oXMLout->writeElement("title", $songItem["title"]);
                $oXMLout->writeElement("author", $songItem["author"]);
                $oXMLout->writeElement("length", $songItem["length"]);

                //Create all links
                $oXMLout->startElement("links");

                //self
                $oXMLout->startElement("link");
                $oXMLout->writeElement("rel", "self");
                $oXMLout->writeElement("href", COLLECTION . $songItem['id']);
                $oXMLout->endElement();

                //Collection
                $oXMLout->startElement("link");
                $oXMLout->writeElement("rel", "collection");
                $oXMLout->writeElement("href", COLLECTION);
                $oXMLout->endElement();
                $oXMLout->endElement();

                $oXMLout->endElement();
            }
        }
        $oXMLout->endElement();

        //Create all links
        $oXMLout->startElement("links");
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "self");
        $oXMLout->writeElement("href", COLLECTION);
        $oXMLout->endElement();
        $oXMLout->endElement();

        //Create Pagination
        $oXMLout->startElement("pagination");
        $currentPage = intval(($start-1+$limit) / 2);
        $oXMLout->writeElement("currentPage", $currentPage);
        $oXMLout->writeElement("currentItems", $i);
        $oXMLout->writeElement("totalPages", ceil((($limitCount-1)/ $limit)));
        $oXMLout->writeElement("totalItems",  $limitCount-1);

        $oXMLout->startElement("links");
        //first
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "first");
        $oXMLout->writeElement("page", 1);
        $oXMLout->writeElement("href", COLLECTION."?start=1"."&limit=".$limit);
        $oXMLout->endElement();

        //last
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "last");
        $lastNumber = ceil(($limitCount-1) / $limit);
        $oXMLout->writeElement("page", $lastNumber);
        $oXMLout->writeElement("href", COLLECTION."?start=".($limitCount-$limit)."&limit=".$limit);
        $oXMLout->endElement();

        //previous
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "previous");
        $prev = $currentPage-1;
        if($prev <= 0){
            $prev = 1;
        }
        $oXMLout->writeElement("page", $prev);
        $oXMLout->writeElement("href", COLLECTION."?start=".($start-$limit)."&limit=".$limit);
        $oXMLout->endElement();

        //next
        $oXMLout->startElement("link");
        $oXMLout->writeElement("rel", "next");
        $nextPage = $currentPage+1;
        if($nextPage >= $lastNumber){
            $nextPage = $lastNumber;
        }
        $oXMLout->writeElement("page", $nextPage);
        $oXMLout->writeElement("href", COLLECTION."?start=".($start+$limit)."&limit=".$limit);
        $oXMLout->endElement();
        $oXMLout->endElement();

        $oXMLout->endElement();

        //end <SONGS>
        $oXMLout->endElement();
    }

    //print
    header('Content-Type: application/xml');
    print $oXMLout->outputMemory();
    exit;
}

/**
 * Error if visitor made a mistake or asks for a non supported language
 */
function error()
{
    http_response_code(415);
    header('Content-Type: application/json');
    $errorInfo = new stdClass();
    $errorInfo->message = "Unsupported format: " . $_SERVER["HTTP_ACCEPT"];

    echo json_encode($errorInfo);
    exit;

}
