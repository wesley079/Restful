<?php
/*
 * All these variables are used as DEFINE in all the code.
 * Only edit these items
 */

//Connect to the database - GLOBAL
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_DATABASE", "playlists");
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

//self link
define("COLLECTION", "http://localhost/restfullEindopracht/songs/"); // IMPORTANT: END WITH BACKSLASH
