<?php
namespace Build\Controller;

use Build\Model\ReadyModel;
use Think\Controller;
use Think\Model;

class IndexController extends Controller
{
    //开始构建后台
    public function build()
    {
        $table = I('post.');
        foreach($table['fields'] as $k=>$v){
            if(empty($v['name'])){
                unset($table['fields'][$k]) ;
            }
        }
        $cModel = new ReadyModel();
        if (!$cModel->start($table)) {
            $this->ajaxReturn($cModel->getMessage());
        }

        $this->ajaxReturn(array(
            'status' => 1,
            'data' => $table,
            'url' => U($table['module'] . '/' . replaceName($table['name']) . '/index')
        ));
    }

    public function index()
    {
        $modules = $this->getModule();//查询当前项目有那些模块
        $tables = $this->getTables();//查询当前数据库有那些表
        $fieldsTypes = $this->getFieldType();
        $buildInfo = array(
            'modules' => $modules,
            'tables' => $tables,
            'fieldsTypes' => $fieldsTypes,
            'mappings' => C('MAPPINGS'),
            'inputs' => C('INPUTS'),
            'indexs' => C('INDEXS'),
            'attrs' => C('ATTRS'),
        );
        $this->assign('buildInfo', json_encode($buildInfo));
        $this->display();
    }

    //查询项目有那些模块
    public function getModule()
    {
        $modules = array();
        $commonPathArr = explode('/', COMMON_PATH);
        $commonPath = $commonPathArr[2];
        $notin = array($commonPath, MODULE_NAME, '.', '..', 'Runtime');
        if ($dh = opendir(APP_PATH)) {
            while (($file = readdir($dh)) !== false) {
                if (filetype(APP_PATH . $file) == 'dir' && !in_array($file, $notin)) {
                    array_push($modules, $file);
                }
            }
            closedir($dh);
        }
        return $modules;
    }

    //查询数据库里面有那些表
    public function getTables()
    {
        $model = new Model();
        $tables = $model->query('show tables');
        $return = array();
        foreach ($tables as $v) {
            array_push($return, $v['tables_in_' . C('DB_NAME')]);
        }
        return $return;
    }

    /**
     * 异步取得表字段
     * @param $table string 表名称
     * @return array
     */
    public function getFields()
    {
        $table = I('table', '');
        if (empty($table)) {
            $this->ajaxReturn(0);
        }
        $fields = M($table)->query('show columns from ' . $table);
        $return = array();
        foreach ($fields as $v) {
            $return[] = $v['field'];
        }
        $this->ajaxReturn($return);
    }

    public function getFieldType()
    {
        $fieldsType = array_merge(C('STRING'), C('NUM'), C('TIME'));
        $return = array();
        foreach ($fieldsType as $v) {
            if (in_array($v, C('STRING'))) {
                $return[] = array('name' => $v, 'shade' => 'string');
            }
            if (in_array($v, C('NUM'))) {
                $return[] = array('name' => $v, 'shade' => 'number');
            }
            if (in_array($v, C('TIME'))) {
                $return[] = array('name' => $v, 'shade' => 'date&time');
            }
        }
        return $return;
    }

}