<?php

namespace CanalTP\JourneyBundle\Tests;

class SeleneseTest extends \PHPUnit_Extensions_SeleniumTestCase
{
    public static $seleneseDirectory;

    public static $browsers = array(
        array(
            'name' => 'Firefox on Linux',
            'browser' =>
                '*firefox C:\Users\usrrec-selcli\AppData\Local\Mozilla Firefox\firefox.exe',
            'host' => '10.2.16.22',
            'port' => 4444
        )
    );

    protected function setUp()
    {
        $this->captureScreenshotOnFailure = false;

        self::$seleneseDirectory = dirname(__FILE__).DIRECTORY_SEPARATOR.'SeleneseBag';
        $baseUrl = getenv('SELENESE_BASEURL');
        if ($baseUrl) {
            $this->setBrowserUrl($baseUrl);
            $this->setSpeed(50);
            $this->setTimeout(200);

            $this->start();
        } else {
            throw new \Exception("Undefined 'SELENESE_BASEURL' env.");
        }
    }

    public function testSeleneses()
    {
        $handle = opendir(self::$seleneseDirectory);
        if ($handle) {
            $this->open('/journey');
            while (false !== ($entry = readdir($handle))) {
                if ('.' != $entry  && '..' != $entry) {
                    $this->runSelenese(self::$seleneseDirectory.DIRECTORY_SEPARATOR.$entry);
                }
            }
            closedir($handle);
        }
    }
}
