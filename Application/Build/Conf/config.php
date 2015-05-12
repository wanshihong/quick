<?php
return array(
    //'配置项'=>'配置值'
    'DB_TYPE' => 'mysql',     // 数据库类型
    'DB_HOST' => 'localhost', // 服务器地址
    'DB_NAME' => 'test',          // 数据库名
    'DB_USER' => 'root',      // 用户名
    'DB_PWD' => 'root',          // 密码

    //数据库字段类型配置
    'STRING' => array('CHAR', 'VARCHAR', 'TEXT', 'TINYTEXT'),//字符串
    'NUM' => array('TINYINT', 'SMALLINT', 'INT', 'DECIMAL', 'FLOAT'),//数字
    'TIME' => array('DATE', 'TIME', 'YEAR', 'DATETIME', 'TIMESTAMP'),//时间
    //关联关系类型
    //'MAPPINGS'=>array('', 'HAS_ONE', 'BELONGS_TO', 'HAS_MANY', 'MANY_TO_MANY'),//many_to_many比较复杂，暂时不考虑
    'MAPPINGS' => array('', 'HAS_ONE', 'BELONGS_TO', 'HAS_MANY'),
    //输入类型
    'INPUTS' => array('text', 'select', 'password', 'file', 'checkbox', 'radio', 'email', 'url', 'number', 'range', 'search', 'color'),
    //索引
    'INDEXS' => array('', 'PRIMARY KEY', 'UNIQUE KEY', 'KEY'),
    //数据库字段属性
    'ATTRS' => array('', 'BINARY', 'UNSIGNED', 'UNSIGNED ZEROFILL', 'ON UPDATE CURRENT_TIMESAMP'),


);