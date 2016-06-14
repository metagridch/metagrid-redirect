This is a small script to redirect request from the metagrid crawler to a static html file.

Metagrid Redirect Features
======
- Generate temporary redirects (302) for requests from the metagrid crawler
- Generate a XML sitemap for the metagrid crawler

Test
------------
The easiest way to test metagrid-redirect is with [git](http://git.org/).
```bash
git clone git@source.dodis.ch:metagrid/metagrid-redirect.git
```

Use [docker](http://www.docker.com) to run the test
```bash
docker-compose build
docker-compose up -d
```

Open your browser and connect to http://localhost:80/1221.html. You should get redirected to the corresponding file (test-tester(1980-2010)_1221.html).

Requirements
-----
The scripts works together with most LAMP Systems. You need:
 * Apache
 * php
 * mode_rewrite

To enable mod_rewrite just run
```bash
a2enmod rewrite
```

Usage
-----
Place the script (under src/) in the folder with the static html content and configure it.

Configuration
----
You can define some parameters to configure the script.

Define a regex expression for your unique identifier. In this example a number of undefined length
```php
$identifier = "(\d)";
```
Define a regex expression to identify your resources. You need to use the identifier in this expression. In this example all url's that end with a number and .html
```php
$pattern = "/".$identifier.".html$/";
```
Define if the script should do a redirect (302) or print the resource and answer with 200. Redirect is recommended
```php
$doRedirect = true;
```
Should the script generate a sitemap for metagrid and other crawlers
```php
$doSitemap = true;
```
The url of the sitemap. Be aware of conflicts with google sitemaps
```php
$sitemapUrl = "/sitemap.xml$/";
```

Planned features
-----
The script is very basic and perhabs will handle more complex situations in the future.
* A small cache to enhance performance.
* build a router class to abstract some businesslogic