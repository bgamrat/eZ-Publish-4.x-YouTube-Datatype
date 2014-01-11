<?php /* ?ini charset="iso-8859-1"?

# Although these file settings are cleared on .ini cache clearing - the content
# based on them will persist until a new version is created or the versioned 
# files are deleted manually.  

[FileSettings]
# Directory where versioned instances of the YouTube thumbnails is stored
# Placed under var/<siteaccess>/storage.
# Must be manually created with appropriate ownership and permissions
VersionedFiles=youtube

[Installation]
# Directory eZ is located in, if required.
# For hostname access, where DocumentRoot is pointing at the eZ directory, this should be a slash.
# For URL access, where DocumentRoot is pointing above the eZ directory, this should be the name
# of the directory eZ is located in, followed by a slash.
# If DocumentRoot points to /var/www/html, and eZ is installed in ez, Directory should be set like so:
# Directory=/ez/
# This setting is used by the template to adjust the URLs of files.
Directory=/

*/ ?>
