<?php
/**
 * Created by PhpStorm.
 * User: nsn
 */

namespace mtest\components;

use Codeception\TestCase\Test;
use somov\ffmpeg\components\Ffmpeg;
use somov\ffmpeg\events\EndEvent;
use somov\ffmpeg\events\EndEventMultiple;
use somov\ffmpeg\events\ProgressEvent;
use somov\ffmpeg\events\VideoInfoEvent;


class FfmpegMultipleDestinationTest extends Test
{
    /**
     * @return array
     */
    public function convertFiles()
    {
        return [
/*
            'concat' => [
                [
                    '@ext/files/sea.mp4',
                    [
                        'source' => '@ext/files/sea2.mp4',
                        '-f' => 'mp4'
                    ]
                ],
                [
                    '@ext/_output/m_concat_120.mp4' => ['-map'=> '"[outv]"', ' -map'=>'"[outa]"', '-s'=>'120x64'],
                ],
                [
                    '-filter_complex' => '"[0:v:0][0:a:0][1:v:0][1:a:0]concat=n=2:v=1:a=1[outv][outa]"'
                ]
            ],*/

            'convert-size' => [
                [
                        '@ext/files/sea.mp4' => ['-f' => 'mp4']
                ],
                [
                    [
                        'destination' => '@ext/_output/mMp4_120.mp4',
                        '-s'=>'120x80'
                    ],
                    '@ext/_output/mMp4_240.mp4' => ['-s'=>'240x160'],
                ]
            ],

        ];
    }


    /** @dataProvider convertFiles
     * @param string|array $source
     * @param string|array $destination
     * @param array $arguments
     */
    public function testConvert($source, $destination, $arguments = null)
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

                \Yii::info(basename($event->info->getFileName()) . " $percent % "
                    . $event->processingTime() . '/' . $event->processingTimeEnd(), 'somov\progress');
            },
            'on actionEnd' => function ($event) use (&$eventEnd) {
                $eventEnd = $event;
            }
        ]);

        /**
         * @var EndEventMultiple $end
         */
        $end = $component->convertMultiple($source, $destination, $arguments);
        $this->assertGreaterThanOrEqual(95, $percent);
        $this->assertInstanceOf(EndEventMultiple::class, $end);
        $this->assertCount(count($source),  $end->source);
        $this->assertCount(count($destination),  $end->destination);

    }


}


