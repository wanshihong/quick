<?php
namespace Build\Model;

use Think\Db;
use Think\Exception;
use Think\Model;

class DBModel extends Model
{
    Protected $autoCheckFields = false;
    private $message = '';//错误信息

    public function start($table)
    {
        $name = $table['name'];
        $commit = $table['commit'];
        $fields = $table['fields'];
        try {
            //字段信息验证
            if (!$this->checkField($fields)) throw new Exception('字段信息验证失败');
            //生成SQL
            $sql = $this->createSQL($name, $fields, $commit);
            //执行SQL,创建一张表
            if (!$this->execSql($sql, $name)) throw new Exception($this->message);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    //字段验证
    public function checkField($fields)
    {
        $primary = array();

        foreach ($fields as $k => $v) {
            if (empty($fields[$k]['name'])) {
                unset($fields[$k]);
            }
            try {
                if ($v['index'] == 'PRIMARY KEY') {
                    array_push($primary, $v['name']);
                }
                if (count($primary) > 1) {
                    throw new Exception($primary[0] . '已经是主键了' . $primary[1] . '不能再是主键');
                }

                if ($v['ai'] == 'true' && $v['index'] != 'PRIMARY KEY') {
                    throw new Exception($v['name'] . '不是主键，AI(自增长不能为真)');
                }
                if (in_array($v['type'], C('NUM')) && !is_numeric($v['default'])) {
                    throw new Exception($v['name'] . '是数字类型，默认值也类型必须是数字类型');
                }
                if (in_array($v['type'], C('STRING')) && !is_string($v['default'])) {
                    throw new Exception($v['name'] . '是字符串类型，默认值也类型必须是字符串类型');
                }

                if (($v['attr'] == 'UNSIGNED' || $v['attr'] == 'UNSIGNED ZEROFILL') && !in_array($v['type']['name'], C('NUM'))) {
                    throw new Exception($v['name'] . '的属性是（' . $v['attr'] . '），字段类型必须是数字类型（NUMBER）');
                }
                if ($v['attr'] == 'ON UPDATE CURRENT_TIMESAMP' && $v['type'] != 'TIMESTAMP') {
                    throw new Exception($v['name'] . '的属性是（' . $v['attr'] . '），字段类型必须是 TIMESTAMP');
                }
            } catch (Exception $e) {
                $this->message = $e->getMessage();
                return false;
            }
        }
        if (empty($fields)) {
            $this->message = '您的字段名称为空';
            return false;
        }

        return true;
    }

    /**
     * 生成SQL语句
     * @param $tName string 表名称
     * @param $fields array 字段信息
     * @param $commit string 表注释
     * @return string
     */
    public function createSQL($tName, $fields, $commit)
    {
        $sql = 'CREATE TABLE' . ' `' . $tName . '` (';
        // id int(11) unsigned not null default 0 commit '主键'
        $SQL = '`%name%` %type%(%length%) %attr% %is_null% %AUTO% %default% COMMENT "%commit%", ';
        //循环字段
        foreach ($fields as $v) {
            $tmpSql = $SQL;
            $type = $v['type']['name'];//字段类型
            $attr = empty($v['attr']) ? '' : $v['attr'];//字段属性
            $null = $v['null'] == 'false' ? 'NOT NULL' : '';//是否允许为空
            $ai = $v['ai'] == 'false' ? '' : 'AUTO_INCREMENT';//自增长
            if (!empty($ai)) {//自增长不用默认值
                $default = '';
            } else {
                $default = in_array($type, C('NUM')) ? 'DEFAULT ' . (int)$v['default'] : 'DEFAULT "' . (string)$v['default'] . '"';//字段默认值
            }
            $fieldCommit = empty($v['commit']) ? '' : $v['commit'];//字段注释
            $nowSql = str_replace('%name%', $v['name'], $tmpSql);
            $nowSql = str_replace('%type%', $type, $nowSql);
            $nowSql = str_replace('%length%', $v['length'], $nowSql);
            $nowSql = str_replace('%attr%', $attr, $nowSql);
            $nowSql = str_replace('%is_null%', $null, $nowSql);
            $nowSql = str_replace('%AUTO%', $ai, $nowSql);
            $nowSql = str_replace('%default%', $default, $nowSql);
            $nowSql = str_replace('%commit%', $fieldCommit, $nowSql);
            $sql .= $nowSql;
        }
        //循环索引
        foreach ($fields as $v) {
            if (isset($v['mapping']) && !empty($v['mapping']) && empty($v['index'])) {
                $sql .= 'KEY `' . $v['name'] . '` (`' . $v['name'] . '`), ';
            }
            if (empty($v['index'])) {
                continue;
            }
            $key = $v['index'];
            if ($key == 'PRIMARY KEY') {
                $sql .= $key . ' (`' . $v['name'] . '`), ';
            } else {
                $sql .= $key . ' `' . $v['name'] . '` (`' . $v['name'] . '`), ';
            }
        }
        $sql = trim($sql);
        $sql = rtrim($sql, ',');
        $sql .= ')ENGINE=INNODB CHARSET=utf8 COMMENT=' . '"' . $commit . '"';
        return $sql;
    }

    //执行SQL
    public function execSql($sql, $table)
    {
        $con = connect();
        if (!$con) {
            $this->message = mysql_error();
        }
        mysql_query('use ' . C('DB_NAME'), $con);
        mysql_query("DROP TABLE IF EXISTS {$table}");
        $res = mysql_query($sql);
        if (empty($res)) {
            $this->message = '[error]:' . mysql_error() . '[sql]:' . $sql;
            return false;
        }
        return true;
    }

    public function getMessage()
    {
        return $this->message;
    }

}