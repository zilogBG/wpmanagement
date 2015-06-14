 <?php

include "wp-config.php"; #adds Wordpress configuration, so we can use DB config
include "wp-load.php";
include_once "wp-admin/includes/plugin.php";
include_once "wp-includes/user.php";
include_once "wp-admin/includes/user.php";

$path = getcwd();

function create_newuser($username, $password, $email)
{
    
    $userdata = array(
        
        'user_login' => $username,
        'user_pass' => $password,
        'role' => 'administrator'
    );
    
    if (username_exists($username)) {
        
        echo ("The user $username already exists");
        
    } else {
        
        wp_insert_user($userdata);
        
    }
}

function install_antispam()
{
    
    $args        = (object) array(
        'slug' => 'Anti-spam'
    );
    $request     = array(
        'action' => 'plugin_information',
        'timeout' => 15,
        'request' => serialize($args)
    );
    $url         = 'http://api.wordpress.org/plugins/info/1.0/';
    $response    = wp_remote_post($url, array(
        'body' => $request
    ));
    $plugin_info = unserialize($response['body']);
    $durl        = $plugin_info->download_link;
    echo "$durl";
    echo "<br>";
    $download = file_put_contents("antispam.zip", file_get_contents("$durl")); #downloads the Anti-spam plugin
    if ($download) {
        echo "Download OK!";
    } else {
        echo "Download FAIL!";
    } #check if download is OK
    echo "<br>";
    $path = getcwd();
    $zip  = new ZipArchive; #extracts the .zip
    if ($zip->open('antispam.zip') === TRUE) {
        $zip->extractTo("$path/wp-content/plugins/");
        $zip->close();
        echo 'Plugin extracted';
    } else {
        echo 'Extract Failed';
    }
    echo "<br>";
    $activate = activate_plugin("$path/wp-content/plugins/anti-spam/anti-spam.php"); #Activate the plugin
    if (is_wp_error($activate)) {
    } else {
        echo 'AntiSPAM plug-in activated!';
    }
    echo ('<br>');
    
}

