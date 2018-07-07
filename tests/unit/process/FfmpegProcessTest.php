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
use somov\ffmpeg\process\FfmpegProcess;
use somov\ffmpeg\process\parser\ConvertEndParser;


class FfmpegProcessTest extends Test
{

    public function testVideoConvertAvi()
    {
        /** @var ConvertEndParser $data */
        $data = ProcessRunner::exec([
            'class' => FfmpegProcess::class,
            'action' => [
                'convert' => [
                    'source' => '@ext/files/v600.mp4',
                    'destination' => '@ext/_output/1.avi',
                    'format' => 'avi'
                ]
            ]
        ]);
        $this->assertTrue($data->success);
    }
}


