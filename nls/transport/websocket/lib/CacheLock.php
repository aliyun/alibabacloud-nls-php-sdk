<?php

namespace WebSocket;

Class CacheLock
{
    private $path = null;           // 文件锁存放路径
    private $fp = null;             // 文件句柄
    private $hashnum = 100;         // 锁粒度，设置值越大，粒度越小
    private $name;                  // cache key
    private $eAccelerator = false;  // eAccelerator存在标志 true - 存在； false - 不存在

    public function __construct($name, $path='./')
    {
        $this->eAccelerator = function_exists("eAccelerator_lock");  // 缓存路径
        if (!$this->eAccelerator)
        {
            $this->path = $path . '.' . ($this->_mycrc32($name) % $this->hashnum) . '.cachelock';  // 文件路径, 例如： ./97.txt
        }
        $this->name = $name;
    }

    private function _mycrc32($string)
    {
        $crc = abs(crc32($string));
        if ($crc & 0x80000000)
        {
            $crc ^= 0xffffffff;
            $crc += 1;
        }
        return $crc;
    }

    // 上锁
    public function lock()
    {
        if ($this->eAccelerator)
        {
            return eaccelerator_lock($this->name);
        }
        else
        {
            $this->fp = fopen($this->path, 'w+');
            if ($this->fp === false)
            {
                return false;
            }
            return flock($this->fp, LOCK_EX);  // 独占锁定
        }

    }

    // 解锁
    public function unlock()
    {
        if ($this->eAccelerator)
        {
            return eaccelerator_unlock($this->name);
        }
        else
        {
            if ($this->fp !== false)
            {
                flock($this->fp, LOCK_UN);  // 释放锁定
                clearstatcache();           // 清除文件状态缓存
            }
            fclose($this->fp);              // 关闭文件句柄
        }
    }
}

?>