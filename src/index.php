<?php
/**
 * Created by PhpStorm.
 * User: tmen
 * Date: 13.06.16
 * Time: 10:28
 */
/**
 * preg match pattern for the identifier
 * @param string $identifier
 */
$identifier = "(\d+)";

/**
 * preg match pattern for the url
 * @param string $pattern
 */
$pattern = "/".$identifier.".html$/";

/**
 * Flag if the request should be redirected or directly rendered
 * @param boolean $doRedirect
 */
$doRedirect = true;

/**
 * Generate a XML Sitemap
 * @param boolean $doSitemap
 * @see $sitemapUrl
 */
$doSitemap = true;

/**
 * url for the sitemap. Generate on the fly.
 * @param string $pattern
 */
$sitemapUrl = "/sitemap.xml$/";


// read full url
$url = parse_url('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
// generate base url
$baseurl = $url['scheme']."://".$url['host'].substr( $url['path'], 0,  strrpos( $url['path'], '/'));
// open current directory
$fp = opendir("./");
// try to find html site
if(preg_match($pattern,$url['path'],$matches)){
    $urlId = $matches[1];
    while($file = readdir($fp)){
        $knewPattern = str_replace($identifier, $urlId, $pattern);
        // get id of the file and check if its the same in the url
        if(preg_match($pattern, $file, $fileId)) {
            if (is_file($file) && preg_match($knewPattern, $file) && ($urlId == $fileId[1])) {
                if ($doRedirect) {
                    header("HTTP/1.1 302 Moved Temporarily");
                    header("Location:$baseurl/$file");
                } else {
                    // return 200 and the html code. Don't do redirect
                    echo readfile($file);
                }
                exit;
            }
        }
    }
}
// generate a XML-sitemap
elseif (preg_match($sitemapUrl,$url['path'],$matches) && $doSitemap){
    header("Content-type: text/xml");
    echo '<?xml version="1.0" encoding="utf-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    while($file = readdir($fp)) {
        if(preg_match($pattern,$file, $match)) {
            echo "<url>";
            echo "<loc>" . $baseurl . "/" . $match[1] .".html" . "</loc>";
            echo "<lastmod>" . @date("Y-m-d",filemtime($file)) . "</lastmod>";
            echo "<changefreq>monthly</changefreq>";
            echo "<priority>0.8</priority>";
            echo "</url>";
        }
    }
    echo "</urlset>";
    exit;
}
// return 404 not found
header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);