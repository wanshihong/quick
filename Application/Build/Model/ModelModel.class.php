<?php
namespace Build\Model;

use Think\Model;

class ModelModel extends Model
{
    Protected $autoCheckFields = false;
    protected $message = '';//错误信息


    public function start($modelInfo)
    {
        $module = ucfirst($modelInfo['module']);
        $modelName = ucfirst($modelInfo['name']);
        $className = replaceName($modelName);//处理类名称
        $content = $this->content($module, $modelName, $modelInfo['commit'], $modelInfo['fields']);
        if ($content === false) {
            return false;
        }
        $path = APP_PATH . $module . '/Model/' . $className . 'Model.class.php';
        $file = new FileModel();
        if (!$file->create($path, $content)) {
            $this->message = $file->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 查找组织关联关系
     * @param $fields array 字段信息
     * @return array
     */
    protected function mapping($fields)
    {
        $mappings = array();
        foreach ($fields as $value) {
            $v = $value['mapping'];
            if (empty($v['field']) || empty($v['mapping'])) continue;
            switch ($v['mapping']) {
                case 'HAS_ONE' :
                    $mappings[$v['table']] = array(
                        'mapping_type' => "self::" . $v['mapping'],
                        'class_name' => $v['table'],
                        'foreign_key' => $v['field'],
                        'parent_key' => $value['name']
                    );
                    break;
                case 'BELONGS_TO':
                    $mappings[$v['table']] = array(
                        'mapping_type' => "self::" . $v['mapping'],
                        'class_name' => $v['table'],
                        'foreign_key' => $value['name'],
                        'parent_key' => $v['field']
                    );
                    break;
                case 'HAS_MANY':
                    $mappings[$v['table']] = array(
                        'mapping_type' => "self::" . $v['mapping'],
                        'class_name' => $v['table'],
                        'foreign_key' => $v['field'],
                        'parent_key' => $value['name']
                    );
                    break;
                case 'MANY_TO_MANY':
                    break;
                default :
                    $this->message = '没有' . $v['mapping'] . '这个关联关系';
                    return false;
                    break;
            }

        }
        return $mappings;
    }


    /**
     * 创建模型
     * @param $module string 模块,文件夹名称
     * @param $modelName string 模块名称类名称
     * @param $modelCommit string 模块注释
     * @param $fields array 字段信息
     * @return mixed
     */
    public function content($module, $modelName, $modelCommit, $fields)
    {
        $className = replaceName($modelName);//处理类名称
        $mapping = $this->mapping($fields);
        if ($mapping === false) {
            return false;
        }
        $mappingStr = '';
        $extend = 'Model';
        $use = '';
        $relationSelect = '';
        if (!empty($mapping)) {
            $extend = 'RelationModel';
            $use = 'use Think\Model\RelationModel;';
            $mappingStr = 'protected $_link = ' . var_export($mapping, true) . ';';
            $mappingStr = $this->_repeat($mappingStr);
            $relationSelect = '->relation(true)';
        }
        $str = <<<STR
<?php
namespace {$module}\Model;
use Think\Model;
{$use}
/**
 * {$modelCommit}基础数据模型
 * Class {$className}Model
 * @package {$module}\Model
 */
class {$className}Model extends {$extend}{

    protected ??tableName = '{$modelName}' ;

    {$mappingStr}
    /**
     * 基础数据查询
     * @param ??where array 查询条件
     * @return array
     */
    public function getAll(??where = array()){
        return ??this{$relationSelect}->where(??where)->select();
    }
     /**
     * 查询单条数据
     * @param ??where array||int 主键或者一个查询条件
     * @return array
     */
    public function getOne(??where){
        if(is_int(??where)){
            ??pk = ??this->getPk();
            ??where = array(??pk=>??where);
        }
        return ??this{$relationSelect}->where(??where)->find();
    }
}

STR;
        return str_replace('??', '$', $str);
    }

    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 带引号的关联关系，去掉引号
     * @param $str
     * @return string
     */
    private function _repeat($str)
    {
        $str = str_replace("'self::ONE_TO_ONE'", 'self::ONE_TO_ONE', $str);
        $str = str_replace("'self::HAS_ONE'", 'self::HAS_ONE', $str);
        $str = str_replace("'self::BELONGS_TO'", 'self::BELONGS_TO', $str);
        $str = str_replace("'self::ONE_TO_MANY'", 'self::ONE_TO_MANY', $str);
        $str = str_replace("'self::HAS_MANY'", 'self::HAS_MANY', $str);
        $str = str_replace("'self::MANY_TO_MANY'", 'self::MANY_TO_MANY', $str);
        return $str;
    }

}