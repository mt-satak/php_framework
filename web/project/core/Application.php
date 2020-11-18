<?php


abstract class Application
{
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var DbManager
     */
    protected $dbManager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $loginAction = [];

    /**
     * Application constructor.
     * @param bool $debug
     */
    public function __construct(bool $debug = false)
    {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    /**
     * デバッグモードを設定する
     *
     * @param bool $debug
     */
    protected function setDebugMode(bool $debug): void
    {
        if ($debug) {
            $this->debug = true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        } else {
            $this->debug = false;
            ini_set('display_errors', 0);
        }
    }

    /**
     * 各オブジェクト・ルーティングの初期化を行う
     */
    protected function initialize(): void
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->dbManager = new DbManager();
        $this->router = new Router($this->registerRoutes());
    }

    /**
     * Routerからコントローラを特定してレスポンスを送信する
     * @throws HttpNotFoundException
     */
    public function run(): void
    {
        try {
            $params = $this->router->resolve($this->request->getPathInfo());
            if ($params === false) {
                throw new HttpNotFoundException('No route found for'. $this->request->getPathInfo());
            }

            $controller = $params['controller'];
            $action = $params['action'];

            $this->runAction($controller, $action, $params);
        } catch (HttpNotFoundException $e) {
            $this->render404Page($e);
        } catch (UnAuthorizedActionException $e) {
            [$controller, $action] = $this->loginAction;
            $this->runAction($controller, $action);
        }
        $this->response->send();
    }

    /**
     * アクションを実行する
     *
     * @param string $controllerName
     * @param $action
     * @param array $params
     * @throws HttpNotFoundException
     */
    public function runAction(string $controllerName, string $action, array $params = []): void
    {
        // ucfirst関数でクラス名の先頭を大文字指定する
        $controllerClass = ucfirst($controllerName). 'Controller';
        $controller = $this->findController($controllerClass);

        if ($controller === false) {
            throw new HttpNotFoundException($controllerClass. ' controller is not found.');
        }

        $content = $controller->run($action, $params);

        $this->response->setContent($content);
    }

    /**
     * コントローラクラスが読み込まれていない場合、クラスファイルを読み込む
     *
     * @param string $controllerClass
     * @return false|mixed チェックを通過した場合コントローラのインスタンスを返却する
     */
    public function findController(string $controllerClass)
    {
        if (!class_exists($controllerClass)) {
            $controllerFile = $this->getControllerDir(). '/'. $controllerClass. '.php';
        }

        if (!is_readable($controllerFile)) {
            return false;
        } else {
            require_once $controllerFile;

            if (!class_exists($controllerClass)) {
                return false;
            }
        }

        return new $controllerClass($this);
    }

    /**
     * @param HttpNotFoundException $e
     */
    protected function render404Page(HttpNotFoundException $e)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $this->response->setContent(<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>404</title>
</head>
<body>
    {$message}
</body>
</html>
EOF
        );
    }

    public function configure() {}

    abstract public function getRootDir();

    abstract protected function registerRoutes();

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debug;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return DbManager
     */
    public function getDbManager(): DbManager
    {
        return $this->dbManager;
    }

    /**
     * @return string
     */
    public function getControllerDir(): string
    {
        return $this->getRootDir(). '/controllers';
    }

    /**
     * @return string
     */
    public function getViewDir(): string
    {
        return $this->getRootDir(). '/views';
    }

    /**
     * @return string
     */
    public function getModelDir(): string
    {
        return $this->getRootDir(). '/models';
    }

    public function getWebDir(): string
    {
        return $this->getRootDir(). '/web';
    }
}