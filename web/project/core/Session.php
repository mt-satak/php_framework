<?php


/**
 * Class Session
 * セッション情報を管理するクラス。$_SESSION変数のラッパーとして扱う
 */
class Session
{
    /**
     * @var bool
     */
    protected static $sessionStarted = false;

    /**
     * @var bool
     */
    protected static $sessionRegenerated = false;

    /**
     * Session constructor.
     * クッキーなどから受け取ったセッションIDを元にセッションの復元を行う
     */
    public function __construct()
    {
        // 1度のリクエストで複数回セッション開始しないよう静的プロパティでチェックを行う
        if (!self::$sessionStarted) {
            session_start();

            self::$sessionStarted = true;
        }
    }

    /**
     * セッション情報を設定する
     *
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, $value): void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * セッション情報を取得する
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return $default;
    }

    /**
     * セッション情報を消去する
     */
    public function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * セッションIDを再発行する
     *
     * @param bool $destroy
     */
    public function regenerate(bool $destroy = true): void
    {
        // 1度のリクエストで複数回呼び出されないよう静的プロパティでチェックを行う
        if (!self::$sessionRegenerated) {
            session_regenerate_id($destroy);

            self::$sessionRegenerated = true;
        }
    }

    /**
     * ユーザのログイン状態をセッションに保持する
     *
     * @param bool $bool
     */
    public function setAuthenticated(bool $bool): void
    {
        $this->set('_authenticated', (bool)$bool);
        // セッションIDを再発行しておく(セッションハイジャック対策)
        $this->regenerate();
    }

    /**
     * ユーザのログイン状態を取得する
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->get('_authenticated', false);
    }

}