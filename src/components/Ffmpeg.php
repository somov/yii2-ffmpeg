<?php /** @noinspection MissedFieldInspection */

namespace somov\ffmpeg\components;


use somov\common\components\ProcessRunner;
use somov\common\traits\ContainerCompositions;
use somov\ffmpeg\events\EndEvent;
use somov\ffmpeg\events\ImageEndEvent;
use somov\ffmpeg\events\ProgressEvent;
use somov\ffmpeg\events\VideoInfoEvent;
use somov\ffmpeg\process\FfmpegBaseProcess;
use somov\ffmpeg\process\FfmpegVersionProcess;
use somov\ffmpeg\process\FfprobeProcess;
use somov\ffmpeg\process\ImageProcess;
use somov\ffmpeg\process\parser\ConvertEndParser;
use somov\ffmpeg\process\parser\VideoInfoParser;
use somov\ffmpeg\process\VideoProcess;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\helpers\ArrayHelper;

/**
 * Class Ffmpeg
 * @package somov\ffmpeg\components
 *
 *
 * @method EndEvent convert ($source, $destination, string|null $format, array $addArguments = [])
 * @method EndEvent concat (array $files, string $format, string $destination, array $convertArguments = [], array $concatArguments = [])
 * @method ImageEndEvent createImage(string $source, float $start = 0, $width = null, $height = null, $format = 'image2')
 * @method ImageEndEvent createImagesForPeriod(string $source, integer $count, integer $width = null, integer $height = null, float $start = null, float $end = null, string $format = 'image2', string $extension = 'jpg')
 */
class Ffmpeg extends Component
{

    use ContainerCompositions;

    const EVENT_GET_SOURCE_INFO = 'getSourceInfo';
    const EVENT_BEGIN = 'actionBegin';
    const EVENT_PROGRESS = 'progress';
    const EVENT_END = 'actionEnd';

    /**
     * @var string
     */
    public $ffmpegPath;

    /**
     * Вычислять путем полного декодирования продолжительность источника если не удалось изъять ffprobe
     * @var bool
     */
    public $decodeStreamDuration = true;

    /**
     * Информация о текучем обрабатываемом видео
     * @var VideoInfoParser[]
     */
    private $_runningSourceInfo = [];


    /**
     * @param FfmpegBaseProcess $process
     * @return VideoInfoParser|null
     */
    public function getRunningSourceInfo(FfmpegBaseProcess $process)
    {
        return ArrayHelper::getValue($this->_runningSourceInfo, $process->getActionId());
    }


    /**
     * @return array
     */
    protected function processes()
    {
        return [
            'convert' => VideoProcess::class,
            'concat' => VideoProcess::class,
            'createImage' => ImageProcess::class,
            'createImagesForPeriod' => ImageProcess::class,
        ];
    }


    /** Запускает процесс FfmpegProcess
     * @param FfmpegBaseProcess $process
     * @param VideoInfoParser $sourceInfo
     * @return array|EndEvent
     * @throws \Exception
     */
    protected function exec(FfmpegBaseProcess $process, VideoInfoParser $sourceInfo)
    {
        $this->_runningSourceInfo[$process->getActionId()] = $sourceInfo;

        $this->trigger(self::EVENT_BEGIN, new VideoInfoEvent([
                'info' => $sourceInfo,
                'process' => $process
            ]
        ));

        /** @var ConvertEndParser $result */
        $result = ProcessRunner::exec($process);

        if (!$result->success) {
            $message = $result->getEndMessage();
            if (YII_DEBUG || YII_ENV_TEST) {
                $message .= print_r($process->getStatus(), true);
            }
            throw new \Exception($message);
        }

        $this->processingProgress($result->getBuffer(), $process, $sourceInfo);

        $destinationInfo = null;

        if ($destination = ArrayHelper::getValue($process->getActionParams(), 'destination')) {
            $destinationInfo = $this->getVideoInfo($destination);
        }

        $event = new EndEvent([
            'result' => $result,
            'source' => $sourceInfo,
            'destination' => $destinationInfo
        ]);

        if ($process->hasMethod('configureEndEvent')) {
            $process->configureEndEvent($event);
        }

        $this->trigger(self::EVENT_END, $event);

        unset($this->_runningSourceInfo[$process->getActionId()]);

        return $event;
    }


