<?php


class Router
{
    /**
     * @var array
     */
    protected $routes;

    /**
     * Router constructor.
     * @param $definitions
     */
    public function __construct(array $definitions)
    {
        $this->routes = $this->compileRoutes($definitions);
    }

    /**
     * 渡されたルーティング定義配列の各キーに含まれる動的パラメータを、
     * 正規表現でキャプチャできる形式に変換して返却する
     *
     * @param array $definitions
     * @return array
     */
    private function compileRoutes(array $definitions): array
    {
        $routes = [];

        foreach ($definitions as $url => $params) {
            $tokens = explode('/', ltrim($url, '/'));
            foreach ($tokens as $i => $token) {
                if (0 === strpos($token, ':')) {
                    $name = substr($token, 1);
                    $token = '(?P<' . $name . '>[^/]+)';
                }
                $tokens[$i] = $token;
            }
            $pattern = '/' . implode('/', $tokens);
            $routes[$pattern] = $params;
        }

        return $routes;
    }

    /**
     * @param string $pathInfo
     * @return array|false
     */
    public function resolve(string $pathInfo): array
    {
        if ('/' !== substr($pathInfo, 0, 1)) {
            $pathInfo = '/' . $pathInfo;
        }

        foreach ($this->routes as $pattern => $params) {
            if (preg_match('#^' . $pattern . '$#', $pathInfo, $matches)) {
                $params = array_merge($params, $matches);

                return $params;
            }
        }

        return false;
    }
}