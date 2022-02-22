<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 27.04.20
 * Time: 13:30
 */

namespace somov\ffmpeg\process;

use somov\common\helpers\ArrayHelper;
use somov\ffmpeg\events\EndEventMultiple;
use somov\ffmpeg\process\parser\ConvertEndParser;

/**
 * Class MultipleVideoDestinationProcess
 * @package somov\ffmpeg\process
 */
class MultipleProcess extends FfmpegBaseProcess
{
    /**
     * @var array
     */
    public $outputParser = [
        'class' => ConvertEndParser::class,
        'event' => EndEventMultiple::class
    ];


    /**
     * @var array|null
     */
    public $source = [];

    /**
     * @var array|null
     */
    public $destination = [];


    /**
     * @param array|string $source
     * @param array|string $destinations
     * @param array|null $addArguments
     * @return string
     */
    protected function actionConvertMultiple($source, array $destinations, array $addArguments = null)
    {
        return $this->normalizeArguments(function () use ($source, $destinations, $addArguments) {

            $this->processArgumentsDirection((array)$source, 'source');

            if (is_array($addArguments)) {
                $this->addArgument('\\' . PHP_EOL);
                $this->addArgument($addArguments);
            }

            $this->processArgumentsDirection((array)$destinations, 'destination');

        });
    }

    /**
     * @param array $arguments
     * @param string $direction
     */
    protected function processArgumentsDirection(array $arguments, $direction)
    {
        foreach ($arguments as $key => $item) {

            $this->addArgument('\\' . PHP_EOL);

            if (!is_numeric($key) && is_array($item)) {
                $this->addFile($key, $direction, $item);
            } else if (is_array($item)) {
                $file = ArrayHelper::remove($item, $direction);
                $this->addFile($file, $direction, $item);
            } else {
                $this->addFile($item, $direction);
            }
        }
    }


    /**
     * @param $file
     * @param string $direction
     * @param null $argumentValue
     */
    private function addFile($file, $direction = 'destination', $argumentValue = null)
    {
        $file = \Yii::getAlias($file);

        if (isset($argumentValue)) {
            $this->addArgument($argumentValue);
        }

        if ($direction === 'source') {
            $this->addArgument(['-i' => $file]);
        } else {
            $this->addArgument($file);
        }

        array_push($this->{$direction}, $file);

    }


}