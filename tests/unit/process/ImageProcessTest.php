<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 01.09.19
 * Time: 20:24
 */

use somov\ffmpeg\process\ImageProcess;

class ImageProcessTest extends \Codeception\Test\Unit
{
    /**
     * @throws \somov\common\exceptions\ProcessException
     * @throws \yii\base\InvalidConfigException
     */
    public function testCreateImage()
    {
        $process = Yii::createObject([
            'class' => ImageProcess::class,
            'action' => [
                'createImage' => [
                    'source' => '@ext/files/v600.mp4',
                    'fromTime' => 20,
                    'width' => 20,
                    'height' => 20,
                    'format' => 'image2',
                ]
            ]
        ], [new \somov\ffmpeg\components\Ffmpeg()]);

        /** @var \somov\ffmpeg\process\parser\ConvertEndParser $result */
        $result = \somov\common\components\ProcessRunner::exec($process);
        $this->assertTrue($result->getSuccess());



    }

}
