<?php
/**
 *
 * User: develop
 * Date: 05.07.2018
 */

namespace somov\ffmpeg\process;


use somov\common\interfaces\ParserInterface;
use somov\common\process\BaseProcess;
use somov\common\process\StringBuffered;
use somov\ffmpeg\components\Ffmpeg;
use somov\ffmpeg\process\parser\ConvertEndParser;

/**
 * Class FfmpegBaseProcess
 * @package somov\ffmpeg\process
 *
 * @property-read Ffmpeg $ffmpeg
 */
abstract class FfmpegBaseProcess extends BaseProcess
{

    use StringBuffered;

    /**
     * @var string
     */

    /**
     * @var string
     */
    public $command = 'ffmpeg';

    /**
     * @var Ffmpeg
     */
    private $_ffmpeg;

    /**
     * @var string|ParserInterface
     */
    public $outputParser = ConvertEndParser::class;

    /**
     * @var callable
     */
    public $bufferReaderCallback;

    /**
     * FfmpegBaseProcess constructor.
     * @param $ffmpeg
     * @param array $config
     */
    public function __construct($ffmpeg, array $config = [])
    {
        $this->_ffmpeg = $ffmpeg;
        parent::__construct($config);
    }

    /**
     * @return Ffmpeg
     */
    public function getFfmpeg()
    {
        return $this->_ffmpeg;
    }

    /**
     * @inheritdoc
     */
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