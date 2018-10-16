<?php
/**
 * Created by PhpStorm.
 * User: develop
 * Date: 04.07.2018
 * Time: 0:19
 */

namespace mtest\components;

use Codeception\TestCase\Test;
use somov\ffmpeg\components\Ffmpeg;
use somov\ffmpeg\events\EndEvent;
use somov\ffmpeg\events\ProgressEvent;
use somov\ffmpeg\events\VideoInfoEvent;
use somov\ffmpeg\process\parser\ConvertEndParser;
use somov\ffmpeg\process\parser\VideoInfoParser;


class FfmpegSourceInfoTest extends Test
{

    public function testEvent()
    {

        if (isset($exceptionMessage)) {
            $this->expectExceptionMessage($exceptionMessage);
        }

        $component = new Ffmpeg([
            'decodeStreamDuration' => false,
            'on actionBegin' => function ($event)  {
                /** @var ProgressEvent $event */
                $this->assertNotEmpty($event->info->getDuration());
            },
            'on getSourceInfo' => function ($event) {
                /** @var ProgressEvent $event */
                $event->info->setDuration(13);
            }
        ]);

        $component->convert('@ext/files/webm_h264.webm', '@ext/_output/testsf.mp4', 'mp4', [
            '-c:v' => 'copy'
        ]);

    }
}


