# wpmanagement

Wordpress optimization and administration tools
==============

The following repository is dedicated to scripts, which are oriented towards: 

 - Optimizing Wordpress 
 - Solving common Wordpress problems

Currently there is one present script, which is in development. The file is named tool.php. The script is useful for cleaning and protecting your Wordpress site from Spam comments. 

What it does: 

1. Sends a $POST request to the Wordpress Plugin API.  
2. Fetches the download URL of an Anti-SPAM plugin. Here is the plugin: https://wordpress.org/plugins/anti-spam/
3. Downloads the plugin and extracts it in the /wp-content/plugins directory. 
4. Activates the plugin by using the activate_plugin function. 
5. Fetches the database configuration from the wp-config.php file. 
6. Allows you to either Delete all comments or only the unapproved ones.  

To use the script: 

1. Upload it in your main Wordpress directory. 
2. Open it in the browser. 
