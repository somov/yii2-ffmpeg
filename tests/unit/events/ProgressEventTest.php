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
        $event = new ProgressEvent([
            'size' =>'692',

            'bitrate' => ' 559.2',
            'state' => 'c',
            'speed' => 1,
            'time_ms' => 190.13 * 1000000
        ]);
        $event->info = (new Ffmpeg())->getVideoInfo('@ext/files/v600.mp4');



        $this->assertEquals(692, $event->getSize());
        $this->assertEquals(65, $event->getProgress());

    }


}


