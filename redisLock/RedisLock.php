<?php

// // 命名空间
// namespace xxx;
  
class RedisLock{

    private $redis;  
    private $lock_key;  
    private $lock_timeout; // 锁的超时时间（秒）  
    private $acquire_timeout = 5; // 获取锁的超时时间（秒）  
  
    public function __construct($redis, $lock_key, $lock_timeout = 10){
        $this->redis = $redis;
        $this->lock_key = $lock_key;
        $this->lock_timeout = $lock_timeout;
    }  
  
    public function acquireLock(){

        $start_time = time();  
        $random_value = uniqid(); // 创建一个随机值，作为锁的"value"，用于在释放锁时进行验证  
  
        while (true) {  
            // 使用SETNX命令尝试获取锁，如果成功则返回1，否则返回0  
            $result = $this->redis->set($this->lock_key, $random_value, ['nx', 'ex' => $this->lock_timeout]);  
  
            if ($result) {  
                return true; // 获取锁成功  
            }  
  
            // 如果超过获取锁的超时时间，则返回false  
            if ((time() - $start_time) >= $this->acquire_timeout) {  
                return false;  
            }  
  
            // 等待一段时间后重试
            time_nanosleep(0, 1000000);  // php >= 7.1   固定1毫秒  可以优化设置增加等待时间
            // usleep(1000);  // php <= 7.2   1毫秒
        }  
    }  
  
    public function releaseLock(){
        $script = "
            if redis.call('get', KEYS[1]) == ARGV[1] then
                return redis.call('del', KEYS[1])
            else
                return 0
            end
        ";
  
        // 使用EVAL命令执行Lua脚本，确保只有锁的持有者才能释放锁  
        $result = $this->redis->eval($script, [$this->lock_key, $this->getValue()], 1);  
  
        return $result === 1; // 如果返回1，表示锁已成功释放
    }
  
    private function getValue(){  
        return $this->redis->get($this->lock_key);  
    }  
}  