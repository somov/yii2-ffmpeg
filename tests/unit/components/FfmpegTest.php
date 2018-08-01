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


class FfmpegTest extends Test
{

    public function testFormatNotFound()
    {
        $this->expectExceptionMessage('Unknown format nfound');
        $component = new Ffmpeg();
        $component->convert('@ext/files/v600.mp4', '@ext/_output/1.nf', 'nfound');
    }

    public function testConvert()
    {
        $percent = 0;

        /** @var VideoInfoEvent $eventBegin */
        $eventBegin = null;
        /** @var EndEvent $eventEnd */
        $eventEnd = null;

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

        /**
         * @var ConvertEndParser $convertEnd
         * @var VideoInfoParser $s
         * @var VideoInfoParser $d
         */
        list($convertEnd, $s, $d) = $component->convert(
            '@ext/files/v600.mp4', '@ext/_output/3.avi', 'avi'
        );

        $this->assertSame($convertEnd, $eventEnd->result);

        $this->assertEquals($s->getFormatName(), $eventBegin->info->getFormatName());
        $this->assertSame($eventBegin->info, $s);

        $this->assertLessThanOrEqual(100, $percent);
        $this->assertGreaterThanOrEqual(90, $percent);

        $this->assertFileExists($d->getFileName());

    }

    public function testVideoInfo(){

        /** @var VideoInfoParser $info */
        $info = (new Ffmpeg())->getVideoInfo('https://cdn.theguardian.tv/mainwebsite/2015/07/20/150716YesMen_desk.mp4');
        $this->assertNotFalse($info->getVideoStream());
    }

}


