base.controller('fields', ['$scope', '$cookieStore', '$http', function ($scope, $cookieStore, $http) {
    $scope.modules = buildInfo.modules;//文件夹
    $scope.dbTables = buildInfo.tables; //数据库现有的表
    $scope.mappings = buildInfo.mappings; //关联关系
    $scope.inputs = buildInfo.inputs; //表单类型
    $scope.fiedlindexs = buildInfo.indexs;  //索引
    $scope.fiedlattrs = buildInfo.attrs;//属性
    $scope.fieldtypes = buildInfo.fieldsTypes; //字段类型
    $scope.table = {name: '', commit: '', module: '', fields: []};//数据格式初始化表信息
    $scope.tableField = {};//关联信息，当前表的字段信息
    $scope.openUrl = false ;//创建完成的新链接
    var table = $scope.table;//初始化完成记录原始数据，清空的时候赋值成这个数据

    //页面刷新信息保留
    if ($cookieStore.get('tableInfo') != undefined) {
        $scope.table = $cookieStore.get('tableInfo');
    }
    //监听字段信息变化记录cookie
    $scope.$watch('table', function (newValue, oldValue) {
        $cookieStore.put('tableInfo', $scope.table);
    }, true);

    //添加一个字段
    $scope.addField = function () {
        //每个字段所需的相关信息，添加字段时候,push到table.fields中
        var field = {
            viewCommit: '',//视图显示名称
            input: 'text',//输入类型
            name: '',//字段名称
            commit: '',//注释
            type: $scope.fieldtypes[6],//-字段类型--2个相同的对象并不相等，所以这里要调用声明的对象
            length: 10,//长度
            index: '',//索引
            default: '',//默认值
            null: false,//允许空值
            ai: false,//自增加
            attr: $scope.fiedlattrs[2],//属性，unsigned
            mapping: {table: '', field: '', view: '', mapping: ''},//关联信息
            validate: []//表单验证信息
        };
        $scope.table.fields.push(field);
    };
    //删除一个字段
    $scope.removeField = function (i) {
        $scope.table.fields.splice(i, 1);
    };

    //字段信息弹窗
    $scope.fieldIndex = 0;
    $scope.setField = function (i) {
        $scope.fieldIndex = i;
        //查找当前字段类型在所有字段类型中的索引
        var index = findIndex($scope.table.fields[i].type.name, $scope.fieldtypes);
        //赋值字段类型
        $scope.table.fields[i].type = $scope.fieldtypes[index];
    };

    //关联信息弹窗
    $scope.relationIndex = 0;
    $scope.setRelation = function (i) {
        $scope.relationIndex = i;
    };

    $scope.save = function () {
        $('#saveTable').addClass('disabled').html('<span class="glyphicon glyphicon-cog"></span>保存中...');
        $http({
            method: 'post',
            url: saveUrl,
            data: $scope.table
        }).success(function (res) {
            setTimeout(function () {
                $('#saveTable').removeClass('disabled').html('<span class="glyphicon glyphicon-ok"></span>完成');
            }, 1500);
            console.log(res.data);
            if (res.status == 1) {
                $scope.openUrl = res.url ;
                $scope.error = false ;
            }else{
                $scope.error = res.msg ;
                $scope.openUrl = false;
            }
        }).error(function (res) {
            console.log(res);
        })
    };
    //清空数据
    $scope.clear = function () {
        $scope.table = table;
        window.location.reload();
    };
    $scope.tableChange = function () {
        var mapping = $scope.table.fields[$scope.relationIndex].mapping;
        $scope.getTableField(mapping.table);//取得当前表的字段信息
    };

    $scope.getTableField = function (tableName) {
        $http({
            method: 'post',
            url: getFieldsUrl,
            data: {table: tableName}
        }).success(function (res) {
            console.log(res);
            if (res) {
                $scope.tableField = res;
            } else {
                alert('没有请求到数据，可能是表名错误');
            }
        }).error(function (res) {
            console.log(res);
            alert('没有请求到数据');
        });
    };
}]);

