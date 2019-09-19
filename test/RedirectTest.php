<?php
namespace Metagrid;

/**
 * Override global header function in the metarid namespace
 * @param $string
 */
function header($string){
    HeaderCollector::$headers[] = $string;
}

/**
 * Class HeaderCollector
 * Using this in combination with function header override
 * for the namespace My\Application\Namespace
 * we can make assertions on headers sent
 */
class HeaderCollector {

    public static $headers = [];

    //call this in your test class setUp so headers array is clean before each test
    public static function clean() {
        self::$headers = [];
    }
}

namespace Metagrid\Test;

use Metagrid\HeaderCollector;
use Metagrid\Redirect;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

/**
 * Override the getFiles function from the redirect
 * Class MockedRedirect
 * @package Metagrid\Test
 */
class MockedRedirect extends Redirect {
    /**
     * @var null| vfsStream
     */
    private $root = null;

    /**
     * Get an array of files from vfs
     * @return array
     */
    protected function getFiles() {
        $structure = [
            'edde12.html' => "test1",
            'test-tester(1980-2010)_1221.html' => "test2",
        ];
        /** @var vfsStream root */
        $this->root = vfsStream::setup('root',777, $structure);
        $handle = opendir($this->root->url());

        $files = [];
        while ($a = readdir($handle)){
                $files[] = $a;
        }
        return $files;
    }

    /**
     * Read file from vfs
     * @param $file
     * @return false|int|string
     */
    protected function getContent($file) {
        return file_get_contents($this->root->url('root')."/".$file);
    }
}

class RedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        HeaderCollector::clean();
    }

    /**
     * @test
     **/
    public function testRedirect(): void
    {
        $redirecter = new MockedRedirect();
        $serverMock = ['HTTPS' => true, 'HTTP_HOST' => 'example.com', 'REQUEST_URI' => '/1221.html', 'SERVER_PROTOCOL' => 'https'];
        $redirecter->handleRequest($serverMock);
        $this->assertContains(
            'Location: https://example.com/test-tester(1980-2010)_1221.html', HeaderCollector::$headers
        );
    }

    /**
     * @test
     **/
    public function testSitemap(): void
    {
        $redirecter = new MockedRedirect();
        $serverMock = ['HTTPS' => true, 'HTTP_HOST' => 'example.com', 'REQUEST_URI' => '/sitemap.xml', 'SERVER_PROTOCOL' => 'https'];
        ob_start();
        $redirecter->handleRequest($serverMock);
        $data = ob_get_contents();
        ob_end_clean();
        $this->assertSame(
            '<?xml version="1.0" encoding="utf-8"?>
               <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"><url><loc>https://example.com/12.html</loc><lastmod>1970-01-01</lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url><url><loc>https://example.com/1221.html</loc><lastmod>1970-01-01</lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url></urlset>', $data );
    }

    /**
     * @test
     **/
    public function testEchoContent(): void
    {
        $redirecter = new MockedRedirect();
        $redirecter->setDoRedirect(false);
        $serverMock = ['HTTPS' => true, 'HTTP_HOST' => 'example.com', 'REQUEST_URI' => '/1221.html', 'SERVER_PROTOCOL' => 'https'];
        ob_start();
        $redirecter->handleRequest($serverMock);
        $data = ob_get_contents();
        ob_end_clean();
        $this->assertSame('test2', $data);
    }
}



