404 PHP Checker
===============

This PHP script will help you to track and find broken links on your web site.  
It uses the [PHP Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/).

How does it work ?
==================

The script looks for links in HTML pages :
- IMG SRC
- A HREF
- LINK HREF
- SCRIPT SRC

and checks the HTTP header code.

Requirement
===========

PHP 5+

Installation
============

Download and extract files into a directory.

Usage
=====

The following command line

```bash
php 404.php http://www.example.com
```

will produce :

```bash
time : 1s - file : 2/2 (100%) - HTTP codes : 200 (1) 302 (1) 
```

Meaning that 2 files have been checked in 1 second, one file is "200 OK" and the other is "302 FOUND".  

If a 404 code is found, the url and parents are displayed.  
You can change the "config" part in the script `404.php`.  
This script is used on [404.repair](http://www.404.repair) so you can try it online.
