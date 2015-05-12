<?php
namespace Build\Model;

use Think\Model;

class ViewModel extends Model
{
    Protected $autoCheckFields = false;
    protected $message = '';//错误信息

    public function start($info)
    {
        $controllerName = ucfirst($info['name']);
        $controllerName = replaceName($controllerName);
        $commit = $info['commit'];
        $moduleName = ucfirst($info['module']);
        $editStr = $this->editView($controllerName, $commit, $info['fields']);
        $listsStr = $this->listsView($controllerName, $commit, $info['fields']);
        $editFile = APP_PATH . $moduleName . '/View/' . $controllerName . '/edit.html';
        $listsFile = APP_PATH . $moduleName . '/View/' . $controllerName . '/index.html';
        $file = new FileModel();
        if (!$file->create($editFile, $editStr)) {
            $this->message = $file->getMessage();
            return false;
        }
        if (!$file->create($listsFile, $listsStr)) {
            $this->message = $file->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 创建输入框
     * @param $fields array 字段信息
     * @param $model string 模块名称，控制器名称
     * @return string 组合好的输入框
     */
    protected function inputs($fields, $model)
    {
        $str = '';
        foreach ($fields as $v) {
            if ($v['index'] == 'PRIMARY KEY') {
                $str .= "<input type='hidden' name='{$v['name']}' value='{??{$model}Data.{$v['name']}}' />";
            } elseif ($v['mapping']['field'] && $v['mapping']['mapping']) {
                $data = $this->relation($v['mapping']);
                if (empty($data)) {
                    $str .= "<Bootstrap:select name='{$v['name']}' label='{$v['viewCommit']}' value='{??{$model}Data.{$v['name']}}' />";
                } else {
                    $str .= "<Bootstrap:select name='{$v['name']}' label='{$v['viewCommit']}' value='{??{$model}Data.{$v['name']}}' data='{??{$data['data']}}' key='{$data['key']}' text='{$data['text']}' />";
                }
            } else {
                $str .= "<Bootstrap:{$v['input']} name='{$v['name']}' label='{$v['viewCommit']}' value='{??{$model}Data.{$v['name']}}' />";
            }
            $str .= "\n";
        }
        return $str;
    }

    //通过关联信息取得关联表，关联字段，关联显示字段
    public function relation($m)
    {
        $data = array();
        if (!empty($m)) {
            $data = array(
                'data' => $m['table'],
                'text' => $m['view'],
                'key' => $m['field']
            );
        }
        return $data;
    }

    //添加修改视图创建
    protected function editView($controllerName, $commit, $fields)
    {
        $inputs = $this->inputs($fields, $controllerName);
        $str = <<<EDIT
<extend name="./Public/baseBuild.html"/>
<block name="title">
    <title>管理中心-{??action}</title>
</block>
<block name="body">
    <div class="row clearfix">
        <div class="pull-left">
            <ol class="breadcrumb">
                <li><a href="{:U('Index/index')}">首页</a></li>
                <li><a href="{:U('{$controllerName}/index')}">{$commit}首页</a></li>
                <li class="active">{??action}</li>
            </ol>
        </div>
        <div class="pull-right">
            <a href="{:U('{$controllerName}/index')}" class="btn btn-default"><span class="glyphicon glyphicon-menu-left"></span>返回</a>
        </div>
    </div>
    <div class="container well well-lg">
        <taglib name="Bootstrap"/>
        <form class="form-horizontal" method="post" action="{:U('edit')}">
            {$inputs}
            <div class="form-group text-right">
                <button class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span>保存</button>
            </div>
        </form>
    </div>
</block>
EDIT;
        return str_replace('??', '$', $str);
    }

    //列表页面视图创建
    protected function listsView($controllerName, $commit, $fields)
    {
        $thead = $this->thead($fields);
        $tbody = $this->tbody($fields, $controllerName);
        $str = <<<LISTS
<extend name="./Public/baseBuild.html"/>
<block name="title">
    <title>管理中心-{$commit}首页</title>
</block>
<block name="body">
     <div class="row clearfix">
        <div class="pull-left">
            <ol class="breadcrumb">
                <li><a href="{:U('Index/index')}">首页</a></li>
                <li class="active">{$commit}首页</li>
            </ol>
        </div>
        <div class="pull-right">
            <a href="{:U('{$controllerName}/edit')}" class="btn btn-default"><span class="glyphicon glyphicon-menu-right"></span>添加{$commit}</a>
        </div>
    </div>
    <table class="table table-striped table-bordered table-hover table-condensed">
        <thead>
            {$thead}
        </thead>
        <tbody>
            {$tbody}
        </tbody>
    </table>
      <div class="modal fade" id="delColumn" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">您确定要删除吗？</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <a type="button" href="javascript:" class="btn btn-primary" id="delFix">确定</a>
                </div>
            </div>
        </div>
    </div>
</block>
<block name="script">
    <script>
        $('.del').click(function(){
            $('#delFix').attr('href',$(this).attr('delUrl'));
        });
    </script>
</block>
LISTS;
        return str_replace('??', '$', $str);
    }

    //生产thead
    private function thead($fields)
    {
        $str = '';
        $action = '';
        $mapping = '';
        foreach ($fields as $v) {
            if ($v['mapping']['mapping'] == "HAS_MANY") {
                $table_commit = getTableCommit($v['mapping']['table']);
                if (!empty($table_commit)) {
                    $mapping = '<th>' . $table_commit . '信息</th>';
                }
            }
            if ($v['index'] == 'PRIMARY KEY') {
                $num = '<th>ID</th>';
                $action = "<th>操作</th>";
            } else {
                $str .= "<th>{$v['viewCommit']}</th>\n";
            }
        }
        return $num . $str . $mapping . $action;
    }

    private function tbody($fields, $controllerName)
    {
        $foreach = "<foreach name='{$controllerName}Data' item='v' >\n<tr>\n";
        $str = '';
        $mapping = '';
        foreach ($fields as $v) {
            if ($v['mapping']['mapping'] == 'HAS_MANY') {
                $table_commit = getTableCommit($v['mapping']['table']);
                if (!empty($table_commit)) {
                    $url = "{:U('{$v['mapping']['table']}/index',array('{$v['mapping']['field']}'=>??v['{$v['name']}']))}";
                    $mapping = '<td><a href="' . $url . '" class="btn btn-link btn-sm">查看'.$table_commit.'</a></td>';
                }
            }
            if ($v['index'] == 'PRIMARY KEY') {
                $num = '<td>{??v.' . $v['name'] . '}</td>';
                $editUrl = '{:U("edit",array("' . $v["name"] . '"=>??v["' . $v["name"] . '"]))}';
                $delUrl = '{:U("del",array("' . $v["name"] . '"=>??v["' . $v["name"] . '"]))}';
                $action = "<td>
                                <a href='{$editUrl}' class='btn btn-default btn-xs' title='编辑'>
                                    <span class='glyphicon glyphicon-edit'></span>编辑
                                </a>
                                <a href='javascript:' delUrl='{$delUrl}' class='btn btn-danger btn-xs del' data-toggle='modal' data-target='#delColumn'>
                                    <span class='glyphicon glyphicon-trash'></span>删除
                                </a>
                            </td>";
            } elseif ($v['input'] == 'select') {
                $str .= "<td>{??v.{$v['mapping']['table']}.{$v['mapping']['view']}}</td>\n";
            } elseif ($v['mapping']['mapping'] == 'BELONGS_TO') {
                $str .= "<td>{??v.{$v['mapping']['table']}.{$v['mapping']['view']}}</td>\n";
            } else {
                $str .= "<td>{??v.{$v['name']}}</td>\n";
            }
        }
        return $foreach . $num . "\n" . $str . "\n" . $mapping . $action . "\n" . "</tr>\n</foreach>\n";
    }

    public function getMessage()
    {
        return $this->message;
    }

}