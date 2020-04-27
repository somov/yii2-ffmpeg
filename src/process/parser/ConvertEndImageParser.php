<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 27.04.20
 * Time: 14:56
 */

namespace somov\ffmpeg\process\parser;

use somov\ffmpeg\events\ImageEndEvent;
use somov\ffmpeg\process\FfmpegBaseProcess;
use somov\ffmpeg\process\ImageProcess;
use yii\helpers\FileHelper;

/**
 * Class ConvertEndImageParser
 * @package somov\ffmpeg\process\parser
 */
class ConvertEndImageParser extends ConvertEndParser
{
    /**
     * @param $options
     * @param FfmpegBaseProcess|ImageProcess $process
     * @return \somov\ffmpeg\events\ImageEndEvent
     */
    public function createEvent($options, FfmpegBaseProcess $process)
    {

        $event = new ImageEndEvent($options);
        $event->images = &$process->getImages();

        $dir = $process->getWorkingDir();
        $params = $process->getParams();

        if (isset($dir)) {
            $index = 0;
            $time = (integer)$params['start'];
            foreach (FileHelper::findFiles($dir, ['only' => [
                '*.' . $params['extension']
            ]]) as $file) {
                $index++;
                $process->addImage($index, $file, $params['size'], $time);
                $time += $params['period'];
            }
        }

        return $event;
    }
}