<?php

session_start();

include "advisoriesdb.php";

connect_mysql();

$query = "SELECT versions FROM versions ORDER BY versions DESC";
$result = query_db( $query );

if ( $result->num_rows == 0 )
{
   $message = "No book versions found.";
   DisplayErrorMessage( $message );
   exit;
}

while ( list($v) = $result->fetch_row() )
{
  $versions[] = $v;
}

// Get the headers, menu, and footer code
$handle  = fopen("header1.html", "r");
$header1 = fread($handle, 8192);
fclose($handle);

# Fix the title of the page
$lines = explode("\n", $header1);
$lines[5] = "    <title>Security Advisories</title>";
$header1 = implode("\n", $lines);

$handle = fopen("header.html", "r");
$header = fread($handle, 8192);
fclose($handle);

# Remove first line from the header
$lines = explode("\n", $header);
unset($lines[0]);
$header = implode("\n", $lines);


$handle  = fopen("menu.html", "r");
$menu    = fread($handle, 8192);
fclose($handle);

$handle = fopen("footer.html", "r");
$footer = fread($handle, 8192);
fclose($handle);

$submit = ( isset($_POST['submit']) ) ? $_POST['submit'] : "notset";

// Start output
print $header1;
print $header;
print $menu;

$pop_message   = "";
$list_packages = "";
$only_headers= ( isset($_POST['headers'])  ) ? $_POST['headers']  : ""; 

if   ( $submit == "Search" ) search();
else                         main();

print $footer;
///////  End

function main()
{
  global $pop_message;
  global $list_packages;
  global $desc;

  global $versions;

  print "<div class='main'>\n";
  print "<h2>Linux From Scratch Security Advisories</h2>\n";
  print "<form method='post' action='{$_SERVER['PHP_SELF']}'>\n";

  # These are for resetting the form
  $radio       = ( isset($_POST['stype'])    ) ? $_POST['stype']    : ""; 
  $book        = ( isset($_POST['book'])     ) ? $_POST['book']     : ""; 
  $release     = ( isset($_POST['release'])  ) ? $_POST['release']  : ""; 
  $package     = ( isset($_POST['package'])  ) ? $_POST['package']  : ""; 
  $search      = ( isset($_POST['search'])   ) ? $_POST['search']   : ""; 
  $severity    = ( isset($_POST['severity']) ) ? $_POST['severity'] : ""; 
  $level       = ( isset($_POST['level'])    ) ? $_POST['level']    : ""; 
  $desc        = ( isset($_POST['desc'])     ) ? $_POST['desc']     : ""; 
//print_r($_POST);

  $radio1 = ( $radio == "Book" || $radio == "" ) ? "checked=checked"     : "";
  $radio2 = ( $radio == "package"              ) ? "checked=checked"     : "";
  $radio3 = ( $radio == "descr"                ) ? "checked=checked"     : "";
  $radio4 = ( $radio == "all"                  ) ? "checked=checked"     : "";

  $checked      = ( $severity == 'true'        ) ? "checked='checked'"   : "";
  $desc_checked = ( $desc == 'true'            ) ? "checked='checked'"   : "";

//  $b1 = ( $book == "LFS"                       ) ? "selected='selected'" : "";
//  $b2 = ( $book == "BLFS" || $book == ""       ) ? "selected='selected'" : "";

//  $r1 = ( $release == "10.0"                   ) ? "selected='selected'" : "";
//  $r2 = ( $release == "10.1"                   ) ? "selected='selected'" : "";
//  $r3 = ( $release == "11.0"                   ) ? "selected='selected'" : "";
//  $r4 = ( $release == "11.1"                   ) ? "selected='selected'" : "";
//  $r5 = ( $release == "11.2" || $release == "" ) ? "selected='selected'" : "";


  $s1 = ( $level == "Critical"                 ) ? "selected='selected'" : "";
  $s2 = ( $level == "High"                     ) ? "selected='selected'" : "";
  $s3 = ( $level == "Medium" || $level == ""   ) ? "selected='selected'" : "";
  $s4 = ( $level == "Low"                      ) ? "selected='selected'" : "";

  $radio_style1 = 'style="margin: 0; vertical-align: top"';
  $radio_style2 = 'style="align: left; font-weight: bold"';
  $radio_style2_debug = 'style="align: left; font-weight: bold; border: padding-top: 1em;"';

  echo <<<HTML
  <p>In the fields below, select the type of search:</p>
  <ul>
    <li>Selecting the desired version will display all advisories
        associated with that LFS/BLFS release.</li>
    <li>Selecting 'Package Name' will display all the advisories associated with
        all versions of the input package name. Package names may only 
        contain alphanumeric characters plus hyphens and underscores. 
        All other characters will be rejected. Partial names are accepted.</li>
    <!--<li>Selecting 'Description' will search the descriptions of all packages 
        for the input text and show maching advisories.  Do not use 
        punctuation characters.</li>-->
    <li>Selecting 'All Advisories' will show all the advisories in the database.  
        This could be quite long.</li>
    <li>If the 'Severity' box is checked, the results of a search will limit
        the output to advisories with the selected severity level.</li>
  </ul>

  <p>Search by:</p>

  <table style="margin-left: auto; margin-right:auto;">
  <tr>
      <td $radio_style2>
        <input type="radio" id="book" name="stype" value="Book" $radio_style1 $radio1 /> 
        Version</td>

      <td><select name="release">
HTML;

foreach ( $versions as $v )
{
  if ( $v == $release )
    echo "<option selected='selected'>$v</option>\n";
  else
    echo "<option>$v</option>\n";
}
  echo <<<HTML
          </select>
      </td>
  </tr>

  <tr>
      <td $radio_style2>
        <input type="radio" id="pname" name="stype" value="package" $radio_style1 $radio2 />
        Package Name</td>
     
      <td><input type="text" name="package" size="12" maxlength="30" value="$package"
                 style='color: black;'/></td>
  </tr>

<!--  <tr>
      <td $radio_style2>
        <input type="radio" id="descrip" name="stype" value="descr" $radio_style1 $radio3 />
        Description</td>

      <td><input type="text" name="search" size="12" maxlength="30" 
                 value="$search" style='color: black;'/> </td>
  </tr>-->

  <tr>
      <td $radio_style2>
        <input type="radio" id="all" name="stype" value='all' $radio_style1 $radio4 />
        All Advisories</td>
  </tr>
 
  <tr style="border: 1px solid red">
      <td style="align: left; font-weight: bold; padding-top: 0.0em;">
        <input type="checkbox" id="severity" name="severity" value="true" 
               style="margin: 1em; vertical-align: middle;" $checked />
        <!--<div style="margin-top: 0.9em; margin-right: 2em; float: right;">Severity</div>-->
        <span style="">Severity</span>
      </td>

      <td style="padding-top: 0.0em;">
          <select name="level">
            <option $s1>Critical</option>
            <option $s2>High</option>
            <option $s3>Medium</option>
            <option $s4>Low</option>
          </select>
      </td>
  </tr>

  <tr>
      <td style="align: left; font-weight: bold;">
        <input type="checkbox" id="desc" name="desc" value="true" 
               style="margin: 1em; vertical-align: top" $desc_checked />
        <div style="margin-top: 0.9em; margin-right: 2em; float: right;">Show Descriptions</div>
      </td>
  </tr>


  <tr>
      <th colspan="2" class="button" style="padding-top: 1em">
           <input type="submit" name='submit' value="Search" style="color: black" /></th>
  </tr>
  </table>

  </form>

HTML;

  if ( strlen($pop_message) > 0 ) DisplayErrorMessage( $pop_message );
  if ( strlen($list_packages) > 0 ) 
     printf( "<div>%s</div>", $list_packages );
}

