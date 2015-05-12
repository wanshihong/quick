<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN" ng-app="build" >
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>快速构建后台</title>
    <link href="/quick_admin/Public/Build/bootstrap-3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .control-label{padding-right: 0;}
        button{letter-spacing:5px;}
    </style>
</head>
<body ng-controller="fields">

<div class="container well">
    <h3 class="text-center">创建</h3>

    <div class="row well-sm">
        <div class="col-xs-10">
            <div class="row form-horizontal">
                <div class="col-xs-4 form-group">
                    <label class="col-xs-3 control-label">表名称:</label>
                    <div class="col-xs-9">
                        <input type="text" class="form-control" ng-model="table.name" placeholder="数据库名称"/>
                    </div>
                </div>
                <div class="col-xs-4 form-group">
                    <label class="col-xs-3 control-label">表名称:</label>
                    <div class="col-xs-9">
                        <input type="text" class="form-control" ng-model="table.commit" placeholder="视图显示名称"/>
                    </div>
                </div>
                <div class="col-xs-4 form-group">
                    <label class="col-xs-4 control-label">创建到模块:</label>
                    <div class="col-xs-8">
                        <select ng-model="table.module" ng-options="module for module in modules"
                                class="form-control"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-2 text-right">
            <button class="btn btn-default" ng-click="addField()">
                <span class="glyphicon glyphicon-plus"></span>添加字段
            </button>
        </div>
    </div>
    <table class="table table-bordered table-responsive table-hover table-striped">
        <thead>
        <tr>
            <th width="50">序</th>
            <th>字段</th>
            <th>名称</th>
            <th>输入类型</th>
            <th>字段信息</th>
            <th>关联信息</th>
            <!--<th>表单验证</th>-->
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <tr ng-repeat="field in table.fields">
            <td>{{ $index+1 }}</td>
            <td><input type="text" class="form-control" ng-model="field.name" placeholder="数据库字段名称"/></td>
            <td><input type="text" class="form-control" ng-model="field.viewCommit" placeholder="视图显示名称"/></td>
            <td>
                <select ng-model="field.input" ng-options="input for input in inputs" class="form-control"></select>
            </td>
            <td>
                <button class="btn btn-link" data-toggle="modal" data-target="#addField" ng-click="setField($index)">
                    字段详情
                </button>
            </td>
            <td>
                <button ng-show="dbTables" class="btn btn-link" data-toggle="modal" data-target="#relation" ng-click="setRelation($index)">
                    关联信息
                </button>
                <code ng-hide="dbTables">数据库中还没有表</code>
            </td>
            <!--<td>表单验证</td>-->
            <td>
                <button class="btn btn-danger btn-sm" title="删除" ng-click="removeField($index)">
                    <span class="glyphicon glyphicon-trash"></span>
                </button>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="clearfix">
        <div class="pull-left">
            <code>*数据库字段名称为空会自动过滤</code>
        </div>
        <div class="pull-right">
            <a href="{{openUrl}}" target="_blank" ng-show="openUrl">点击打开:{{openUrl}}</a>
            <code ng-show="error">{{error}}</code>
            <button class="btn btn-danger" ng-click="clear()"><span class="glyphicon glyphicon-trash"></span>清空</button>
            <button class="btn btn-default" id="saveTable" ng-click="save()"><span class="glyphicon glyphicon-floppy-saved"></span>保存</button>
        </div>
    </div>
    <!-- 添加一个字段信息弹窗 -->
    <div class="modal fade" id="addField" tabindex="-1" role="dialog" aria-labelledby="addFileLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addFileLabel">设置 <code>{{table.fields[fieldIndex].name}}</code>的字段详情</h4>
            </div>
            <form name="addFile" class="form-horizontal" novalidate>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-xs-2 control-label">索引</label>
                        <div class="col-xs-10">
                            <select ng-model="table.fields[fieldIndex].index"
                                    ng-options="fiedlindex  for fiedlindex in fiedlindexs" class="form-control">
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label">字段类型</label>
                        <div class="col-xs-10">
                            <select ng-model="table.fields[fieldIndex].type"
                                    ng-options="type.name  group by type.shade for type in fieldtypes"
                                    class="form-control">
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label">字段长度</label>
                        <div class="col-xs-10">
                            <input type="number" ng-model="table.fields[fieldIndex].length" class="form-control"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label">默认值</label>
                        <div class="col-xs-10">
                            <input type="text" ng-model="table.fields[fieldIndex].default" class="form-control"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-2 control-label">属性</label>
                        <div class="col-xs-10">
                            <select ng-model="table.fields[fieldIndex].attr"
                                    ng-options="attr  for attr in fiedlattrs" class="form-control">
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-2 control-label">字段注释</label>
                        <div class="col-xs-10">
                            <input type="text" ng-model="table.fields[fieldIndex].commit" class="form-control"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-2 control-label"></label>
                        <div class="col-xs-10">
                            <label class="checkbox-inline">
                                <input type="checkbox" ng-model="table.fields[fieldIndex].null" value="true"> 空值
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" ng-model="table.fields[fieldIndex].ai" value="true"> 自增长
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- 添加一个字段信息弹窗 end -->

    <!-- 关联信息弹窗 -->
    <div class="modal fade" id="relation" tabindex="-1" role="dialog" aria-labelledby="relationLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="relationLabel">设置 <code>{{ table.fields[relationIndex].name }}</code>的关联信息
                </h4>
            </div>
            <form name="relation" class="form-horizontal" novalidate>
                <div class="modal-body">
                    <table class="table table-bordered table-responsive table-hover table-striped">
                        <tr>
                            <th>关联表</th>
                            <th>关联字段</th>
                            <th>关联显示字段</th>
                            <th>关联关系</th>
                        </tr>
                        <tr>
                            <td>
                                <select ng-model="table.fields[relationIndex].mapping.table"
                                        class="form-control mapping"
                                        ng-options="table for table in dbTables"
                                        ng-change="tableChange()">
                                </select>
                            </td>
                            <td>
                                <select ng-model="table.fields[relationIndex].mapping.field"
                                        class="form-control mapping"
                                        ng-options="table for table in tableField">
                                </select>
                            </td>
                            <td>
                                <select ng-model="table.fields[relationIndex].mapping.view"
                                        class="form-control mapping"
                                        ng-options="table for table in tableField">
                                </select>
                            </td>
                            <td>
                                <select ng-model="table.fields[relationIndex].mapping.mapping" class="form-control"
                                        ng-options="mapping for mapping in mappings">
                                </select>
                            </td>
                        </tr>
                    </table>
                    <div class="well">
                        关联关系说明：
                        <a target="_blank" href="http://document.thinkphp.cn/manual_3_2.html#relation_model">
                            http://document.thinkphp.cn/manual_3_2.html#relation_model
                        </a>
                    </div>
                    <code>注：关联字段、关联关系同时存在才会创建相关代码。关联显示用于添加时候，下拉框选择显示！</code>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- 关联信息弹窗 end -->

</div>


<script src="/quick_admin/Public/Build/jquery.1.11.2.js"></script>
<script src="/quick_admin/Public/Build/bootstrap-3.3.4/js/bootstrap.min.js"></script>
<script src="/quick_admin/Public/Build/angular-1.3.0/angular.min.js"></script>
<script src="/quick_admin/Public/Build/angular-1.3.0/angular-cookies.min.js"></script>
<script src="/quick_admin/Public/Build/fun.js"></script>
<script src="/quick_admin/Public/Build/index.js"></script>
<script>
    var buildInfo = jQuery.parseJSON( '<?php echo ($buildInfo); ?>' );
    var saveUrl = '<?php echo U("Index/build");?>';
    var getFieldsUrl = '<?php echo U("Index/getFields");?>'
</script>
</body>
</html>