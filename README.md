# yii2-jaeger-plugins
使用步骤

#### 安装依赖扩展

- composer install  opentracing/opentracing:1.0.0-beta5 -vvv
- composer install jukylin/jaeger-php:^6.3.4 -vvv
- composer install eeliu/php_simple_aop:v0.2.4 -vvv

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

  

  

  尽情使用.....