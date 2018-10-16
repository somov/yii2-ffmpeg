<?php
/**
 *
 * User: develop
 * Date: 05.07.2018
 */

namespace somov\ffmpeg\process;


use somov\common\process\BaseProcess;
use somov\common\process\StringBuffered;
use somov\ffmpeg\process\parser\ConvertEndParser;

class FfmpegProcess extends BaseProcess
{

    use StringBuffered;

    public $action = 'convert';


    public $outputParser = ConvertEndParser::class;

    /**
     * @var callable
     */
    public $bufferReaderCallback;

    public function init()
    {
        $this->setBufferSize(1024);
        parent::init();
    }


    /** Необходимые аргументы процесса ffmpeg
     * @param callable $configCall функуия кофнигрутор аргументов действия
     * @param $destination
     * @param array $addArguments
     * @return string
     */
    protected function normalizeArguments(callable $configCall, $destination, $addArguments = null)
    {

        $this->addArgument('-progress', '/dev/stdout ')
            ->addArgument('-nostdin');

        call_user_func($configCall);

        $this->addArgument('-y');

        if (isset($addArguments)) {
            $this->addArgument($addArguments);
        }

        $this->addArgument($destination)
            //all in stdout
            ->addArgument('2>&1');

        return $this->prepareCommand();

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

    /**
     * @param $listFile
     * @param $destination
     * @param $addArguments
     * @return string
     */
    protected function actionConcat($listFile, $destination, $addArguments)
    {
        return $this->normalizeArguments(function () use ($listFile) {
            $this->addArgument('-f', 'concat')
                ->addArgument('-i', $listFile);
        }, \Yii::getAlias($destination), $addArguments);
    }

    /**
     * Called from [StringBuffered] trait;
     * @return bool
     */
    protected function beforeFlushBuffer()
    {
        if (isset($this->bufferReaderCallback) && is_callable($this->bufferReaderCallback)) {
            return call_user_func_array($this->bufferReaderCallback, [
                'buffer' => $this->_buffer,
                'process' => $this
            ]);
        }
        return true;
    }

}