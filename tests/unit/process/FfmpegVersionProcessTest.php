<?php
/**
 * Created by PhpStorm.
 * User: develop
 * Date: 04.07.2018
 * Time: 0:19
 */

namespace mtest\command;

use Codeception\TestCase\Test;
use somov\common\components\ProcessRunner;
use somov\ffmpeg\process\FfmpegVersionProcess;


class FfmpegVersionProcessTest extends Test
{
    /**
     * @var FfmpegVersionProcess
     */
    private static $version;

    public static function setUpBeforeClass()
    {
        self::$version = ProcessRunner::exec([
            'class' => FfmpegVersionProcess::class,
            'isGetCodecs' => (bool) rand(0,1),
            'isGetFormats' => (bool) rand(0,1)
        ]);

        parent::setUpBeforeClass();
    }


    public function testVersion()
    {
        $version = self::$version->getVersion();

        $this->assertContains('ffmpeg version', $version);

    }

    public function testFormat()
    {
        $formats = self::$version->getFormatsArray();
        if (self::$version->isGetFormats) {
            $this->assertTrue(count($formats) > 0);
            $this->assertTrue(self::$version->formatExists('mp4'));
        } else {
            $this->assertEmpty($formats);
            $this->assertFalse(self::$version->formatExists('mp4'));
        }
    }

    public function testCodec()
    {
        $codecs = self::$version->getCodecsArray();
        if (self::$version->isGetCodecs) {
            $this->assertTrue(count($codecs) > 0);
        }

        $this->assertFalse(self::$version->codecExists('nonexists'));
    }

    public function testConfigItem()
    {

        $item = self::$version->getConfigItem('h264');
        if (isset($item)) {
            $this->assertTrue($item->getDecodingSupported());
            $this->assertTrue($item->getEncodingSupported());
            $this->assertTrue($item->getVideoCodec());
            $this->assertFalse($item->getAudioCodec());
            $this->assertFalse($item->getSubTitleCodec());
            $this->assertTrue($item->getLossyCompression());
            $this->assertTrue($item->getLossLessCompression());
        }

    }


}