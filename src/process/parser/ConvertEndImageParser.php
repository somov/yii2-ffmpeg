<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 27.04.20
 * Time: 14:56
 */

namespace somov\ffmpeg\process\parser;

use somov\common\helpers\ArrayHelper;
use somov\common\helpers\StringHelper;
use somov\common\process\BaseProcess;
use somov\ffmpeg\components\ImageFile;
use somov\ffmpeg\events\ImageEndEvent;
use somov\ffmpeg\process\ImageProcess;
use yii\helpers\FileHelper;

/**
 * Class ConvertEndImageParser
 * @package somov\ffmpeg\process\parser
 */
class ConvertEndImageParser extends ConvertEndParser
{
    /**
     * @var ImageFile[]
     */
    protected $images = [];

    /**
     * @var string
     */
    public $event = ImageEndEvent::class;

    /**
     * @param integer $index
     * @param string $file
     * @param string $size
     * @param int $time
     */
    protected function addImage($index, $file, $size, $time)
    {
        $this->images[] = \Yii::createObject(array_merge(
            ['class'=>ImageFile::class],
            compact('index', 'file', 'size', 'time')),[$this]
        );
    }

    /**
     * @param mixed $data
     * @param BaseProcess|ImageProcess $process
     * @return ConvertEndParser
     */
    public function parse($data, BaseProcess $process)
    {
        parent::parse($data, $process);
        $this->findImages($process);
        return $this;
    }

    /**
     * @param ImageProcess $process
     */
    protected function findImages(ImageProcess $process){

        $dir = $process->getWorkingDir();
        $params = $process->getParams();

        if (isset($dir) && is_dir($dir)) {
            $index = 0;
            $start =  (integer) ArrayHelper::getValue($params, 'start', 0);
            $files = FileHelper::findFiles($dir, ['only' => ['*.' . $params['extension']]]);
            sort($files);
            foreach ($files as $file) {
                preg_match('/[\d]+/', StringHelper::basename($file), $m);
                $time = ((int) ltrim($m[0])) + $start;
                $index++;
                $this->addImage($index, $file, $params['size'], $time);
            }
        }
    }

    /**
     * @return ImageFile[]
     */
    public function getImages()
    {
        return $this->images;
    }


}