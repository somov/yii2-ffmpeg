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

    public function convertFiles()
    {
        return [
            'mp4-avi' => ['source' => '@ext/files/t.mp4', 'format' => 'avi'],
            'mp4-webm' => [
                'source' => '@ext/files/t.mp4',
                'format' => 'webm',
                [
                    '-ss' => '00::11'
                ],
                'Invalid duration specification'
            ],
            'avi-flv' => ['source' => '@ext/files/t.avi', 'format' => 'flv'],
            'flv-avi' => ['source' => '@ext/files/t.flv', 'format' => 'avi'],
            //'avi-mp4 big' => ['source' => '@ext/files/big.avi', 'format' => 'mp4']
            'avi-mp4' => ['source' => '@ext/files/t.avi', 'format' => 'mp4']
        ];
    }


    /** @dataProvider convertFiles
     * @param $source
     * @param $format
     * @param array $arguments
     * @param null $exceptionMessage
     */
    public function testConvert($source, $format = null, $arguments = [], $exceptionMessage = null)
    {
        $percent = 0;


        if (isset($exceptionMessage)) {
            $this->expectExceptionMessage($exceptionMessage);
        }

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
            $source, '@ext/_output/test.' . $format, $format, $arguments
        );

        $this->assertSame($convertEnd, $eventEnd->result);

        $this->assertEquals($s->getFormatName(), $eventBegin->info->getFormatName());
        $this->assertSame($eventBegin->info, $s);

        $this->assertGreaterThanOrEqual(95, $percent);

        $this->assertFileExists($d->getFileName());

    }

    public function testVideoInfo()
    {

        /** @var VideoInfoParser $info */
        $info = (new Ffmpeg())->getVideoInfo('https://cdn.theguardian.tv/mainwebsite/2015/07/20/150716YesMen_desk.mp4');
        $this->assertNotFalse($info->getVideoStream());
    }

}


