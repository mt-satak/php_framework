<?php


class Response
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $statusText = 'OK';

    /**
     * @var array
     */
    protected $httpHeaders = [];

    public function send(): void
    {
        header('HTTP/1.1' . $this->statusCode . ' ' . $this->statusText);

        foreach ($this->httpHeaders as $name => $value) {
            header($name . ':' . $value);
        }

        echo $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @param int $statusCode
     * @param string $statusText
     */
    public function setStatusCode(int $statusCode, $statusText = ''): void
    {
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setHttpHeader(string $name, string $value): void
    {
        $this->httpHeaders[$name] = $value;
    }
}