<?php


class DbManager
{
    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var array
     */
    protected $repositoryConnectionMap = [];

    /**
     * @var array
     */
    protected $repositories = [];

    /**
     * @param string $name
     * @param array $params
     */
    public function connect(string $name, array $params): void
    {
        $params = array_merge([
            'dsn'      => null,
            'user'     => '',
            'password' => '',
            'options'  => [],
        ], $params);

        $con = new PDO(
            $params['dsn'],
            $params['user'],
            $params['password'],
                $params['options']
        );

        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connections[$name] = $con;
    }

    /**
     * @param string|null $name
     * @return array
     */
    public function getConnection(?string $name): array
    {
        if (is_null($name)) {
            return current($this->connections);
        }
        // 指定がなければPDOを取得する
        return $this->connections[$name];
    }

    /**
     * @param string $repositoryName
     * @param string $name
     */
    public function setRepositoryConnectionMap(string $repositoryName, string $name): void
    {
        $this->repositoryConnectionMap[$repositoryName] = $name;
    }

    /**
     * @param string $repositoryName
     * @return array
     */
    public function getConnectionForRepository(string $repositoryName): array
    {
        if (isset($this->repositoryConnectionMap[$repositoryName])) {
            $name = $this->repositoryConnectionMap[$repositoryName];
            $con = $this->getConnection($name);
        } else {
            $con = $this->getConnection();
        }

        return $con;
    }

    /**
     * @param string $repositoryName
     * @return mixed
     */
    public function get(string $repositoryName)
    {
        if (!isset($this->repositories[$repositoryName])) {
            $repositoryClass = $repositoryName. 'Repository';
            $con = $this->getConnectionForRepository($repositoryName);

            $repository = new $repositoryClass($con);

            $this->repositories[$repositoryName] = $repository;
        }

        return $this->repositories[$repositoryName];
    }

    /**
     * PDOインスタンスを破棄して、データベース接続を閉じる
     */
    public function __destruct()
    {
        // 先にリポジトリを破棄する(リポジトリ内でも接続情報を参照しているため)
        foreach ($this->repositories as $repository) {
            unset($repository);
        }

        foreach ($this->connections as $con) {
            unset($con);
        }
    }
}