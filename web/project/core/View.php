<?php


class View
{
    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * @var array
     */
    protected $layoutVariables = [];

    /**
     * View constructor.
     *
     * @param string $baseDir
     * @param array $defaults
     */
    public function __construct(string $baseDir, array $defaults = [])
    {
        $this->baseDir = $baseDir;
        $this->defaults = $defaults;
    }

    public function setLayoutVar(string $name, $value): void
    {
        $this->layoutVariables[$name] = $value;
    }

    /**
     * @param string $_path
     * @param array $_variables
     * @param bool $_layout
     *
     * @return false|mixed|string
     */
    public function render(string $_path, array $_variables = [], bool $_layout = false)
    {
        // TODO: 将来的にbladeやtwigを採用したい場合はひと工夫必要
        $_file = $this->baseDir. '/'. $_path. '.php';

        extract(array_merge($this->defaults, $_variables));

        ob_start(); // アウトプットバッファリング開始
        ob_implicit_flush(0); // 引数で自動フラッシュを無効に設定

        require $_file;

        // バッファに格納された文字列を取得する
        $content = ob_get_clean(); // 取得と同時にバッファのクリアも行う

        if ($_layout) {
            $content = $this->render($_layout, array_merge($this->layoutVariables, ['_content' => $content]));
        }

        return $content;
    }

    /**
     * 特殊文字のエスケープを行う
     *
     * @param string $str
     *
     * @return string
     */
    public function h(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}