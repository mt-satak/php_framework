<?php


/**
 * Class DbRepository
 * データベースアクセスを行う全ての子クラスの抽象クラス
 */
abstract class DbRepository
{
    /**
     * @var PDO
     */
    protected $con;

    /**
     * DbRepository constructor.
     * DbManagerからPDOインスタンスを受け取ってプロパティに保持する
     *
     * @param $con
     */
    public function __construct(PDO $con)
    {
        $this->setConnection($con);
    }

    /**
     * @param PDO $con
     */
    public function setConnection(PDO $con): void
    {
        $this->con = $con;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function execute(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetch(string $sql, array $params = []): array
    {
        // fetch()の引数で連想配列を返すよう指定
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        // fetch()同様、連想配列を返すよう指定
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

}