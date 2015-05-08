<html>
	<head>
		<title>SPAM tool</title>
		<style>
form {
	 text-align: center;

}

h2 {
	 text-align: center;
   }
   
body {
	background-color: linen;
}
</style>
	</head>
	
	<body>
			
		<h2>Simple SPAM Managing tool</h2>
			
<form action="" method="post">
<input type="radio" name="radio" value="all">Delete ALL comments
<br>
<input type="radio" name="radio" value="un">Delete UNapproved comments
<br>
<input type="submit" name="submit" value="Kill spam" />
</form>


			<?php
			 
include "wp-config.php"; #adds Wordpress configuration, so we can use DB config
include "wp-load.php";
include "wp-admin/includes/plugin.php";

$connect = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD); #connect to DB
    if (!$connect) {
    echo 'Database connect failed';
    } else {
    echo 'Connection to database OK!';
    }
		echo "<br>";

$select = mysql_select_db(DB_NAME); 
$tablename = $table_prefix . "comments";
$countunapproved  = ("SELECT COUNT(comment_approved) FROM $tablename WHERE comment_approved = 0");
$execute = mysql_query($countunapproved, $connect);
	if ($execute) {
    		echo ("Comments UNapproved (comment_approved = 0): "), mysql_result($execute, 0);}
	else {
		echo "Comment count failed";}    
    
		echo ('<br>');

$counttotal  = ("SELECT COUNT(comment_approved) FROM $tablename"); 
$execute = mysql_query($counttotal, $connect);
	if ($execute) {
    		echo ("Comments total: "), mysql_result($execute, 0);}
    	else {
    		echo "Comment count failed";}    
    	
		echo ('<br>');		

    $args = (object) array( 'slug' => 'Anti-spam' );
 
    $request = array( 'action' => 'plugin_information', 'timeout' => 15, 'request' => serialize( $args) );
 
    $url = 'http://api.wordpress.org/plugins/info/1.0/';
 
    $response = wp_remote_post( $url, array( 'body' => $request ) );
 
    $plugin_info = unserialize( $response['body'] );
 
    $durl = $plugin_info->download_link;
 
 	echo "$durl";
$download = file_put_contents("antispam.zip", file_get_contents("$durl")); #downloads the Anti-spam plugin

if ($download) {
echo "Download OK!";}
else {
echo "Download FAIL!";} #check if download is OK
echo "<br>";
$path = getcwd();

$zip = new ZipArchive; #extracts the .zip
if ($zip->open('antispam.zip') === TRUE) {
    $zip->extractTo("$path/wp-content/plugins/");
    $zip->close();
    echo 'Plugin extracted';
} else {
    echo 'Extract Failed';}
    
echo "<br>"; 

$activate = activate_plugin( "$path/wp-content/plugins/anti-spam/anti-spam.php" ); #Activate the plugin
if ( is_wp_error( $activate ) ) {
	
} else {

echo 'AntiSPAM plug-in activated!';}
    	
echo ('<br>');		

if (isset($_POST['submit'])) {
if(isset($_POST['radio']))
{ 
if ($_POST['radio']=="all") { $select = mysql_select_db(DB_NAME); 
$tablename = $table_prefix . "comments";
$query  = ("TRUNCATE $tablename"); #TRUNCATE comments
mysql_query($query, $connect);
echo "DELETED!"; 
}
elseif ($_POST['radio']=="un") { $select = mysql_select_db(DB_NAME); 
$tablename = $table_prefix . "comments";
$tablename2 = $table_prefix . "commentmeta"
$query  = ("DELETE FROM $tablename WHERE comment_approved = 0 or comment_approved = 'spam'"); 
mysql_query($query, $connect); 
$query2  = ("DELETE FROM $tablename2 WHERE comment_id NOT IN (SELECT comment_id FROM $tablename)");
mysql_query($query2, $connect); 
echo "DELETED!"; 
    } }}
else{ echo "<span>Please choose any radio button.</span>";}

mysql_close($connect);
exit;
?>
	</body>
</html>
