<?php

namespace Database;

/**
 * 数据库操作模型类
 * @author 邹义良
 */
class Model
{
    protected static $connection;

    protected $table;
    protected $field;
    protected $orderBy;
    protected $limit;
    protected $offset;
    protected $condition;
    protected $params = array();

    const PARAM_PREFIX = ':_db_prefix_';

    public function __construct($table)
    {
        if (strpos($table, '{{') === false) {
            $table = '{{%' . $table . '}}';
        }
        if (!preg_match('/^\\{\\{%?[\\.\w]+%?\\}\\}$/', $table)) {
            throw new \Exception('Table name error:' . $table);
        }

        $this->table = $table;
    }

    /**
     * @param $table
     * @return $this
     */
    public static function table($table)
    {
        static $models = array();
        if (!array_key_exists($table, $models)) {
            $models[$table] = new self($table);
        }
        return $models[$table];
    }

    public function setConnection(Connection $connection)
    {
        self::$connection = $connection;
        return $this;
    }

    /**
     * @return Connection
     */
    public static function getConnection($config = null)
    {
        global $dbConnectionConfig;

        if (self::$connection instanceof Connection) {
            return self::$connection;
        }
        self::$connection = new Connection($config ? $config : $dbConnectionConfig);
        return self::$connection;
    }

    /**
     * 执行新增操作
     * @param array $data 列名只允许字母数字或下划线
     * @return bool
     */
    public function insert(array $data)
    {
        $names = array();
        $placeholders = array();
        foreach ($data as $name => $value) {
            if (preg_match('/^\w+$/', $name)) {
                $names[] = '[[' . $name . ']]';
                $placeholders[] = ':' . $name;
            }
        }

        $sql = 'INSERT INTO '
            . $this->table
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';

        return self::getConnection()->execute($sql, $data) > 0;
    }

    public function insertGetId(array $data)
    {
        if (self::insert($data)) {
            return self::getConnection()->getLastInsertId();
        }
        return 0;
    }

    /**
     * 执行数据库DELETE操作，返回受影响行数
     * @param null $id 如果指定id，则删除指定id数据(where条件同时也有效)。如果不指定id，则只跟据where条件查询。如果是复合主键，$id传入关联数组即可
     * @return int
     */
    public function delete($id = null)
    {
        if (!self::isEmpty($id)) {
            self::whereWithPk($id);
        }

        $sql = 'DELETE FROM ' . $this->table . (self::isEmpty($this->condition) ? '' : ' WHERE ' . $this->condition);

        $sql = $this->replacePlaceholder($sql);

        $rowCount = self::getConnection()->execute($sql, $this->params);

        $this->reset();

        return $rowCount;
    }

    /**
     * 执行数据库UPDATE操作，返回受影响行数
     * @param array $data 更新的关联数组，key只允许字母数字或下划线
     * @return int
     * @throws \Exception
     */
    public function update(array $data)
    {
        if (count($data) == 0) {
            return 0;
        }

        $placeholders = array();
        $params = array();
        foreach ($data as $name => $value) {
            if (!preg_match('/^\w+$/', $name)) {
                throw new \Exception('列名不能包含特殊字符');
            }
            $placeholders[] = "[[$name]] = " . self::PARAM_PREFIX . $name;
            $params[self::PARAM_PREFIX . $name] = $value;
        }

        $this->params = array_merge($this->params, $params);

        $sql = 'UPDATE ' . $this->table
            . ' SET ' . implode(', ', $placeholders)
            . (self::isEmpty($this->condition) ? '' : " WHERE {$this->condition}");

        $sql = $this->replacePlaceholder($sql);

        $rowCount = self::getConnection()->execute($sql, $this->params);

        $this->reset();

        return $rowCount;
    }

    /**
     * 自增
     * @param $field
     * @param int $value
     * @return int
     */
    public function increment($field, $value = 1)
    {
        $sql = 'UPDATE ' . $this->table
            . " SET [[$field]] = [[$field]] + ?"
            . (self::isEmpty($this->condition) ? '' : " WHERE {$this->condition}");

        $sql = $this->replacePlaceholder($sql);

        $rowCount = self::getConnection()->execute($sql, array_merge(array($value), $this->params));

        $this->reset();

        return $rowCount;
    }

