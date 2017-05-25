# php-megasync
PHP implementation of [megaSync](https://github.com/XJIOP/php-megasync).

Important
=========

php-megasync is one side Sync only  
Local -> Remote (with removal)


Requirement
============

You need to have a linux server with running php.
php exec() function must be accepted to allow you to use correctly the service.
on your Linux server you need to install one packet megatools who can be here:  
https://github.com/megous/megatools


Prepare Project
===============

- Edit ```megasync.php``` and set Login details


Additional options
==================

```
$megatools->CONFIG["LOG"] = true;  
$megatools->CONFIG["EXTENSION"] = array("jpg", "png", "gif");
```


Run sync
==========

```
$ php megasync.php "/local/folder" "/mega/folder"
```
