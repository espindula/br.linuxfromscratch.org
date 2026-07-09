<?php
$database    = "advisories";
$user        = "advisory_user";        
$db_password = "";
$mysqli      = NULL;

function DisplayErrorMessage($message)
{
   printf("<blockquote><blockquote><blockquote><h3 style='color: #cc0000;'>");
   printf("%s</h3></blockquote></blockquote></blockquote>\n", $message);
}

// Open a persistent connection to the server
function connect_mysql()
{
   global $host;
   global $user;
   global $db_password;
   global $database;
   global $mysqli;

   //$mysqli = new mysqli( "localhost", $user, $db_password, $database );
   $mysqli = new mysqli( "127.0.0.1", $user, $db_password, $database );

   if ( $mysqli->connect_errno )
   {
      DisplayErrorMessage( "Failed to connect to MySQL: (" . 
                           $mysqli->connect_errno . ") " . 
                           $mysqli->connect_error );
      exit();
   }
}

function query_db( $query )
{
  global $database;
  global $mysqli;

  $result = $mysqli->query( $query );

  if ( $mysqli->errno )
  {
     $mysqli->query( "UNLOCK TABLES" );

     $message = $query . "<br/>" . 
                $mysqli->errno . ":" .  $mysqli->error . "<br/>\n";

     DisplayErrorMessage( $message );
     exit();
  }

  return $result;
}
?>