    /**
     * 返回符合条件的所有数据
     * @return array
     * @throws \Exception
     */
    public function select()
    {
        $sql = 'SELECT ' . (self::isEmpty($this->field) ? '*' : $this->field) . ' FROM ' . $this->table
            . (self::isEmpty($this->condition) ? '' : (' WHERE ' . $this->condition))
            . (self::isEmpty($this->orderBy) ? '' : (' ORDER BY ' . $this->orderBy))
            . (self::isEmpty($this->limit) ? '' : (' LIMIT ' . intval($this->offset) . ', ' . intval($this->limit)));

        $sql = $this->replacePlaceholder($sql); // 统一占位符

        $params = $this->params;

        $this->reset();

        return $this->getConnection()->query($sql, $params);
    }

    /**
     * 返回符合条件的单条数据
     * @param null $id 如果指定id，则根据id查询(where条件同时也有效)。如果不指定id，则只跟据where条件查询。如果是复合主键，$id传入关联数组即可
     * @return null|array
     */
    public function find($id = null)
    {
        if (!self::isEmpty($id)) {
            self::whereWithPk($id);
        }

        $this->limit(1);

        $result = $this->select();

        if (count($result) == 0) {
            return null;
        }

        return $result[0];
    }

    /**
     * 根据主键生成where条件
     * @param $id
     * @return $this
     * @throws \Exception
     */
    protected function whereWithPk($id, $logic = 'AND')
    {
        $pks = self::queryPrimaryKeyFields();

        if (count($pks) == 1) {
            $id = array($pks[0] => $id); //转为关联数组
        }

        if (count($pks) != count($id)) {
            throw new \Exception('复合主键需要传入数组');
        }

        //e.g. ['user_id'=>null, 'article_id'=>null]
        $pks = array_combine($pks, array_fill(0, count($pks), null));

        return $this->where(array_merge($pks, $id), $logic);
    }

    /**
     * 统计查询 COUNT、SUM、MAX、 MIN、AVG
     * @param $method
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        if (in_array(strtoupper($method), array('SUM', 'COUNT', 'MAX', 'MIN', 'AVG'))) {

            $field = isset($arguments[0]) ? $arguments[0] : (self::isEmpty($this->field) ? '*' : $this->field);

            $sql = "SELECT {$method}({$field}) FROM " . $this->table
                . (self::isEmpty($this->condition) ? '' : "WHERE {$this->condition}");

            $sql = $this->replacePlaceholder($sql);

            $params = $this->params;

            $this->reset();

            return self::getConnection()->queryScalar($sql, $params);
        }

        return self::__callStatic($method, $arguments);
    }

    public static function  __callStatic($method, $arguments)
    {
        if (method_exists(self::getConnection(), $method)) {
            return call_user_func_array([self::getConnection(), $method], $arguments);
        }

        throw new \Exception('Call to undefined method ' . __CLASS__ . '::' . $method . '()');
    }

    /**
     * 指定查询字段 推荐使用数组,例如 ['id','name','age']
     * @param array|string $field
     * @return $this
     * @throws \Exception
     */
    public function field($field)
    {
        if (is_array($this->field)) {
            $field = array();
            foreach ($this->field as $value) {
                $field[] = "[[$value]]";
            }
            $field = join(',', $field);
        }

        if (!preg_match('/^[\w\s\.\,\[\]`]+$/', $field)) { //字母、数字、下划线、空白、点、逗号、中括号、反引号
            throw new \Exception('field error.' . $field);
        }

        $this->field = $field;
        return $this;
    }

    /**
     *
     * @param int|string $limit
     * @return $this
     */
    public function limit($limit)
    {
        //e.g. "20,10"
        if (is_string($limit) && strpos($limit, ',') !== false) {
            list($this->offset, $limit) = explode(',', $limit);
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * 排序
     * @param array|string $columns
     * @return $this
     * @throws \Exception
     */
    public function orderBy($columns)
    {
        if (!is_array($columns)) {

            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);

            $temp = array();
            foreach ($columns as $column) {
                if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
                    $temp[$matches[1]] = strcasecmp($matches[2], 'desc') ? SORT_ASC : SORT_DESC;
                } else {
                    $temp[$column] = SORT_ASC;
                }
            }

            $columns = $temp;
        }

        $orders = array();
        foreach ($columns as $name => $direction) {

            if (!preg_match('/^[\w\-\.]+$/', $name)) { //列名只允许字母、数字、下划线、点(.)、中杠(-)
                throw new \Exception('Order by field error:' . $name);
            }

            $orders[] = $name . ($direction === SORT_DESC ? ' DESC' : '');
        }

        $this->orderBy = implode(', ', $orders);
        return $this;
    }