    /**
     * @param $name
     * @param $params
     * @return array|bool
     * @throws \Exception
     */
    protected function callProgressAction($name, $params)
    {
        if ($class = ArrayHelper::getValue($this->processes(), $name)) {

            $reflection = new \ReflectionMethod($class, 'action' . ucfirst($name));
            $reflection->setAccessible(true);
            $reflectionParams = ArrayHelper::map($reflection->getParameters(), function ($p) {
                /** @var \ReflectionParameter $p */
                return $p->getPosition();
            }, function ($p) use ($params) {
                /** @var \ReflectionParameter $p */
                return [
                    'name' => $p->getName(),
                    'value' => ArrayHelper::getValue($params, $p->getPosition(), ($p->isOptional()) ? $p->getDefaultValue() : null)
                ];
            });

            $params = ArrayHelper::map($reflectionParams, 'name', 'value');

            $info = null;

            if ($source = ArrayHelper::getValue($params, 'source')) {
                $info = $this->getVideoInfo($params['source']);
            } else {
                if (empty($params['files'])) {
                    throw  new InvalidCallException('Missing video source');
                }

                $info = $this->getVideoInfo($params['files'][0]);
            }

            return $this->exec($this->createProcess([
                'class' => $class,
                'progressInfo' => $info,
                'action' => [$name => $params],
                'bufferSize' => 512
            ]), $info);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $params)
    {

        if ($result = $this->callProgressAction($name, $params)) {
            return $result;
        }

        return parent::__call($name, $params);
    }

    /**
     * @param $file
     * @return mixed|VideoInfoParser
     * @throws \Exception
     */
    public function getVideoInfo($file)
    {
        /** @var VideoInfoParser $info */
        $info = ProcessRunner::exec([
            'class' => FfprobeProcess::class,
            'source' => $file
        ]);

        $this->trigger(self::EVENT_GET_SOURCE_INFO, new VideoInfoEvent([
                'info' => $info
            ]
        ));

        if (!$info->getDuration() && $this->decodeStreamDuration) {
            /** @var ConvertEndParser $result */
            $result = ProcessRunner::exec($this->createProcess([
                'class' => VideoProcess::class,
                'progressInfo' => $info,
                'action' => [
                    'decodeStreamDuration' => [
                        'source' => $file
                    ]
                ]
            ]));

            if (!$result->success) {
                throw new \Exception('Error decode source duration ' . $result->getEndMessage());
            }

            $info->setDuration($result->getEndDuration());
        };

        return $info;
    }

    /**
     * @param string $buffer
     * @param FfmpegBaseProcess $process
     * @param VideoInfoParser $info
     * @return bool
     */
    private function processingProgress($buffer, $process, $info)
    {
        //https://regex101.com/r/hXURld/9
        $pattern = "#bitrate=\s*(?'bitrate'(N\/A|\d*(?:\.\d+)?)).*total_size=\s*(?'size'(N\/A|\d+)).*out_time_ms=\s*(?'time_ms'\d+).*speed=\s*(?'speed'\d*(?:\.\d+)?).*progress=\s*(?'state'\w+)#s";

        if (preg_match_all($pattern, $buffer, $m, PREG_SET_ORDER)) {
            $event = (new ProgressEvent(reset($m), [
                'info' => $info,
                'process' => $process
            ]));

            $this->trigger(self::EVENT_PROGRESS, $event);

            return $event->isRunning();

        }

        return true;
    }

    /**
     * @return FfmpegVersionProcess
     */
    public function getVersion()
    {
        return $this->getCompositionFromFactory([ProcessRunner::class, 'exec'], FfmpegVersionProcess::class, [
            'commandPath' => $this->ffmpegPath
        ]);
    }

    /**
     * @param array $options
     * @return object|FfmpegBaseProcess
     */
    protected function createProcess(array $options = [])
    {

        $default = [
            'commandPath' => $this->ffmpegPath,
        ];

        if ($info = ArrayHelper::remove($options, 'progressInfo')) {
            $default['bufferReaderCallback'] = function ($buffer, $process) use ($info) {
                return $this->processingProgress($buffer, $process, $info);
            };
        }
        return \Yii::createObject(ArrayHelper::merge($default, $options), [$this]);
    }


}