function search()
{
   main();
   global $list_packages;
   global $pop_message;

   $search_type = $_POST['stype'];
   $severity    = ( isset($_POST['severity']) ) ? $_POST['severity'] : ""; 

   switch ( $search_type )
   {
     case "Book":
        search_by_book( $severity );
        break;

     case "package":
        search_by_package( $severity );
        break;

     case "all":
        display_all( $severity );
        break;
   }

   if ( strlen($pop_message) > 0   ) 
     DisplayErrorMessage( $pop_message );
   else  
     printf( "<div>%s</div>", $list_packages );
}

function search_by_book( $severity )
{
   global $pop_message;
   global $list_packages;

   $book    = $_POST['book'];
   $release = $_POST['release'];

   $query = "SELECT id, name, entry_date, severity, description " .
            "FROM advisories WHERE "                 .
            "SUBSTRING( id, 4, 4) = '$release'"; 

   if ( $severity != "" )
   {
      $level = $_POST['level'];
      $query .= " AND severity='$level'";
   }

   $query .= " ORDER BY id DESC, entry_date DESC, name";

   $result = query_db( $query );

   if ( $result->num_rows == 0 )
   {
      $pop_message = "No results found.";
      return;
   }

   $list_packages  = "<table style='margin-left: auto; margin-right: auto; margin-top: 2em;'>\n";
   $list_packages .= "<tr><th style='text-align:left;'>ID</th>
                          <th style='text-align:left;'>Package</th>
                          <th style='text-align:left;'>DateID</th>
                          <th style='text-align:left;'>Severity</th>\n";

   # $severity is reused here
   while ( list( $id, $name, $entry_date, $severity, $description ) = $result->fetch_row() )
   {
      $list_packages .= format_advisory( $name, $id, $entry_date, $severity, $description );
   }

   $list_packages .= "</table>";
}

