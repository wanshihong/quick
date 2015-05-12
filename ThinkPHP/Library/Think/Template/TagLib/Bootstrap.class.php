<?php
namespace Think\Template\TagLib;

use Think\Template\TagLib;

class Bootstrap extends TagLib
{

    protected $tags = array(
        /**
         * id 自定义ID，不定义默认（input+表单name）
         * class 自定义class
         * help 表单的输入说明 显示再表单下方
         * status 当前表单的初始化状态，可选值 ：success,error,warning 三个值选其一
         * inline 内联表单
         * horizontal 水平排列的表单【说明：两个数字，逗号分割,实用这个排列方式需要在表单上面添加 form-horizontal】【示例:horizontal="3,9"】
         */
        'text' => array('attr' => array('id', 'class', 'name', 'value', 'label', 'placeholder', 'help', 'status', 'inline', 'horizontal'), 'close' => 0),
        'password' => array('attr' => array('id', 'class', 'name', 'label', 'placeholder', 'help', 'status', 'inline', 'horizontal'), 'close' => 0),
        'number' => array('attr' => array('id', 'class', 'name', 'value', 'label', 'placeholder', 'help', 'status', 'inline', 'horizontal'), 'close' => 0),
        'email' => array('attr' => array('id', 'class', 'name', 'value', 'label', 'placeholder', 'help', 'status', 'inline', 'horizontal'), 'close' => 0),
        'url' => array('attr' => array('id', 'class', 'name', 'value', 'label', 'placeholder', 'help', 'status', 'inline', 'horizontal'), 'close' => 0),
        'file' => array('attr' => array('id', 'class', 'name', 'label', 'help', 'inline', 'horizontal'), 'close' => 0),
        'checkbox' => array('attr' => array('id', 'class', 'name', 'label', 'inline', 'disabled', 'checked', 'default'), 'close' => 0),
        'radio' => array('attr' => array('id', 'class', 'name', 'label', 'inline', 'disabled', 'checked', 'default'), 'close' => 0),
        /**
         * key : value值字段名称，默认id
         * text : 显示值字段名称,默认name
         */
        'textarea' => array('attr' => array('id', 'class', 'name', 'label', 'value', 'label', 'rows', 'key', 'text', 'horizontal'), 'close' => 0),
        'select' => array('attr' => array('id', 'class', 'name', 'label', 'data', 'value', 'key', 'horizontal'), 'close' => 0)
    );
    private $ids = array();//自动生产ID保存，防止重复

    /**
     * @param $id string input的ID
     * @param $name string input的name值
     * @return string
     */
    protected function setId($id, $name)
    {
        return empty($id) ? 'input' . ucfirst($name) . mt_rand(1, 9999) : $id;
    }

    /**
     * 根据表单的状态返回图标和状态
     * @param $status 表单当前的状态
     * @return array()
     */
    protected function inputStatus($attr)
    {
        switch ($attr['status']) {
            case 'success':
                $icon = '<span class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>';
                $class = 'has-success';
                break;
            case 'error':
                $icon = '<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>';
                $class = 'has-error';
                break;
            case 'warning':
                $icon = '<span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>';
                $class = 'has-warning';
                break;
            default:
                $icon = '';
                $class = '';
                break;
        }
        $icon = empty($icon) ? $icon : $icon . '<span id="' . $attr['id'] . 'Status" class="sr-only">(' . $attr['status'] . ')</span>';
        return array('statusClass' => $class, 'icon' => $icon);
    }

    protected function wrapDiv($attr)
    {
        $class = array();
        if (!empty($attr['inline'])) array_push($class, 'form-inline');
        if (!empty($attr['statusClass'])) array_push($class, $attr['statusClass']);
        if (!empty($attr['has_feedback'])) array_push($class, $attr['has_feedback']);
        if (!empty($attr['class'])) array_push($class, $attr['class']);
        if (empty($attr['typeClass'])) {
            //输入类型
            array_push($class, 'form-group');
        } else {
            //选择类型  checkbox radio
            array_push($class, $attr['typeClass']);
        }
        return '<div class="' . implode(' ', $class) . '">';
    }

    protected function wrapLabel($attr)
    {
        $id = $attr['id'];
        $width = '';
        if (array_key_exists('horizontal', $attr) && !empty($attr['horizontal'])) {
            $width = 'col-xs-' . $attr['horizontal'][0];
        }
        $typeClass = empty($attr['typeClass']) ? 'control-label' : $attr['typeClass'];
        return '<label class="' . $typeClass . ' ' . $width . '" for="' . $id . '">';
    }

    protected function input($attr)
    {
        $attrs = array();
        if (!empty($attr['placeholder'])) array_push($attrs, $attr['placeholder']);//输入框描述
        if (!empty($attr['describedby'])) array_push($attrs, $attr['describedby']);//输入框与图标关联，
        if (empty($attr['typeClass'])) {
            //没有这个类型为输入框类型 text/password/email....
            array_push($attrs, 'class="form-control"');//calss 属性
            if (!empty($attr['value'])) array_push($attrs, 'value=' . $attr['value']);//默认值
        } else {
            //选择类型 checkbox/radio
            if (!empty($attr['default'])) {
                $default = $this->_trim($attr['default']);
                $default = explode('.', $default);
                $default = $default[0] . "['" . $default[1] . "']";
                $checked = '<?php if(' . $default . ' == ' . $attr['value'] . '){ echo "checked" ; } ?>';
            } else {
                $checked = empty($attr['checked']) ? '' : 'checked';
            }
            if (!empty($attr['disabled'])) array_push($attrs, 'disabled');//是否禁用
            array_push($attrs, $checked);//默认值判断
        }
        $input = "<input type='{$attr['type']}' name='{$attr['name']}' id='{$attr['id']}' " . implode(' ', $attrs) . " >";
        return $input . $attr['icon'] . $attr['help'];
    }

