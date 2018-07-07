<?php
/**
 * Created by PhpStorm.
 * User: develop
 * Date: 04.07.2018
 * Time: 0:19
 */

namespace mtest;

use Codeception\TestCase\Test;
use somov\ffmpeg\components\Ffmpeg;
use somov\ffmpeg\events\ProgressEvent;


class ProgressEventTest extends Test
{

    public function testEvent()
    {
        $event = new ProgressEvent();
        $event->info = (new Ffmpeg())->getVideoInfo('@ext/files/v600.mp4');

        $event->setRaw([
                'size' => '     692',
                'time' => '00:03:10.13',
                'frame' => '  299',
                'bitrate' => ' 559.2',
                'fps' => '0',
            ]
        );

        $this->assertEquals(692, $event->getSize());
        $this->assertEquals(65, $event->getProgress());

    }


}


