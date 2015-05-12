<?php
namespace Build\Model;

use Think\Model;

class ControllerModel extends Model
{
    Protected $autoCheckFields = false;
    protected $message = '';//错误信息
    private $has_many = '';//HAS_MANY 关联表的表名称



    public function start($info)
    {
        $controllerName = ucfirst($info['name']);
        $controllerName = replaceName($controllerName) ;
        $commit = $info['commit'];
        $module = ucfirst($info['module']);
        $content = $this->content($module, $controllerName, $commit, $info['fields']);
        $path = APP_PATH . $module . '/Controller/' . $controllerName . 'Controller.class.php';
        $file = new FileModel();
        if (!$file->create($path, $content)) {
            $this->message = $file->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 查找关联关系，生成编辑页面需要的关联数据查询
     * @param $fields
     * @return string
     */
    protected function relation($fields)
    {
        $str = '';
        foreach ($fields as $v) {
            $m = $v['mapping'];
            if (empty($m['field']) || empty($m['mapping'])) continue;
            if($m['mapping']=='HAS_MANY'){//记录has_many参数
                $this->has_many = $m ;
            }
            $str .= "??{$m['table']} = M('{$m['table']}')->field('{$m['field']},{$m['view']}')->select();\n";
            $str .= "??this->assign('{$m['table']}',??{$m['table']});\n";
        }
        return $str;
    }

    /**
     * 如果存在关联查询的时候替换指定controller中首页的查询语句
     * @param $module string 模块名称
     */
    public function replaceControllerIndexSelect($module)
    {
        $mapping = $this->has_many ;
        $field = $mapping['field'] ;
        $table = ucfirst($mapping['table']);
        $replace = '$'.$field .' = I("'.$field.'",0);' ."\n" . '$where = empty($'.$field .') ? array() : array("'.$field.'"=>$'.$field.');' ."\n" .'$' .$table . 'Data = $' . $table . '->getAll($where);'  ;
        $file = APP_PATH . $module . '/Controller/' . $table . 'Controller.class.php';
        $con = file_get_contents($file) ;
        //$BaknData = $Bakn->getAll();
        $search = '$' .$table . 'Data = $' . $table . '->getAll();'  ;
        $con = str_replace($search,$replace,$con);
        file_put_contents($file,$con);
    }

    //创建控制器字符串
    public function content($module, $controllerName, $commit, $fields)
    {
        //取得关联查询语句
        $relationData = $this->relation($fields);
        //首页查询调休

        if($this->has_many){//如果存在has_many关联关系
            $this->replaceControllerIndexSelect($module);
        }
        $str = <<<STR
<?php
namespace {$module}\Controller;
use Think\Controller;

class {$controllerName}Controller extends Controller{

    public function index(){
        /* @var ??{$controllerName} \\{$module}\\Model\\{$controllerName}Model */
        ??{$controllerName} = D('{$controllerName}');
        ??{$controllerName}Data = ??{$controllerName}->getAll();
        ??this->assign('{$controllerName}Data',??{$controllerName}Data);
        ??this->display();
    }
    /**
     * 获取主键的值
     * @param ??model \\{$module}\\Model\\{$controllerName}Model
     * @return int||false 主键的值或者假
     */
    private function _get_pk(??model){
        ??pkName = ??model->getPk();
        ??pk = I(??pkName,0,'int');
        return empty(??pk) ? false : ??pk;
    }
    /**
     * 保存数据
     * @param ??model \\{$module}\\Model\\{$controllerName}Model
     * @param ??pk string 主键(为真表示为修改)
     * @return mixed
     */
    private function _save(??model,??pk){
        if(!??model->create()){
            ??this->error(??model->getError());
        }
        if(??pk && ??model->find(??pk)){
            ??result = ??model->save();
        }else{
            ??result = ??model->add();
        }
        return ??result;
    }

    public function edit(){
         /* @var ??{$controllerName} \\{$module}\\Model\\{$controllerName}Model */
        ??{$controllerName} = D('{$controllerName}');
        ??pk = ??this->_get_pk(??{$controllerName});
        ??action = empty(??pk) ? '新增' : '修改' ;
        ??action .= '{$commit}' ;
        if(IS_POST){
             ??result = ??this->_save(??{$controllerName},??pk) ;
             if(??result===0){
                ??this->error('没有任何数据被修改!');
             }elseif(??result===false){
                ??this->error(??action . '失败');
             }else{
                ??this->success(??action . '成功',U('index'));
             }
        }else{
            if(!empty(??pk)){
                ??{$controllerName}Data = ??{$controllerName}->getOne(??pk);
                ??this->assign('{$controllerName}Data',??{$controllerName}Data);
            }
            {$relationData}
            ??this->assign('action',??action);
            ??this->display();
        }
    }

    public function del(){
        /* @var ??{$controllerName} \\{$module}\\Model\\{$controllerName}Model */
        ??{$controllerName} = D('{$controllerName}');
        ??pkName = ??{$controllerName}->getPk();
        ??pk = I(??pkName,0,'int');
        if(empty(??pk)){
            E('参数错误');
        }
        if(??{$controllerName}->where(array(??pkName=>??pk))->delete()){
            ??this->success('删除{$commit}成功');
        }else{
             ??this->success('删除{$commit}失败');
        }
    }

}


STR;
        return str_replace('??', '$', $str);
    }

    public function getMessage()
    {
        return $this->message;
    }

}