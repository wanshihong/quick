<?php
namespace Build\Model;

use Think\Exception;
use Think\Model;

class ReadyModel extends Model
{
    Protected $autoCheckFields = false;
    protected $createModule = ''; //创建到那个模块
    protected $message = '';//错误信息

    public function start($tableInfo)
    {
        try {
            //初始化校验
            if (!$checkRes = $this->check($tableInfo)) {
                throw new Exception($this->message);
            }
            //创建数据表
            $DB = new DBModel();
            if (!$DB->start($tableInfo)) {
                throw new Exception($DB->getMessage());
            }
            //创建模型
            $model = new ModelModel();
            if (!$model->start($tableInfo)) {
                throw new Exception($model->getMessage());
            }
            //创建控制器
            $controller = new ControllerModel();
            if(!$controller->start($tableInfo)){
                throw new Exception($controller->getMessage());
            }
            //创建视图
            $view = new ViewModel();
            if(!$view->start($tableInfo)){
                throw new Exception($view->getMessage());
            }
            return true;
        } catch (Exception $e) {
            $this->message = $e->getMessage();
            return false ;
        }
    }

    //检测参数
    public function check($tableInfo)
    {
        try {
            if (empty($tableInfo['name'])) {
                throw new Exception('表名称不能为空');
            }
            if (empty($tableInfo['module']) || !file_exists(APP_PATH . $tableInfo['module'])) {
                throw new Exception('请选择您要创建到那个模块');
            }
            if (empty($tableInfo['fields'])) {
                throw new Exception('您还没有定义字段信息');
            }
            return true;
        } catch (Exception $e) {
            $this->message = $e->getMessage();
            return false ;
        }

    }

    public function getMessage()
    {
        if (empty($this->message)) {
            return array('status' => 1, 'msg' => 'ok');
        } else {
            return array('status' => 0, 'msg' => $this->message);
        }
    }

}