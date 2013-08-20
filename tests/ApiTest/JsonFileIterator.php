<?php

namespace ApiTest;

use Zend\Json\Json;

class JsonFileIterator extends \GlobIterator
{

    /**
     * Override parent to force pattern for JSON file
     * @param string $path
     */
    public function __construct($path)
    {
        $path = $path . '/*.json';
        parent::__construct($path, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO);
    }

    /**
     * Override pattern to return an array instead of FileInfo.
     * @return array [url parameters, expected json, optional message]
     */
    public function current()
    {
        $file = parent::current();

        @list($params, $message) = split('#', str_replace('.json', '', $file->getFilename()));

        $fullpath = getcwd() . '/../data/logs/tests/' . $file->getPath() . '/';
        @mkdir($fullpath, 0777, true);

        $json = Json::decode(file_get_contents($file->getPathname()), Json::TYPE_ARRAY);
        $result = array(
            $params,
            $json,
            $message,
            $fullpath . $file->getFilename(),
        );

        return $result;
    }

}