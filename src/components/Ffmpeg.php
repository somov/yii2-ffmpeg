<?php /** @noinspection MissedFieldInspection */

namespace somov\ffmpeg\components;


use somov\common\components\ProcessRunner;
use somov\common\traits\ContainerCompositions;
use somov\ffmpeg\events\EndEvent;
use somov\ffmpeg\events\ProgressEvent;
use somov\ffmpeg\events\VideoInfoEvent;
use somov\ffmpeg\process\FfmpegBaseProcess;
use somov\ffmpeg\process\FfmpegVersionProcess;
use somov\ffmpeg\process\FfprobeProcess;
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
 * @method array convert ($source, $destination, string|null $format, array $addArguments)
 * @method array concat (array $files, string $format, string $destination, array $convertArguments, array $concatArguments)
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
            'convert' => [
                'class' => VideoProcess::class,
                'source', 'destination', 'format', 'addArguments'
            ],
            'concat' => [
                'class' => VideoProcess::class,
                'files', 'format', 'destination', 'convertArguments', 'concatArguments'
            ]
        ];
    }


    /** Запускает процесс FfmpegProcess
     * @param FfmpegBaseProcess $process
     * @param VideoInfoParser $sourceInfo
     * @return array
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

        $destinationInfo = $this->getVideoInfo(ArrayHelper::getValue($process->getActionParams(), 'destination'));

        $this->trigger(self::EVENT_END, new EndEvent([
            'result' => $result,
            'source' => $sourceInfo,
            'destination' => $sourceInfo
        ]));

        unset($this->_runningSourceInfo[$process->getActionId()]);

        return [
            $result,
            $sourceInfo,
            $destinationInfo
        ];
    }


    /**
     * @param $name
     * @param $params
     * @return array|bool
     * @throws \Exception
     */
    protected function callProgressEvent($name, $params)
    {
        if ($action = ArrayHelper::getValue($this->processes(), $name)) {

            $class = ArrayHelper::remove($action, 'class');

            $params = array_combine($action, $params);
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
                'action' => [$name => $params]
            ]), $info);
        }

        return false;
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function __call($name, $params)
    {

        if ($result = $this->callProgressEvent($name, $params)) {
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
        $pattern = '/^frame=(?\'frame\'\s*[\d]+)\sfps=(?\'fps\'\s*[\d]+).*?size=(?\'size\'\s*[\d]+).*?time=(?\'time\'.*?)\sbitrate=(?\'bitrate\'.*?)kbits/m';

        if (preg_match_all($pattern, $buffer, $m, PREG_SET_ORDER)) {
            $raw = reset($m);

            $this->trigger(self::EVENT_PROGRESS,
                (new ProgressEvent(
                    [
                        'info' => $info,
                        'process' => $process
                    ])
                )->setRaw($raw));
        }
        /**
         * Запрещаем сброс буфера если конвертация окончена
         */
        return !(strpos($buffer, 'progress=end') > 1);
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