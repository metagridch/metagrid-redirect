<?php
namespace Metagrid;

class Redirect
{
    /**
     * preg match pattern for the identifier
     * @param string $identifier
     */
    private $identifier = "";

    /**
     * preg match pattern for the url
     * @param string $pattern
     */
    private $pattern = "";

    /**
     * Flag if the request should be redirected or directly rendered
     * @param boolean $doRedirect
     */
    private $doRedirect = true;

    /**
     * Flag if we should generate a XML Sitemap
     * @param boolean $doSitemap
     * @see $sitemapUrl
     */
    private $doSitemap = true;

    /**
     * url for the sitemap. Generate on the fly.
     * @param string $pattern
     */
    private $sitemapUrl = "";

    /**
     * Baseurl
     * @var string
     */
    private $baseurl = '';

    /**
     * Path to search for files
     * @var string
     */
    private $path = '';
    /**
     * Redirect constructor.
     * @param string $path
     * @param string $identifier A regex pattern for the unique identifier
     * @param string $pattern A regex pattern for the resource url
     * @param string $sitemapUrl A regex pattern for the sitemap url
     */
    function __construct(string $path, string $identifier = "(\d+)", string $pattern = "/(\d+).html$/", string $sitemapUrl = "/sitemap.xml$/") {
        $this->path = $path;
        $this->identifier = $identifier;
        $this->pattern = $pattern;
        $this->sitemapUrl = $sitemapUrl;
    }

    /**
     * Switch to allow disallow the sitemap
     * @param bool $sitemap
     */
    public function setDoSitemap(bool $sitemap) {
        $this->doSitemap = $sitemap;
    }
    /**
     * Switch to allow disallow redirect (instead of direct print file content)
     * @param bool $redirect
     */
    public function setDoRedirect(bool $redirect) {
        $this->doRedirect = $redirect;
    }

    /**
     * Handle the request
     * @param $server
     */
    public function handleRequest(array $server)
    {
        // read full url
        $url = parse_url('http' . (isset($server['HTTPS']) ? 's' : '') . '://' . "{$server['HTTP_HOST']}{$server['REQUEST_URI']}");
        // generate base url
        $this->baseurl = $url['scheme'] . "://" . $url['host'] . substr($url['path'], 0, strrpos($url['path'], '/'));
        if (preg_match($this->pattern, $url['path'], $matches)) {
            $this->redirect($matches[1]);
        } elseif (preg_match($this->sitemapUrl, $url['path'], $matches) && $this->doSitemap) {
            $this->sitemap();
        }else {
            $this->notFound();
        }
    }

    /**
     * Send not found response
     */
    private function notFound() {
        // return 404 not found
        header("HTTP/1.1 404 Not Found", true, 404);
    }
    /**
     * Read the directory
     * @return array
     */
    protected function getFiles() {
        // open current directory
        $fp = opendir($this->path);
        $files = [];
        while($file = readdir($fp)) {
            if(is_file($this->path."/".$file)){
                $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * Generate a xml-sitemap
     */
    public function sitemap() {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8"?>
               <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        foreach($this->getFiles() as $file) {
            if(preg_match($this->pattern,$file, $match)) {
                echo "<url>";
                echo "<loc>" . $this->baseurl . "/" . $match[1] .".html" . "</loc>";
                echo "<lastmod>" . @date("Y-m-d",filemtime($file)) . "</lastmod>";
                echo "<changefreq>monthly</changefreq>";
                echo "<priority>0.8</priority>";
                echo "</url>";
            }
        }
        echo "</urlset>";
    }

    /**
     * Redirect to or echo the content of a static page
     * Redirect constructor.
     * @param $urlId
     */
    public function redirect($urlId)  {
        foreach($this->getFiles() as $file) {
            $knewPattern = str_replace($this->identifier, $urlId, $this->pattern);
            // get id of the file and check if its the same in the url
            if (preg_match($this->pattern, $file, $fileId)) {
                if (preg_match($knewPattern, $file) && ($urlId == $fileId[1])) {
                    if ($this->doRedirect) {
                        header("HTTP/1.1 302 Moved Temporarily");
                        header("Location: $this->baseurl/$file");
                    } else {
                        // return 200 and the html code. Don't do redirect
                        echo $this->getContent($file);
                    }
                }
            }
        }
    }

    /**
     * Get the content of a file
     * @param $file
     * @return false|int
     */
    protected function getContent($file) {
        return readfile($file);
    }
}
