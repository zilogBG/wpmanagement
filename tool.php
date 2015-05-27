<?php

include "wp-config.php"; #adds Wordpress configuration, so we can use DB config
include "wp-load.php";
include "wp-admin/includes/plugin.php";
$path = getcwd();
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
                <title>SPAM tool</title>
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

        <h2>Simple SPAM Managing tool</h2>

        <form method="post" action="">
<input name="deleteall" type="submit" value="Delete all comments" />
</form>

           <form method="post" action="">
<input name="deletespam" type="submit" value="Delete SPAM comments" />
</form>

        <form method="post" action="">
<input name="deleteunap" type="submit" value="Delete Unapproved comments" />
</form>     

        <form method="post" action="">
<input name="meta" type="submit" value="Clean commentmeta table" />
</form>   

</body>
</html>');

echo (DB_NAME);

echo ("<br>");

$connection    = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$tablecomments = $table_prefix . "comments";
$commentmeta   = $table_prefix . "commentmeta";

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

if (file_exists("$path/wp-content/plugins/anti-spam/anti-spam.php")) {
    
    echo ("Anti - SPAM plugin already installed!");
    
} else {
    install_antispam();
    
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

mysqli_close($connection);
exit;
?>
