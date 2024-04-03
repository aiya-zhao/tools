<?php
// --------------【测试示例】--------------
$redis = 'redis';  // 获取redis，可以使用昨天封装的

$lock_key = 'lock_key';   // 分布式锁的键值，可以根据不同业务逻辑，设置不同键值【最好加上用户的身份标识，避免不同用户请求堵在一起】
$lock = new RedisLock($redis, $lock_key);    // 获取分布式锁，可以封装在昨天那个类中
if ($lock->acquireLock()){
    $res = [
        'lock' => $lock->getValue($lock_key),   // 测试锁是否设置成功，测试时，需要将RedisLock类中的getValue方法改成public类型
        'release_lock' => $lock->releaseLock(),   // 测试是否正常释放锁
        'lock_r' => $lock->getValue($lock_key),   // 测试锁释放后，是否还存在
    ];
    $lock->acquireLock(); // 获取一个锁，然后不释放，相同接口请求第二次看能否立即获取到锁【主要测试锁的功能是否正常】
    return $res;
}else{
    return '获取锁失败';
}



// --------------【使用示例】--------------
$redis = 'redis';  // 获取redis，可以使用昨天封装的

$lock_key = 'lock_key';   // 分布式锁的键值，可以根据不同业务逻辑，设置不同键值【最好加上用户的身份标识，避免不同用户请求堵在一起】
$lock = new RedisLock($redis, $lock_key);    // 获取分布式锁，可以封装在昨天那个类中
  
if ($lock->acquireLock()) {  
    try {  
        // 执行需要加锁的代码...  
  
        // 释放锁  
        $lock->releaseLock();
    } catch (Exception $e) {  
        // 异常处理...  
        $lock->releaseLock(); // 确保在异常情况下也能释放锁  
    }  
} else {  
    // 获取锁失败的处理...  
}