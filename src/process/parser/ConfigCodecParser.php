<?php
/**
 *
 * User: develop
 * Date: 04.07.2018
 */

namespace somov\ffmpeg\process\parser;


class ConfigCodecParser extends ConfigParser
{
    protected $rawPattern = '/Codecs:.*?-------(.*?)$/s';

    protected $fieldsPattern = '/^(?\'mode\'.{8})(?\'name\'.{21})(?\'description\'.*?)$/m';

    protected $listAttributes = [
        'decodingSupported',
        'encodingSupported',
        'videoCodec',
        'audioCodec',
        'subTitleCodec',
        'intraFrame',
        'lossyCompression',
        'lossLessCompression',
    ];


}