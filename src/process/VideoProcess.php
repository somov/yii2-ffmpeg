<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 31.08.19
 * Time: 19:55
 */

namespace somov\ffmpeg\process;


use somov\ffmpeg\process\parser\VideoInfoParser;
use yii\base\InvalidValueException;
use yii\helpers\ArrayHelper;

/**
 * Class ConcatVideosProcess
 * @package somov\ffmpeg\process\parser
 */
class VideoProcess extends FfmpegBaseProcess
{

    /**
     * @param array $files
     * @param $format
     * @param $destination
     * @param array $convertArguments
     * @param array $concatArguments
     * @return string
     */
    protected function actionConcat(array $files, $format, $destination, $convertArguments = [], $concatArguments = [])
    {

        if (count($files) < 1) {
            throw new InvalidValueException('Empty files list ');
        }

        if (!$this->ffmpeg->getVersion()->formatExists($format)) {
            throw new InvalidValueException('Unknown format ' . $format);
        }

        /** @var VideoInfoParser[] $list */
        $list = [];

        foreach ($files as $file) {
            $list[] = $this->ffmpeg->getVideoInfo($file);
        }

        foreach ($list as $index => $info) {
            if ($info->getFormatName('true') <> $format) {
                $fileName = $info->getFileName();
                $dst = $fileName . '_.' . $format;

                $end =  $this->ffmpeg->convert($fileName, $dst, $format, $convertArguments);
                if (!$end->result->success) {
                    throw new \RuntimeException("Error convert $fileName to format $format " . $end->result->getEndMessage());
                }
                $list[$index] = $end->destination;
            }
        }

        $sourceFile = $this->newTemporaryFile();

        file_put_contents($sourceFile, implode(PHP_EOL, array_map(function ($d) {
            return 'file ' . $d;
        }, ArrayHelper::getColumn($list, 'fileName'))));


        if ($info = $this->ffmpeg->getRunningSourceInfo($this)) {
            $info->setDuration(array_sum(ArrayHelper::getColumn($list, 'duration')));
        }

        return $this->normalizeArguments(function () use ($sourceFile) {
            $this->addArgument('-f', 'concat')
                ->addArgument('-safe', 0)
                ->addArgument('-err_detect', 'ignore_err')
                ->addArgument('-i', $sourceFile);
        }, \Yii::getAlias($destination), $concatArguments);

    }


    /** Convert video to specific format
     * @param string $source
     * @param string $destination
     * @param string $format
     * @param null $addArguments addition command arguments
     * @return string
     * @internal param string $codec
     */
    protected function actionConvert($source, $destination, $format, $addArguments = null)
    {

        if (!isset($format)) {
            $i = pathinfo($destination);
            $format = $i['extension'];
        }

        if (!$this->ffmpeg->getVersion()->formatExists($format)) {
            throw new InvalidValueException('Unknown format ' . $format);
        }


        $source = \Yii::getAlias($source);

        return $this->normalizeArguments(function () use ($source, $format) {
            $this->addArgument('-i', $source)
                ->addArgument('-f', $format);
        }, \Yii::getAlias($destination), $addArguments);

    }

    /**
     * @param $source
     * @return string
     */
    protected function actionDecodeStreamDuration($source)
    {
        return $this->actionConvert($source, '-', 'null', ['-tune' => 'fastdecod']);
    }

}