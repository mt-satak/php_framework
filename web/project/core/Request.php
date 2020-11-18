<?php


class Request
{
    /**
     * @return bool
     */
    public function isPost(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }
        return false;
    }

    /**
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getGet(string $name, ?string $default): string
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        return $_GET[$default];
    }

    /**
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getPost(string $name, ?string $default): string
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        return $_POST[$default];
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        return $_SERVER['SERVER_NAME'];
    }

    public function isSsl(): bool
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestUri = $this->getRequestUri();

        if (0 === strpos($requestUri, $scriptName)) {
            return $scriptName;
        } elseif (0 === strpos($requestUri, dirname($scriptName))) {
            return rtrim(dirname($scriptName), '/');
        }
        return  '';
    }

    /**
     * @return string
     */
    public function getPathInfo(): string
    {
        $baseUrl = $this->getBaseUrl();
        $requestUrl = $this->getRequestUri();

        $pos = strpos($requestUrl, '?');
        if (false !== $pos) {
            $requestUrl = substr($requestUrl, 0, $pos);
        }
        return (string)substr($requestUrl, strlen($baseUrl));
    }
}