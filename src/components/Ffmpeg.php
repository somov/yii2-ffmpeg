<?php

namespace somov\ffmpeg\components;


use somov\common\components\ProcessRunner;
use somov\common\traits\ContainerCompositions;
use somov\ffmpeg\events\EndEvent;
use somov\ffmpeg\events\ProgressEvent;
use somov\ffmpeg\events\VideoInfoEvent;
use somov\ffmpeg\process\FfmpegProcess;
use somov\ffmpeg\process\FfmpegVersionProcess;
use somov\ffmpeg\process\FfprobeProcess;
use somov\ffmpeg\process\parser\ConvertEndParser;
use somov\ffmpeg\process\parser\VideoInfoParser;
use yii\base\Component;
use yii\base\InvalidValueException;

class Ffmpeg extends Component
{

    use ContainerCompositions;

    const EVENT_BEGIN = 'actionBegin';

    const EVENT_PROGRESS = 'progress';

    const EVENT_END = 'actionEnd';

    /**
     * @var string
     */
    public $ffmpegPath;


    /**
     * @param $source
     * @param $destination
     * @param string|null $format
     * @param array $addArguments
     * @return array
     * @throws \Exception
     */
    public function convert($source, $destination, $format = null, $addArguments = [])
    {
        if (!isset($format)) {
            $i = pathinfo($source);
            $format = $i['extension'];
        }

        if (!$this->getVersion()->formatExists($format)) {
            throw new InvalidValueException('Unknown format ' . $format);
        }

        $info = $this->getVideoInfo($source);

        $this->trigger(self::EVENT_BEGIN, new VideoInfoEvent([
                'info' => $info
            ]
        ));

        $process = $this->createProcess([
            'action' => [
                'convert' => compact('source', 'destination', 'format', 'addArguments')
            ]
        ], $info);

        /** @var ConvertEndParser $result */
        $result = ProcessRunner::exec($process);

        if (!$result->success) {
            throw new \Exception($result->getEndMessage());
        }

        $this->readBuffer($result->getBuffer(), $process, $info);

        $sourceInfo = $this->getVideoInfo($destination);

        $this->trigger(self::EVENT_END, new EndEvent([
            'result' => $result,
            'source' => $info,
            'destination' => $sourceInfo
        ]));

        return [
            $result,
            $info,
            $sourceInfo
        ];
    }

    /**
     * @param string $buffer
     * @param FfmpegProcess $process
     * @param VideoInfoParser $info
     * @return bool
     */
    protected function readBuffer($buffer, $process, $info)
    {
        if ($process->getActionId() === 'convert') {
            return $this->processingProgress($buffer, $process, $info);
        }
        return true;
    }

    /**
     * @param $file
     * @return VideoInfoParser|mixed
     */
    public function getVideoInfo($file)
    {
        return ProcessRunner::exec([
            'class' => FfprobeProcess::class,
            'source' => $file
        ]);
    }

    /**
     * @param string $buffer
     * @param FfmpegProcess $process
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
     * @param VideoInfoParser $info
     * @return object|FfmpegProcess
     */
    protected function createProcess(array $options = [], $info)
    {
        return \Yii::createObject(array_merge([
            'class' => FfmpegProcess::class,
            'commandPath' => $this->ffmpegPath,
            'bufferReaderCallback' => function ($buffer, $process) use ($info) {
                return $this->readBuffer($buffer, $process, $info);
            },
        ], $options));
    }


}