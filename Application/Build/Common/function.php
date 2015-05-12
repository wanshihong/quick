<?php
/**
 * 查找字符串中的下划线，替换掉，并且把下划线后面的一个字母大写
 * @param $name
 * @return mixed
 */
function replaceName($name)
{
    $num = strpos($name, '_');
    $tmp = substr($name, $num + 1, 1);
    return str_replace('_' . $tmp, strtoupper($tmp), $name);
}

function connect(){
    $con = mysql_connect(C('DB_HOST'), C('DB_USER'), C('DB_PWD'));
    mysql_query('set names ' . C('DB_CHARSET'), $con);
    return $con ;
}

/**
 * 查询表字名称
 * @param $table string 表名称
 * @return array
 */
function getTableCommit($table)
{
    $con = connect();
    mysql_query('use information_schema', $con);
    $sql = 'select * from TABLES where TABLE_SCHEMA="' . C('DB_NAME') . '" and TABLE_NAME= "' . $table .'"';

    $res = mysql_query($sql, $con);
    $res = mysql_fetch_assoc($res);

    return $res['TABLE_COMMENT'];
}