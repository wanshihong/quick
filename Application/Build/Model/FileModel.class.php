<?php
namespace Build\Model;
class FileModel
{

    private $message = '';

    //创建文件
    public function create($path, $content)
    {
        if (!$this->createPath($path)) {
            return false;
        }
        if (!$this->createFle($path, $content)) {
            return false;
        }
        return true;
    }

    //创建目录
    public function createPath($path)
    {
        $create_path = dirname($path);
        if (file_exists($create_path)) {
            return true;
        }
        if (!mkdir($create_path, 0777, true)) {
            $this->message = "创建{{$path}}失败,请检查权限是否足够";
            return false;
        }
        return true;
    }

    //创建文件
    public function createFle($file, $content)
    {
        if (empty($content)) return true;
        if (!file_put_contents($file, $content)) {
            $this->message = "写入{$file}失败,请检查权限是否足够";
            return false;
        }
        return true;
    }

    public function getMessage()
    {
        return $this->message;
    }
}