
Voobly-API-PHP-Class-Wrapper
============================

This is a PHP implementation for the Voobly Public API (http://www.voobly.com/pages/view/147/External-API-Documentation). 

Features
========

- Support for all the API calls: All the aviable API calls are fully implemented.

- Integrated Cache System: Simple but effective cache system. With this you won't do more than a few calls per day even with in a hight traffic site, so you don't have to worry about being blocked for doing for than 1000 daily calls.
IMPORTANT: This cache is and API-Level Cache, is not designed for be used as cache for your application.

- Auto-Split & Auto-Merge for large querys: When you are doing a query with a large uidlist (>30), Voobly server won't response your request. This PHP implementation auto-split the query, and auto-merge the results making your life easy.

Installation
============

1. Copy all the content of the package to your server.
2. Create a cache/ folder (must be writeable, chmod 777).
3. Open config.php and replace ENTER_YOUR_KEY_HERE for your API Key.
4. Point your browser to sample.php to see if it work.

How to USE
==========

See sample.php for code samples, it's very easy to use.

Requeriments
============

PHP 5.x

Changelog
=========

Version 1.0 
-Initial Release

License
=======

This package is distributed under the GNU Public License v2 - http://www.gnu.org/licenses/gpl-2.0.txt
