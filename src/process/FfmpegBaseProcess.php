<?php
/**
 *
 * User: develop
 * Date: 05.07.2018
 */

namespace somov\ffmpeg\process;


use somov\common\helpers\FileHelper;
use somov\common\interfaces\ParserInterface;
use somov\common\process\BaseProcess;
use somov\common\process\StringBuffered;
use somov\ffmpeg\components\Ffmpeg;
use somov\ffmpeg\process\parser\ConvertEndParser;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/**
 * Class FfmpegBaseProcess
 * @package somov\ffmpeg\process
 *
 * @property-read Ffmpeg $ffmpeg
 *

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
     * @var string
     */
    public $workingDirAlias = '@runtime/tmp/ffmpeg';

    /**
     * @var string
     */
    private $_workingDir;

    /**
     * @var integer
     */
    private $_eTime;

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
     *
     */
    public function __destruct()
    {
        if ($dir = $this->getWorkingDir()) {
            try {
                FileHelper::removeDirectory($dir);
            } catch (\Exception $exception) {
            }
        }
    }

    /**
     * @return Ffmpeg
     */
    public function getFfmpeg()
    {
        return $this->_ffmpeg;
    }


    /** Необходимые аргументы процесса ffmpeg
     * @param callable $configCall функция настройки аргументов действия
     * @param string|null $destination
     * @param array $addArguments
     * @return string
     */
    protected function normalizeArguments(callable $configCall, $destination = null, $addArguments = null)
    {

        $this->addArgument('-progress', '/dev/stdout ')
            ->addArgument('-hide_banner')
            ->addArgument('-loglevel', 'error')
            ->addArgument('-nostdin');

        $this->addArgument('-y');

        call_user_func($configCall);

        if (isset($addArguments)) {
            $this->addArgument($addArguments);
        }

        if (isset($destination)) {
            $this->addArgument($destination);
        }


        //all in stdout
        $this->addArgument('2>&1');
        $command = $this->prepareCommand();

        \Yii::debug($command, self::class);
        $this->_eTime = time();
        return $command;

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

    /**
     * @param array $key
     * @param bool $reCreateDir
     * @return mixed
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    protected function newTemporaryFile($key = [], $reCreateDir = true)
    {
        $dir = \Yii::getAlias($this->workingDirAlias);

        if (empty($key)) {
            $key = range($key, 100);
            shuffle($key);
            $key = array_slice($key ,0,10);
        }
        $this->_workingDir = $dir . DIRECTORY_SEPARATOR . sha1(Json::encode($key));

        if ($reCreateDir && is_dir($this->_workingDir)) {
            FileHelper::removeDirectory($this->_workingDir);
        }

        FileHelper::createDirectory($this->_workingDir);


        return $this->_workingDir . DIRECTORY_SEPARATOR . StringHelper::basename(stream_get_meta_data(tmpfile())['uri']);
    }

    /**
     * @return mixed
     */
    public function getWorkingDir()
    {
        return $this->_workingDir;
    }

    /**
     * @return int
     */
    public function getExecutingStartTime()
    {
        return $this->_eTime;
    }


}