<?php

class ClassLoader
{
    /**
     * @var array
     */
    protected $dirs;

    /**
     * PHPにオートローダクラスを登録する
     * オートロード時、この関数に設定されたコールバック(loadClass())が呼び出される
     *
     * @return void
     */
    public function register(): void
    {
        // https://www.php.net/manual/ja/function.spl-autoload-register.php
        // 指定した関数を __autoload() の実装として登録する
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * 引数で指定されたオートロード対象のディレクトリのフルパスをプロパティに追加する
     *
     * @param string $dir
     * @return void
     */
    public function registerDir(string $dir): void
    {
        $this->dirs[] = $dir;
    }

    /**
     * オートロード時に自動的にクラスファイルを読み込む
     *
     *
     * @param string $className
     * @return void
     */
    public function loadClass(string $className): void
    {
        // プロパティに登録済みのディレクトリからクラスファイルを探す
        foreach ($this->dirs as $dir) {
            $file = $dir . '/' . $className . '.php';
            if (is_readable($file)) {
                // 見つけたらrequireで読み込む
                // https://www.php.net/manual/ja/function.require.php
                require $file;
                return;
            }
        }
    }
}