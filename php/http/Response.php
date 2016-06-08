<?php

namespace Rain\Http;

/**
 * Http Response
 * @author 邹义良
 */
class Response
{
    public $body;
    public $transferStats;
    public $header;

    public function __construct(array $data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * http响应状态码
     * @return int
     */
    public function getStatusCode()
    {
        return (int)$this->transferStats['http_code'];
    }

    /**
     * http响应body
     * @return string
     */
    public function getBody()
    {
        return (string)$this->body;
    }

    /**
     * http响应头部信息
     * @return string
     */
    public function getHeaderRaw()
    {
        return (string)$this->header;
    }
}