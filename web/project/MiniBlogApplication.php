<?php


class MiniBlogApplication extends Application
{
    /**
     * @var string[]
     */
    protected $loginAction = ['account', 'signin'];

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return dirname(__FILE__);
    }

    /**
     * @return array
     */
    protected function registerRoutes(): array
    {
        return [];
    }

    public function configure(): void
    {
        $this->dbManager->connect('master', [
            'dsn' => 'mysql:dbname=mini_blog;host=localhost',
            'user' => 'root',
            'password' => '',
        ]);
    }
}