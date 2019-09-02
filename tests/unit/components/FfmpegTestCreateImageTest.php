<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 01.09.19
 * Time: 22:01
 */

use somov\ffmpeg\components\Ffmpeg;
use somov\ffmpeg\events\ProgressEvent;

class FfmpegTestCreateImageTest extends Codeception\TestCase\Test
{

    public function testCreateImage()
    {

        $component = new Ffmpeg([
            'on actionBegin' => function ($event) use (&$eventBegin) {
                $eventBegin = $event;
            },
            'on progress' => function ($event) use (&$percent) {
                /** @var ProgressEvent $event */
                $percent = $event->getProgress();
            },
            'on actionEnd' => function ($event) use (&$eventEnd) {
                $eventEnd = $event;
            }
        ]);

        /** @var \somov\ffmpeg\events\ImageEndEvent $r */
        $r = $component->createImage('@ext/files/timer.mp4', 25.500000, 222, 222);

        $this->assertFileExists($r->images[0]->file);

    }


    public function testCreateImagesForPeriod()
    {

        $percent = 0;

        $component = new Ffmpeg([
            'on actionBegin' => function ($event) use (&$eventBegin) {
                $eventBegin = $event;
            },
            'on progress' => function ($event) use (&$percent) {
                /** @var ProgressEvent $event */
                $percent = $event->getProgress();
            },
            'on actionEnd' => function ($event) use (&$eventEnd) {
                $eventEnd = $event;
            }
        ]);

        /** @var \somov\ffmpeg\events\ImageEndEvent $r */
        $r = $component->createImagesForPeriod('@ext/files/timer2.mp4', 10, null, null);

        $this->assertGreaterThanOrEqual(99, $percent);

        $this->assertFileExists($r->images[0]->file);
        $this->assertCount(10, $r->images);

    }

}
