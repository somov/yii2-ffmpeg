<?php
/**
 * Created by PhpStorm.
 * User: develop
 * Date: 04.07.2018
 * Time: 0:19
 */

namespace mtest\command;

use Codeception\TestCase\Test;
use Imagine\Image\Box;
use somov\common\components\ProcessRunner;
use somov\ffmpeg\process\FfprobeProcess;
use somov\ffmpeg\process\parser\VideoInfoParser;


class FProbeTest extends Test
{
    public function getCommands()
    {
        return [
            [
                'first' => ProcessRunner::exec([
                    'class' => FfprobeProcess::class,
                    'source' => '@ext/files/v600.mp4'
                ])
            ]
        ];
    }

    /**
     * @dataProvider getCommands
     * @param VideoInfoParser $data
     */
    public function testRun($data)
    {
        $this->assertNotEmpty($data);

        $box= $data->getBox();

        $this->assertInstanceOf(Box::class, $box);
        $this->assertSame(640, $box->getWidth());
        $this->assertSame(360, $box->getHeight());
        $this->assertSame( 599490.0, $data->getBitRate());
        $this->assertSame( 294.706667, $data->getDuration());
        $this->assertSame(26883462, $data->getFileSize());

    }


}


