# yii2-jaeger-plugins
使用步骤

#### 安装依赖扩展

- composer config minimum-stability dev
- composer require jukylin/jaeger-php:^v2.1.3 -vvv
- composer require eeliu/php_simple_aop:v0.2.4 -vvv

------



#### 初始化GIT子模块

- git submodule add git@github.com:wzzjjboy/yii2-jaeger-plugins.git plugins
- git submodule init
- git submodule update 
- 或者git submodule update --init --recursive
- 子模块使数码更新需要进程目录 git pull

------



#### 初始化git  autload

- 修改composer.json添加代码

  ```
  "autoload": {      
     "psr-4": {
        "Plugins\\": "plugins/"
     }
  }
  ```

- composer dump-autoload

------



#### 修改框架入口文件

- 打开yii框架输入文件

  - web入口的位置：backend/web/index.php(替换项目名为自己的名称)
  - console入口的位置:项目根目录下面的yii文件

- 添加AOP_CACHE_DIR和PLUGINS_DIR常量位置，引入自动加载的代码

  - 如果是web入口则为

    ```php
    define('AOP_CACHE_DIR',__DIR__.'/../runtime/cache/');
    define('PLUGINS_DIR', __DIR__ . '/../../plugins/');
    /**
     * unregister Yii class loader
     * wrapper with PinpointYiiClassLoader
     */
    function pinpoint_user_class_loader_hook()
    {
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            if(is_array($loader) && is_string($loader[0]) && $loader[0] =='Yii'){
                spl_autoload_unregister($loader);
                spl_autoload_register(['Plugins\PinpointYiiClassLoader','autoload'],true,false);
                break;
            }
        }
    }
    pinpoint_user_class_loader_hook();
    $app = new yii\web\Application($config);
    require_once __DIR__. '/../../vendor/eeliu/php_simple_aop/auto_pinpointed.php';
    $app->run();
    ```

  - 如果是console入口则为

    ```php
    define('AOP_CACHE_DIR',__DIR__.'/console/runtime/cache/');
    define('PLUGINS_DIR', __DIR__ . '/plugins/');
    
    /**
     * unregister Yii class loader
     * wrapper with PinpointYiiClassLoader
     */
    function pinpoint_user_class_loader_hook()
    {
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            if(is_array($loader) && is_string($loader[0]) && $loader[0] =='Yii'){
                spl_autoload_unregister($loader);
                spl_autoload_register(['Plugins\PinpointYiiClassLoader','autoload'],true,false);
                break;
            }
        }
    }
    
    pinpoint_user_class_loader_hook();
    
    $application = new yii\console\Application($config);
    require_once __DIR__. '/vendor/eeliu/php_simple_aop/auto_pinpointed.php';
    $exitCode = $application->run();
    exit($exitCode);
    
    ```

- 添加jager配置到环境文件中

  ```php
      //配置jager
      'jaeger' => [
          'http_port' => 80,
          'jaeger_rate' => 0.1,//采样概率
          'jaeger_mode' => 1,//模型
          'jaeger_server_host' => '127.0.0.1:6831', //jaeger服务端地址
          'jaeger_pname' => 'application_name', //应用名称
          'jaeger_open' => true,//是否开票jaeger
      ],
  ```

  

#### 注意事项

- 该方式采用动态代码的方式，会在定义的位置(AOP_CACHE_DIR)产生缓存文件、如果被代理的原文件做了修改则需要删除缓存文件，不然代码一直不会生效
- 插件目录的注释一定要紧贴着类，不然不能产生代理文件,插件注释格式必须是///@hook开头的
- 后续待增加...

尽情使用.....