    /**
     * 输入框默认表单赋值
     * @param $attr
     */
    protected function set_input_attr($attr)
    {
        $attr['id'] = $this->setId($attr['id'], $attr['name']);
        $status = $this->inputStatus($attr);
        $attr['statusClass'] = $status['statusClass'];//wrapDiv上面显示
        $attr['icon'] = $status['icon'];//wrapLabel上面显示
        $attr['has_feedback'] = empty($status['icon']) ? '' : 'has-feedback';//wrapDiv上面显示
        $attr['describedby'] = empty($status['icon']) ? '' : 'aria-describedby="' . $attr['id'] . 'Status"';//表单上关联图标
        $attr['help'] = empty($attr['help']) ? '' : '<span class="help-block">' . $attr['help'] . '</span>';
        $attr['placeholder'] = empty($attr['placeholder']) ? '' : 'placeholder="' . $attr['placeholder'] . '"';
        if (array_key_exists('horizontal', $attr) && !empty($attr['horizontal'])) {
            $attr['horizontal'] = explode(',', $attr['horizontal']);
        }
        return $attr;
    }

    public function _text($attr)
    {
        $attr['type'] = 'text';
        return $this->inputClass($attr);
    }

    public function _password($attr)
    {
        $attr['type'] = 'password';
        return $this->inputClass($attr);
    }

    public function _email($attr)
    {
        $attr['type'] = 'email';
        return $this->inputClass($attr);
    }

    public function _number($attr)
    {
        $attr['type'] = 'number';
        return $this->inputClass($attr);
    }

    public function _url($attr)
    {
        $attr['type'] = 'url';
        return $this->inputClass($attr);
    }

    public function _file($attr)
    {
        $attr['type'] = 'file';
        return $this->inputClass($attr);
    }

    public function _checkbox($attr)
    {
        $attr['typeClass'] = empty($attr['inline']) ? 'checkbox' : 'checkbox-inline';
        $attr['type'] = 'checkbox';
        return $this->checkClass($attr);
    }

    public function _radio($attr)
    {
        $attr['typeClass'] = empty($attr['inline']) ? 'radio' : 'radio-inline';
        $attr['type'] = 'radio';
        return $this->checkClass($attr);
    }

    public function _textarea($attr)
    {
        $id = $this->setId($attr['id'], $attr['name']);
        $textarea = '<textarea class="form-control ' . $attr['class'] . '" id="' . $id . '" name="' . $attr['name'] . '" rows="' . $attr['rows'] . '">' . $attr['value'] . '</textarea>';
        return $this->_return($attr, $textarea);
    }

    //选择类型表单
    protected function checkClass($attr)
    {
        $attr = $this->set_input_attr($attr);
        $input = $this->input($attr);
        $div = $this->wrapDiv($attr);
        $label = $this->wrapLabel($attr);
        return $div . $label . $input . $attr['label'] . '</label></div>';
    }

    //输出输入类型表单
    protected function inputClass($attr)
    {
        $input = $this->input($attr);
        return $this->_return($attr, $input);
    }

    //去除左右花括号
    private function _trim($field)
    {
        $field = ltrim($field, '{');
        return rtrim($field, '}');
    }

    public function _select($attr)
    {
        $data = $this->_trim($attr['data']);
        $key = empty($attr['key']) ? 'id' : $attr['key'];
        $text = empty($attr['text']) ? 'name' : $attr['text'];
        $value = $attr['value'];
        $id = $this->setId($attr['id'], $attr['name']);
        if (strpos($value, '{') !== false) {
            $value = $this->_trim($value);
        }
        $select = '<select id="' . $id . '" name="' . $attr['name'] . '" class="form-control">';
        if(empty($data)){
            $select .= '<option>您还没有传入data属性</option>';
        }else{
            $select .= '<?php foreach(' . $data . ' as $v): ?>';
            $select .= '<option value="{$v.' . $key . '}" <?php if($v["' . $key . '"]==' . $value . '){ echo "selected"; } ?> >{$v.' . $text . '}</option>';
            $select .= '<?php endforeach; ?>';
        }
        $select .= '</select>';
        return $this->_return($attr, $select);

    }

    public function _return($attr, $form)
    {
        $attr = $this->set_input_attr($attr);
        $div = $this->wrapDiv($attr);
        $label = $this->wrapLabel($attr);
        //判断是不是水平排列的表单
        if (array_key_exists('horizontal', $attr) && !empty($attr['horizontal'])) {
            return $div . $label . $attr['label'] . '</label><div class="col-xs-' . $attr['horizontal'][1] . '">' . $form . '</div></div>';
        }else{
            return $div . $label . $attr['label'] . '</label>' . $form . '</div>';
        }
    }


}