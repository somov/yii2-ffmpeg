<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 01.09.19
 * Time: 20:19
 */

namespace somov\ffmpeg\process;

use somov\ffmpeg\process\parser\ConvertEndImageParser;

/**
 * Class ImageProcess
 * @package somov\ffmpeg\process
 */
class ImageProcess extends FfmpegBaseProcess
{

    /**
     * @var string
     */
    public $outputParser = ConvertEndImageParser::class;

    /** Параметры генерации изображений
     *  используется ConvertEndImageParser
     * @var array
     */
    protected $params;



    /**
     * @param string $source
     * @param float|integer $start
     * @param integer $width
     * @param integer $height
     * @param string $format
     * @param string $extension
     * @return string
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    protected function actionCreateImage($source, $start = 0, $width = null, $height = null, $format = 'image2', $extension = 'jpg')
    {
        $destination = $this->newTemporaryFile(). '.'.$extension;

        $source = \Yii::getAlias($source);

        $size = $this->normalizeSize($width, $height);

        $params['extension'] = $extension;
        $params['start'] = $start;
        $params['size'] = $size;

        $this->params = $params;

        return $this->normalizeArguments(function () use ($source, $start, $size, $format) {
            $this->addArgument('-ss', $this->secondsToTime($start))
                ->addArgument('-i', $source)
                ->addArgument('-f', $format)
                ->addArgument('-s', $size)
                ->addArgument('-vframes', 1);
        }, $destination);
    }


    /**
     * @param string $source
     * @param integer $count
     * @param integer $width
     * @param integer $height
     * @param int $start
     * @param float $end
     * @param string $format
     * @param string $extension
     * @return string
     */
    protected function actionCreateImagesForPeriod($source, $count, $width = null, $height = null,
                                                   $start = null, $end = null, $format = 'image2', $extension = 'jpg')
    {
        $this->newTemporaryFile(func_get_args());

        $source = \Yii::getAlias($source);

        $params['extension'] = $extension;
        $params['start'] = (float)(isset($start)) ? $start : 1;
        $params['end'] = (float)(isset($end)) ? $end : $this->ffmpeg->getRunningSourceInfo($this)->getDuration();
        $params['period'] = (integer)round(($params['end'] - $params['start']) / $count);
        $params['size'] = $this->normalizeSize($width, $height);

        $this->params = $params;

        return $this->normalizeArguments(function () use ($params, $source, $format, $count) {
            $this->addArgument('-ss', $this->secondsToTime($params['start']))
                ->addArgument('-i', $source)
                ->addArgument('-f', $format)
                ->addArgument('-s', $params['size'])
                ->addArgument('-vf', strtr("\"fps=1,select='not(mod(t,:period))'\"", [':period' => $params['period']]))
                ->addArgument('-vsync', 0)
                ->addArgument('-frame_pts', 1)
                ->addArgument($this->getWorkingDir() . DIRECTORY_SEPARATOR . '_%04d.' . $params['extension']);
        });

    }

    /** Если не указаны размеры
     * определяется из источников
     * @param integer $width
     * @param integer $height
     * @return string
     */
    protected function normalizeSize(&$width, &$height)
    {
        if (!isset($width) || !isset($height)) {
            $box = $this->ffmpeg->getRunningSourceInfo($this)->getBox();
            $width = $box->getWidth();
            $height = $box->getHeight();
        }

        return $width . 'x' . $height;
    }


    /**
     * @param integer $seconds
     * @return false|string
     */
    public function secondsToTime($seconds)
    {
        return gmdate("H:i:s.U", $seconds);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }




}