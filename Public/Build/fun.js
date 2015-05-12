angular.module('setHttp', [], function ($httpProvider) {
    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    /**
     * The workhorse; converts an object to x-www-form-urlencoded serialization.
     * @param {Object} obj
     * @return {String}
     */
    var param = function (obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;
        for (name in obj) {
            value = obj[name];
            if (value instanceof Array) {
                for (i = 0; i < value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value instanceof Object) {
                for (subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function (data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
});
var base = angular.module('build', ['ngCookies', 'setHttp'])
//重复值验证，调用格式 repeatpwd=“formName.inputName” 【repeatpwd=“表单name.输入框name”】
    .directive('repeatval', function () {
        return {
            require: 'ngModel',
            link: function (scope, elm, attrs, ctrl) {
                var tageCtrl = scope.$eval(attrs.repeatval);
                tageCtrl.$parsers.push(function (viewValue) {
                    ctrl.$setValidity('repeatval', viewValue == ctrl.$viewValue);
                    return viewValue;
                });
                ctrl.$parsers.push(function (viewValue) {
                    if (viewValue == tageCtrl.$viewValue) {
                        ctrl.$setValidity('repeatval', true);
                        return viewValue;
                    } else {
                        ctrl.$setValidity('repeatval', false);
                        return undefined;
                    }
                });
            }
        };
    })
    //表单验证指令，调用格式 form-check="obj" [obj 是 $scope下面的一个对象]
    .directive('formCheck', function (form, $timeout) {
        return {
            link: function (scope, element, attrs) {
                var info = {};//存放验证信息
                //验证属性配置
                var validate = ['required', 'ng-required', 'ng-minlength', 'ng-maxlength', 'pattern', 'ng-pattern', 'repeatval', 'min', 'max'];
                var not_types = ['button', 'hidden', 'submit', 'reset'];
                //循环记录验证属性
                angular.forEach(element[0], function (item) {
                    var i_name = _$(item)[0].localName;//输入框标签名称
                    var i_type = _$(item).attr('type');//输入框type
                    if ($.inArray(i_type, not_types) != -1 || i_name == 'button') return; //按钮隐藏域跳过
                    //循环验证属性，取得设置的验证值
                    var tmp = {};
                    for (var i = 0, len = validate.length; i < len; i++) {
                        var attr_value = _$(item).attr(validate[i]);
                        if (attr_value == undefined || attr_value == 'undefined') continue;
                        //分割验证属性ng-minlength 变成 minlength
                        var check_type = validate[i].split('-');
                        check_type = (check_type.length > 1) ? check_type[1] : check_type[0];
                        tmp[check_type] = attr_value;
                    }
                    tmp['name'] = _$(item).parent('div.form-group').find('label').text();
                    info[_$(item).attr('name')] = tmp;
                });
                var formName = attrs.name;//表单Name
                //监听表单对象，启动验证
                scope.$watch(attrs.formCheck, function (newValue, oldValue) {
                    if (newValue == oldValue) return false;
                    scope.info = form.check(info, scope[formName]);
                }, true);
                console.log('如果写了验证规则，不起作用，请检查是否给表单赋值了ng-model,设置格式为 [obj.input]。label不边颜色，请查看label是否加了control-label');
            }
        }
    })
    .factory('form', function ($timeout) {
        return {
            //验证的结果
            result: {},
            /**
             * 根据验证状态返回表单颜色className
             * @param obj 当前验证的表单对象
             * @returns {string}
             */
            color: function (obj) {
                if (!obj.$pristine) {
                    return obj.$valid == true ? 'has-success' : 'has-error';
                } else {
                    return 'has-warning';
                }
            },
            /**
             * 根据验证状态返回表单图标className
             * @param className
             * @returns {string}
             */
            icon: function (className) {
                var obj = {
                    'has-warning': 'glyphicon-warning-sign',
                    'has-success': 'glyphicon-ok',
                    'has-error': 'glyphicon-remove'
                };
                return obj[className];
            },
            message: {
                'required': '请输入%name%',
                'minlength': '请输入最少 %num% 个字符的%name%',
                'maxlength': '请输入最多 %num% 个字符的%name%',
                'pattern': '请输入正确格式的%name%',
                'repeatval': '同  %target% 输入不一致',
                'email': '请输入正确的邮箱格式',
                'number': '%name%请输入数字',
                'min': '%name%不能小于 %num% ',
                'max': '%name%不能大于 %num% '
            },
            /**
             * 根据一个表单刚才输入的值，返回错误信息，
             * @param input 当前验证的表单状态对象
             * @param inputName 当前验证的表单name
             * @returns {string}
             */
            getMsg: function (input, inputName) {
                for (var checkType in input) {
                    if (input[checkType]) {
                        var i = this.result[inputName];
                        var msg = this.message[checkType];
                        var tmp = msg.replace(/%num%/, i[checkType]);
                        if (checkType == 'repeatval') {
                            var formText = this.result[i[checkType].split('.')[1]].name;
                            tmp = tmp.replace(/%target%/, formText);
                        }
                        return tmp.replace(/%name%/, i['name']);
                    }
                }
                return '';
            },
            /**
             * 根据验证状态返回表单图标className
             * @param className
             * @returns {string}
             */
            getMsgColor: function (className) {
                var obj = {
                    'has-warning': 'text-warning',
                    'has-success': 'text-success',
                    'has-error': 'text-danger'
                };
                return obj[className];
            },
            /**
             * 表单验证
             * @param info 页面需要验证的表单信息
             * @param form angular 表单验证对象
             * @returns Object
             */
            check: function (info, form) {
                this.result = info;
                for (var iName in info) {
                    if (iName == 'undefined' || iName == undefined) continue;
                    if (form[iName] == undefined || form[iName] == 'undefined') continue;
                    this.result[iName]['color'] = this.color(form[iName]);
                    this.result[iName]['icon'] = this.icon(this.result[iName]['color']);
                    this.result[iName]['msg'] = this.getMsg(form[iName].$error, iName);
                    this.result[iName]['msgColor'] = this.getMsgColor(this.result[iName]['color']);
                }
                return this.result;
            }
        };
    });

/**
 * 模拟jq查找DOM
 * @param obj
 * @returns {*|Object}
 * @private
 */
function _$(obj) {
    return angular.element(obj);
}
/**
 * 从数组中查找索引
 * @param value
 * @param attr
 * @returns {number}
 */
function findIndex(value, attr) {
    for (var i = 0, len = attr.length; i < len; i++) {
        if(attr[i].name==value){
            return i ;
        }
    }
    return -1 ;
}