function search_by_package( $severity )
{
   global $pop_message;
   global $list_packages;

   $pkg = $_POST['package'];

   #  Add validity check for $pkg
   define( "ALPHA", "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" );
   define( "DIGIT", "0123456789" );

   # Check for valid package/version
   $package     = $_POST['package']; 

   # Sanity check for package name
   if ( strlen($package) < 2 ) 
      $pop_message .= "Package name is too short.";
   else
   {
      $ok = validate( $package, ALPHA . DIGIT . "-_" );
      if ( ! $ok ) 
      {
         $pop_message .= "Bad characters in package name '$package'.<br/>";
         return;
      }
   }

   $query = "SELECT id, name, entry_date, severity, description " .
            "FROM advisories " .
            "WHERE name LIKE '%$pkg%' ";

   if ( $severity != "" )
   {
      $level = $_POST['level'];
      $query .=" AND severity='$level' ";
   }

   $query .= "ORDER BY id DESC, entry_date DESC, name";
//print $query;

   $result = query_db( $query );

   if ( $result->num_rows == 0 )
   {
      $pop_message = "No results found.";
      return;
   }

   $list_packages  = "<table style='margin-left: auto; margin-right: auto; margin-top: 2em;'>\n";
   $list_packages .= "<tr><th style='text-align:left;'>ID</th>
                          <th style='text-align:left;'>Package</th>
                          <th style='text-align:left;'>DateID</th>
                          <th style='text-align:left;'>Severity</th>\n";

   # $severity is reused here
   while ( list( $id, $name, $entry_date, $severity, $description ) = $result->fetch_row() )
   {
      $list_packages .= format_advisory( $name, $id, $entry_date, $severity, $description );
   }

   $list_packages .= "</table>";

}
/*
function search_description( $severity )
{
   global $pop_message;
   global $list_packages;

   $search = $_POST['search'];

   $query = "SELECT * FROM advisories WHERE " .
            "description LIKE '%$search%' ";
   if ( $severity != "" )
   {
      $level = $_POST['level'];
      $query .=" AND severity='$level'";
   }

   $query .= "ORDER BY package, pkg_version DESC";

   $result = query_db( $query );

   if ( $result->num_rows == 0 )
   {
      $pop_message = "No results found.";
      return;
   }

   while ( list($entry, $package, $pkg_version, $book, $book_version,
                $entry_date, $level, $description) = $result->fetch_row() )
   {
      # Highlight the search string
      $replace = "<span style='color: blue;'>$search</span>";

      # Escape any slashes
      $search      = preg_replace( "/\//", "\/", $search );

      $description = preg_replace( "/$search/", $replace, $description );

      $list_packages .= format_advisory( $package,  $pkg_version,
                                         $book,     $book_version, 
                                         $level,    $entry_date,
                                         $description );
   }
}
*/
function display_all( $severity )
{
   global $pop_message;
   global $list_packages;

   $query = "SELECT id, name, entry_date, severity, description " .
            "FROM advisories";

   if ( $severity != "" )
   {
      $level = $_POST['level'];
      $query .= " WHERE severity='$level';";
   }

   $result = query_db( $query );

   if ( $result->num_rows == 0 )
   {
      $pop_message = "No results found.";
      return;
   }

   $list_packages  = "<table style='margin-left: auto; margin-right: auto; margin-top: 2em;'>\n";
   $list_packages .= "<tr><th style='text-align:left;'>ID</th>
                          <th style='text-align:left;'>Package</th>
                          <th style='text-align:left;'>DateID</th>
                          <th style='text-align:left;'>Severity</th>\n";

   # $severity is reused here
   while ( list( $id, $name, $entry_date, $severity, $description ) = $result->fetch_row() )
   {
      $list_packages .= format_advisory( $name, $id, $entry_date, $severity, $description );
   }

   $list_packages .= "</table>";
}

function format_advisory( $name, $id, $entry_date, $severity, $description )
{
   global $list_packages;
   global $desc;

   $list_packages .= 
      "<tr><td>$id</td> 
           <td>$name</td> 
           <td>$entry_date</td> 
           <td>$severity</td></tr>\n";

   if ( $desc == "true" ) 
      $list_packages .= "<tr><td colspan='4'>$description</td></tr>\n";
}

function validate( $input, $valid )
{
  $bytes = str_split( $input );
  foreach ( $bytes as $byte )
  {
     if ( strpos( $valid, $byte ) === false ) return false;
  }

  return true;
}
?>