echo ('<html>
            <head>
                <title>Wordpress Tool</title>
    <style>
        form   {
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

        <h4>SPAM Managing</h4>
        
                <form method="post">
<input name="instalplugin" type="submit" value="Install Anti-SPAM plugin" />
  
<input name="deletespam" type="submit" value="Delete SPAM comments" />
 
<input name="deleteunap" type="submit" value="Delete Unapproved comments" />

<input name="meta" type="submit" value="Clean commentmeta table" />

<input name="optimize" type="submit" value="Optimize comment tables" />

<input name="deleteall" type="submit" style="color:red;" value="Delete all comments" />
</form>  

</body>
</html>');

echo ('Website database is: ' . DB_NAME);

echo ("<br>");

$connection    = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$tablecomments = $table_prefix . "comments";
$commentmeta   = $table_prefix . "commentmeta";
$userstable    = $table_prefix . "users";

if (!$connection) {
    die('Connect Error' . mysqli_connect_error());
} else {
    echo ("Connected succeffully to DB!");
}
echo ("<br>");

$countall   = mysqli_query($connection, "SELECT COUNT(*) FROM $tablecomments");
$countedall = mysqli_fetch_array($countall);

if ($countedall) {
    echo ("Comments total: "), $countedall[0];
} else {
    die('Connect Error: ' . mysqli_error($connection));
}

echo ("<br>");

$count   = mysqli_query($connection, "SELECT COUNT(*) FROM $tablecomments WHERE comment_approved = '0'");
$counted = mysqli_fetch_array($count);

if ($counted) {
    
    echo ("Comments UNapproved (comment_approved = 0): "), $counted[0];
}

else {
    die('Connect Error: ' . mysqli_error($connection));
}

echo ("<br>");

$countspam   = mysqli_query($connection, "SELECT COUNT(*) FROM $tablecomments WHERE comment_approved = 'spam'");
$countedspam = mysqli_fetch_array($countspam);

if ($countedspam) {
    
    echo ("Comments marked as spam (comment_approved = spam): "), $countedspam[0];
}

else {
    die('Connect Error: ' . mysqli_error($connection));
}

echo ("<br>");

echo ("Comments approved: "), $countedall[0] - $counted[0] - $countedspam[0];


echo ("<br>");


if (isset($_POST['instalplugin'])) {
    if (file_exists("$path/wp-content/plugins/anti-spam/anti-spam.php")) {
        
        echo ("Anti - SPAM plugin already installed!");
        
    } else {
        install_antispam();
        
    }
}


echo ("<br>");
if (isset($_POST['deleteall'])) {
    $deleteall = mysqli_query($connection, "TRUNCATE TABLE $tablecomments");
    if ($deleteall) {
        
        echo ("All comments deleted!");
    }
    
    else {
        die('Connect Error: ' . mysqli_error($connection));
    }
    
}

if (isset($_POST['deletespam'])) {
    $deletespam = mysqli_query($connection, "DELETE FROM $tablecomments WHERE comment_approved = 'spam'");
    if ($deletespam) {
        
        echo ("Comments marked as spam deleted!");
    }
    
    else {
        die('Connect Error: ' . mysqli_error($connection));
    }
    
}

if (isset($_POST['deleteunap'])) {
    $deleteunap = mysqli_query($connection, "DELETE FROM $tablecomments WHERE comment_approved = '0'");
    if ($deleteunap) {
        
        echo ("Comments marked as unapproved deleted!");
    }
    
    else {
        die('Connect Error: ' . mysqli_error($connection));
    }
}
if (isset($_POST['meta'])) {
    $deletemeta = mysqli_query($connection, "DELETE FROM $commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $tablecomments)");
    if ($deletemeta) {
        
        echo ("Commentmeta table cleaned!");
    }
    
    else {
        die('Connect Error: ' . mysqli_error($connection));
    }
    
}

if (isset($_POST['optimize'])) {
    $deleteall = mysqli_query($connection, "OPTIMIZE TABLE $tablecomments, $commentmeta");
    if ($deleteall) {
        
        echo ("Tables optimized!");
    }
    
    else {
        die('Connect Error: ' . mysqli_error($connection));
    }
    
}

echo ("<h4>User Managing</h3>");

$username = $_POST["cuser"];
$password = $_POST["cpass"];
$email    = "domain@domain.com";


if (isset($_POST['cuser'], $_POST['cpass'])) {
    create_newuser($username, $password, $email);
}

echo ("<br>");


$id = $_POST['deluser'];

if (isset($_POST['deluser'])) {
    wp_delete_user($id);
    
}

$all_admin = new WP_User_Query(array(
    'role' => 'administrator'
));
echo ("Administrator users:");
echo ("<br>");
echo ("<br>");
if (!empty($all_admin->results)) {
    foreach ($all_admin->results as $user) {
        echo "
    
        <table border='1' style='width:100%; table-layout: fixed;'> 
        <tr>
        <td>$user->ID</td>
        <td>$user->user_login</td>
        <td>$user->user_pass</td>
        <td><form method='post'><button type='submit'; style='color:red'; name='deluser' value='$user->ID'>Delete user!</button></form></td> 
        </tr>
        </table>";
    }
} else {
    echo 'No users found.';
}

echo ("<br>");

echo ("Add an administrator user:");
echo ('<form style="text-align:left;" method="post"  action="">
Username:
<input type="text" name="cuser" value="">
<br>
Password:
<input type="text" name="cpass" value="">
<br>
<input type="submit" value="Submit">
</form>');

if ($_POST["cuser"] === "")
    echo "You are trying to add a user without a username!";
if ($_POST["cpass"] === "")
    echo "<br>You should specify a password for the user!";


mysqli_close($connection);
exit();
?> 
