# YouTube Datatype Extension for eZ Publish 4.x

This extension provides a YouTube datatype for eZ Publish 4.x.

## Overview

The datatype has two attributes, the name and the YouTube view URL, for example: http://www.youtube.com/watch?v=EtOoQFa5ug8.  The YouTube content may be displayed as an image thumbnail, suitable for display in a list, or as a video embedded on the page.

This repository also includes a package with a YouTube video class which can be used as an example for template development.

## Installation

Place the files in the youtube directory under the extension directory of eZ Publish 4.x, enable the extension, regenerate the autoload array and clear the cache.

You may use the content class export package as an example for templates and implementation ideas.

## Use

The datatype is an extension of the image datatype and behaves much like it.  

In line view, an image thumbnail is displayed.  The thumbnail images are managed as standard eZ Publish images, although they are copied from YouTube.  The datatype uses the default YouTube thumbnail.

In full view, the YouTube video is embedded in the page for viewing, with the default YouTube HTML.

## Limitations

This datatype was developed under and tested with eZ Publish Community Project 2011.12, which is in the eZ Publish 4.x series.  It will not work with eZ Publish 5.x installations.
=======
eZ-Publish-4.x-YouTube-Datatype
===============================

A datatype which can be used to include YouTube content in eZ Publish 4.x sites.
