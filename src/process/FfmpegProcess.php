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

    /**
     * @var callable
     */
    public $bufferReaderCallback;

    public function init()
    {
        $this->setBufferSize(1024);
        parent::init();
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
        $destination = \Yii::getAlias($destination);

        $this->addArgument('-progress', '/dev/stdout ')
            ->addArgument('-nostdin')
            ->addArgument('-i', $source)
            ->addArgument('-f', $format)
            ->addArgument('-y');

        if (isset($addArguments)) {
            $this->addArgument($addArguments);
        }

        $this->addArgument($destination)
            //all in stdout
            ->addArgument('2>&1');

        $this->outputParser = ConvertEndParser::class;

        return $this->prepareCommand();
    }

    protected function actionDecodeStreamDuration($source)
    {
        return $this->actionConvert($source, '-', 'null', ['-tune' => 'fastdecod']);
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