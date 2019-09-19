# Metagrid Redirect
This is a small script to redirect request from the metagrid crawler to a static html file. This may be helpful in the case you have a dump of static html files from a internal system with seo optimized filenames.

__Features:__
- Generate temporary redirects (302) for requests from the metagrid crawler
- Generate a XML sitemap for the metagrid crawler

Test
------------
There are some unittests, but you can simply start a docker container and test the script in the browser.
```bash
docker-compose build
docker-compose up 
```

Open your browser and connect to `http://localhost/example/1221.html`. You should get redirected to the corresponding file (test-tester(1980-2010)_1221.html).

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
Install the script with composer or checkout the repository. Then you can use it like the following example
```php
use Metagrid\Redirect;
$redirecter = new Redirect(__DIR__);
$redirecter->handleRequest($_SERVER);
```
Configuration
----
You can define some parameters to configure the script.

Define a regex expression for your unique identifier. In this example a number of undefined length
```php
$identifier = "(\d+)";
$redirecter = new Redirect(__DIR__, $identifier);
```
Define a regex expression to identify your resources. You need to use the identifier in this expression. In this example all urls that end with a number and .html
```php
$pattern = "/(\d+).)html$/";
$identifier = "(\d+)";
$redirecter = new Redirect(__DIR__, $identifier, $pattern);

```
Define if the script should do a redirect (302) or print the resource and answer with 200. Redirect is recommended
```php
$redirecter = new Redirect(__DIR__);
// echo the content of the file
$redirecter->setDoRedirect(false);
```
Should the script generate a sitemap for metagrid and other crawlers
```php
$redirecter = new Redirect(__DIR__);
// hide sitemap
$redirecter->setDoSitemap(false);
```
The url of the sitemap. Be aware of conflicts with google sitemaps
```php
$pattern = "/(\d+).)html$/";
$identifier = "(\d+)";
$sitemapUrl = "/sitemap_de.xml$/";
$redirecter = new Redirect(__DIR__, $identifier, $pattern,$sitemapUrl);
```

If the script is placed in a subdirectory of the domain you need to adjust the .htaccess file. If your script is placed in a folder http://example.org/folder/ then you need to change the htaccess like this.
```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # adjust to your folderstructure
    RewriteRule . /test/index.php [L]
</IfModule>
```