    /**
     * 设置条件
     * @param string $condition
     * @param array $params $condition中占位符对应的值。 $condition为array时，此参数代表$logic
     * @param string $logic 当已存在where条件时，再次调用此方法，默认使用AND连接已有条件。需要以OR连接时，通过此参数指定。
     * @return $this
     * @throws \Exception
     */
    public function where($condition = '', $params = array(), $logic = 'AND')
    {
        if (self::isEmpty($condition)) {
            return $this;
        }

        if (is_numeric($condition)) {
            return $this->whereWithPk($condition, $params);
        }

        if (is_array($condition)) {
            return $this->whereWithArray($condition, $params);
        }

        if (empty($this->condition)) {

            $this->condition = $condition;
            $this->params = $params;
        } else {

            $logic = (is_string($logic) && in_array(strtoupper($logic), array('AND', 'OR'))) ? $logic : 'AND';
            $this->condition = "($this->condition) $logic ($condition)";
            $this->params = array_merge($this->params, $params);
        }
        return $this;
    }

    protected function whereWithArray(array $where, $logic)
    {
        $params = array();
        $conditions = array();

        foreach ($where as $k => $v) {
            if (preg_match('/^\w+$/', $k)) {

                if (is_array($v)) {

                    $temp = self::whereIn($k, $v);
                    $conditions[] = $temp['conditions'];
                    $params = array_merge($params, $temp['params']);

                } else {
                    $conditions[] = '[[' . $k . ']] = ?';
                    $params[] = $v;
                }

            } else {
                throw new \Exception('Where条件中的key必须是字母、数字或下划线');
            }
        }

        if (count($conditions) > 0) {
            return $this->where(join(' AND ', $conditions), $params, $logic);
        }

        return $this;
    }

    protected function whereIn($field, array $values)
    {
        if (count($values) == 0) {
            throw new \Exception('IN条件数组不能为空'); //in条件为空时，是作为无条件处理，还是不匹配任一记录，将产生歧义
        }

        static $count = 0;

        $values = array_values($values);

        $temp = array();
        $params = array();
        for ($i = 0; $i < count($values); $i++) {
            $key = self::PARAM_PREFIX . 'in_' . $count++;
            $temp[] = $key;
            $params[$key] = $values[$i];
        }

        return array(
            'conditions' => "[[$field]] IN (" . implode(', ', $temp) . ')',
            'params' => $params
        );
    }

    /**
     * 查询主键字段
     * @return array
     */
    protected function queryPrimaryKeyFields()
    {
        static $schemas = array();

        if (!array_key_exists($this->table, $schemas)) {
            $schemas[$this->table] = self::getConnection()->query('SHOW FULL FIELDS FROM ' . $this->table);
        }

        $primary = array();
        foreach ($schemas[$this->table] as $field) {
            if ($field['Key'] === 'PRI') {
                $primary[] = $field['Field'];
            }
        }
        return $primary;
    }

    /**
     * 统一占位符 如果同时存在问号和冒号，则将问号参数转为冒号
     * @param $sql
     * @return string
     */
    protected function replacePlaceholder($sql)
    {
        if (strpos($sql, '?') !== false && strpos($sql, ':') !== false) {
            $count = substr_count($sql, '?');
            for ($i = 0; $i < $count; $i++) {
                $sql = preg_replace('/\?/', self::PARAM_PREFIX . $i, $sql, 1);
                $this->params[self::PARAM_PREFIX . $i] = $this->params[$i];
                unset($this->params[$i]);
            }
        }
        return $sql;
    }

    /**
     * 检查是否为空:  null、''、空数组、空白字符("\t"、"\n"、"\r"等)
     * @param mixed $value
     * @return boolean
     */
    protected static function isEmpty($value)
    {
        return $value === '' || $value === array() || $value === null || is_string($value) && trim($value) === '';
    }

    protected function reset()
    {
        $this->orderBy = null;
        $this->limit = null;
        $this->offset = null;
        $this->condition = null;
        $this->params = array();
        $this->field = null;
    }
}