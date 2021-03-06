<?php
#-------------------------------------------------------------------------------
# Copyright 2019 NAVER Corp
#
# Licensed under the Apache License, Version 2.0 (the "License"); you may not
# use this file except in compliance with the License.  You may obtain a copy
# of the License at
#
#   http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
# WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
# License for the specific language governing permissions and limitations under
# the License.
#-------------------------------------------------------------------------------

namespace Plugins;


use Yii;
use Jaeger\Constants;
use OpenTracing\GlobalTracer;
use Plugins\manager\TracerManager;
use yii\web\Response;
use const OpenTracing\Formats\TEXT_MAP;


class PerRequestPlugins extends Candy
{

    private static $_instance;

    public static function instance()
    {
        if (!self::$_instance){
            self::$_instance = new self(__METHOD__, get_called_class());
        }
        return self::$_instance;
    }

    public function __construct($apId, $who, &...$args)
    {
        parent::__construct($apId, $who, $args);
        $this->init();
    }


    public function onBefore()
    {
//        pr(__METHOD__);
    }

    public function onEnd(&$ret)
    {
//        pr(__METHOD__);
    }

    function onException(\Throwable $e)
    {
//        pr(__METHOD__);
    }

    private function init()
    {
        if (!$this->isOpen()){
            return true;
        }

        if ($request = Yii::$app->request){
            list($route) = $request->resolve();
            if (empty($route)){
                return true;
            }
        }
        $tm = TracerManager::instance();
        $headers = Yii::$app->getResponse() instanceof  Response ? Yii::$app->getResponse()->getHeaders()->toArray() : [];
        if (isset($headers[Constants\Tracer_State_Header_Name])) {
            $headers[strtoupper(Constants\Tracer_State_Header_Name)] = $headers[Constants\Tracer_State_Header_Name];
        }
        $span = GlobalTracer::get()->startActiveSpan('rootSpan')->getSpan();
        $span->setTag('env', YII_ENV);
        $span->setTag('author', 'alan.wang@wetax.com.cn');
        $span->setTag('datetime', date("Y-m-d H:i:s"));
        $tm->setServerSpan($span);

//        $scope = GlobalTracer::get()->startActiveSpan('testSpan1');
//        $scope->getSpan()->setTag('a', '1');
//        $scopeInert = GlobalTracer::get()->startActiveSpan('testSpan1');
//        $scopeInert->getSpan()->setTag('c', '3');
//        $scopeInert->close();
//        $scope->close();
//
//        $scope = GlobalTracer::get()->startActiveSpan('testSpan2');
//        $scope->getSpan()->setTag('b', '2');
//        $scope->close();
        return true;
    }

    protected function isOpen()
    {
        return Yii::$app->params['jaeger']['jaeger_open'] ?? false;
    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
        $tm = TracerManager::instance();
        $tm->getServerSpan()->finish();
        GlobalTracer::get()->flush();
    }


}