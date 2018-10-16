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


class FfmpegConcatTest extends Test
{

    public function test()
    {

        $progress = 0;

        $duration  = 0;

        $component = new Ffmpeg([
            'decodeStreamDuration' => false,
            'on actionBegin' => function ($event) use (&$duration) {
                /** @var VideoInfoEvent $event */
                $id = $event->process->getActionId();

                if ($id === 'concat') {
                    $duration = $event->info->getDuration();
                }

            },
            'on progress' => function ($event) use (&$progress) {
                /** @var ProgressEvent $event */
                $id = $event->process->getActionId();
                if ($id === 'concat') {
                    $progress = $event->getProgress();
                }
            }
        ]);

        /**
         * @var ConvertEndParser $convertEnd
         */
        list($convertEnd, $s, $d) = $component->concat([
            '@ext/files/concat/0.webm',
            '@ext/files/concat/1.webm',
            '@ext/files/concat/2.webm',
        ], 'mp4', '@ext/_output/test_concat.mp4', ['-c:v' => 'copy'], ['-c:v' => 'copy', '-movflags' => 'faststart']);

        $this->assertSame($duration % 100, $convertEnd ->getEndDuration() % 100);

        $this->assertGreaterThanOrEqual(95, $progress);

        $this->assertFileExists($d->getFileName());


    }
}


