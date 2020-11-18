<?php


abstract class Controller
{
    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $actionName;

    /**
     * @var Application
     */
    protected $application;

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
     * @var array
     */
    protected $authActions = [];

    /**
     * Controller constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->controllerName = strtolower(substr(get_class($this), 0, -10));
        $this->application    = $application;
        $this->request        = $application->getRequest();
        $this->response       = $application->getResponse();
        $this->session        = $application->getSession();
        $this->dbManager      = $application->getDbManager();
    }

    /**
     * アクションメソッドを実行する
     *
     * @param string $action
     * @param array $params
     *
     * @return string
     * @throws HttpNotFoundException
     * @throws UnAuthorizedActionException
     */
    public function run(string $action, array $params = []): string
    {
        $this->actionName = $action;

        $actionMethod = $action. 'Action';
        if (!method_exists($this, $actionMethod)) {
            $this->forward404();
        }

        // ログイン認証の要不要を判定する
        if ($this->needsAuthentication($action)
        && !$this->session->isAuthenticated()
        ) {
            // ログインが必要な場合は例外を投げておく
            throw new UnAuthorizedActionException();
        }

        // ※可変変数を利用してメソッド呼び出しを行なっている
        // https://www.php.net/manual/ja/functions.variable-functions.php
        return $this->$actionMethod($params);
    }

    /**
     * ログインが必要かどうかを判定し、その結果を返却する
     *
     * @param string $action
     *
     * @return bool
     */
    protected function needsAuthentication(string $action): bool
    {
        if ($this->authActions === true
        || (is_array($this->authActions) && in_array($action, $this->authActions))
        ) {
            return true;
        }

        return false;
    }

    /**
     * ビューファイルの読み込みを行う
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     * @param array $variables
     * @param string|null $template
     * @param string $layout
     *
     * @return false|mixed|string
     */
    protected function render(array $variables = [], ?string $template, string $layout = 'layout')
    {
        // Viewインスタンスに渡す引数を用意する
        $defaults = [
            'request' => $this->request,
            'response' => $this->response,
            'session' => $this->session,
        ];

        $view = new View($this->application->getViewDir(), $defaults);

        // テンプレート名を特定する
        if (is_null($template)) {
            // 指定がなければアクション名をテンプレート名に指定する
            $template = $this->actionName;
        }

        $path = $this->controllerName. '/'. $template;

        return $view->render($path, $variables, $layout);
    }

    /**
     * 404画面へのリダイレクトを行う
     *
     * @throws HttpNotFoundException
     */
    protected function forward404(): void
    {
        throw new HttpNotFoundException(
            'Forwarded 404 page from '. $this->controllerName. '/'. $this->actionName
        );
    }

    /**
     * URLを引数で受け取り、レスポンスオブジェクトにリダイレクトを行う
     *
     * @param string $url
     */
    protected function redirect(string $url): void
    {
        if (!preg_match('#https://#', $url)) {
            $protocol = $this->request->isSsl() ? 'https://' : 'http://';
            $host = $this->request->getHost();
            $baseUrl = $this->request->getBaseUrl();

            $url = $protocol. $host. $baseUrl. $url;
        }

        $this->response->setStatusCode(302, 'Found');
        $this->response->setHttpHeader('Location', $url);
    }

    /**
     * トークンを生成しセッションに格納する
     * ※トークンの最大保持数は10件とし、保持上限を超える場合は古いものを削除し新しいものを追加する
     *
     * @param string $formName
     *
     * @return string
     */
    protected function generateCsrfToken(string $formName): string
    {
        $key = 'csrf_tokens/'. $formName;
        $tokens = $this->session->get($key, []);
        //
        if (count($tokens) >= 10) {
            array_shift($tokens);
        }

        // ハッシュ化しておく
        $token = sha1($formName. session_id(). microtime());
        $tokens[] = $token;

        $this->session->set($key, $tokens);

        return $token;
    }

    /**
     * POSTパラメータに含まれるトークンをセッションに格納されたトークンと突き合わせて
     * トークンが有効かどうか判定結果を返却する
     *
     * @param string $formName
     * @param string $token
     *
     * @return bool
     */
    protected function checkCsrfToken(string $formName, string $token): bool
    {
        $key = 'csrf_token/'. $formName;
        $tokens = $this->session->get($key, []);

        // postされたトークンをセッションに保存済みトークンから探す
        $pos = array_search($token, $tokens, true);
        if (false !== $pos) {
            // 利用済みトークンは不要なので削除する
            unset($tokens[$pos]);
            $this->session->set($key, $tokens);

            // 有効判定OK
            return true;
        }

        // 有効判定NG
        return false;
    }
}