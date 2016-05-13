<?php

namespace Database;

use PDO;

/**
 * 数据库连接帮助类
 * @author 邹义良
 */
class DBConnection
{
    protected $pdo;
    protected $transactions = 0;
    protected $config = array(
        'dsn' => 'mysql:host=localhost;dbname=test',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'tablePrefix' => '',
        'emulatePrepares' => false,
    );

    public function __construct(array $config)
    {
        $this->config = array_replace_recursive($this->config, $config);
    }

    /**
     * 执行SQL语句 (增、删、改 类型的SQL)，返回受影响行数
     *
     * @param string $sql 执行的SQL，可以包含问号或冒号占位符，支持{{%table_name}}格式自动替换为表前缀
     * @param array $params 参数，对应SQL中的冒号或问号占位符
     * @return int 返回受景响行数
     */
    public function execute($sql, $params = array())
    {
        $sql = $this->quoteSql($sql);
        $statement = $this->getPdo()->prepare($sql);

        if ($statement->execute($params)) {
            return $statement->rowCount();
        }
        return 0;
    }

    /**
     * 执行查询类型的SQL语句，返回结果数据
     *
     * @param string $sql
     * @param array $params
     * @param int $fetchStyle 从此参数开始，为setFetchMode的参数，例如为PDO::FETCH_CLASS，则可以传入第4个参数(类名)
     * @return array
     */
    public function query($sql, $params = array(), $fetchStyle = PDO::FETCH_ASSOC)
    {

        $sql = $this->quoteSql($sql);

        $statement = $this->getPdo()->prepare($sql);
        $statement->execute($params);

        $args = func_get_args();
        $args = array_slice($args, 2);

        $args[0] = $fetchStyle;

        //PDOStatement::setFetchMode ( int $mode )
        //PDOStatement::setFetchMode ( int $PDO::FETCH_COLUMN , int $colno )
        //PDOStatement::setFetchMode ( int $PDO::FETCH_CLASS , string $classname , array $ctorargs )
        //PDOStatement::setFetchMode ( int $PDO::FETCH_INTO , object $object )
        call_user_func_array(array($statement, 'setFetchMode'), $args);
        return $statement->fetchAll();
    }

    /**
     * 执行查询统计类型语句, 返回具体单个值, 常用于COUNT、AVG、MAX、MIN
     * @param $sql
     * @param array $params
     * @return mixed 成功返回数据，失败返回FALSE
     */
    public function queryScalar($sql, $params = array())
    {
        $sql = $this->quoteSql($sql);
        $statement = $this->getPdo()->prepare($sql);
        if ($statement->execute($params) && ($data = $statement->fetch(PDO::FETCH_NUM)) !== false) {
            if (is_array($data) && isset($data[0])) {
                return $data[0];
            }
        }
        return false;
    }

    /**
     * 返回最后插入行的ID或序列值
     * @param null $sequence 序列名称
     * @return string
     */
    public function getLastInsertId($sequence = null)
    {
        return $this->getPdo()->lastInsertId($sequence);
    }

    /**
     * 开启事务
     */
    public function beginTransaction()
    {
        ++$this->transactions;
        if ($this->transactions == 1) {
            $this->getPdo()->beginTransaction();
        }
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        if ($this->transactions == 1) $this->getPdo()->commit();
        --$this->transactions;
    }

    /**
     * 回滚事务
     */
    public function rollBack()
    {
        if ($this->transactions == 1) {
            $this->transactions = 0;
            $this->getPdo()->rollBack();
        } else {
            --$this->transactions;
        }
    }

    /**
     * 断开数据库链接
     */
    public function disconnect()
    {
        $this->pdo = null;
    }

    /**
     * 返回PDO对象
     * @return PDO
     */
    public function getPdo()
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }
        $this->pdo = $this->makePdo($this->config);
        return $this->pdo;
    }

    protected function makePdo(array $config)
    {
        $pdo = new PDO($config['dsn'], $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //false表示不使用PHP本地模拟prepare
        if (constant('PDO::ATTR_EMULATE_PREPARES')) {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $config['emulatePrepares']);
        }

        $pdo->exec('SET NAMES ' . $pdo->quote($config['charset']));
        return $pdo;
    }

    /**
     * 返回表前缀
     * @return string
     */
    protected function getTablePrefix()
    {
        return $this->config['tablePrefix'];
    }

    /**
     * 解析SQL中的表名
     * 表前缀示例：当表前缀为 "cms_" 时将sql中的 "{{%user}}" 解析为 "`cms_user`"
     * 列名解析示例："[[name]]" 解析为 "`name`"
     * @param string $sql
     * @return string
     */
    public function quoteSql($sql)
    {
        return preg_replace_callback(
            '/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])/',
            function ($matches) {
                if (isset($matches[3])) {
                    return '`' . $matches[3] . '`';// quoteColumnName
                } else {
                    return str_replace('%', $this->getTablePrefix(), '`' . $matches[2] . '`');//quoteTableName
                }
            },
            $sql
        );
    }
}