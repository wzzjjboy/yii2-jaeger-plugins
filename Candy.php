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

/**
 * User: eeliu
 * Date: 1/4/19
 * Time: 3:23 PM
 */

namespace Plugins;

//require_once "PluginsDefines.php.bak";

use OpenTracing\GlobalTracer;
use OpenTracing\Scope;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;

abstract class Candy
{
    protected $apId;
    protected $who;
    protected $args;
    protected $ret=null;

    /**
     * @var Scope
     */
    protected $scope;

    public function __construct($apId,$who,&...$args)
    {
        $this->apId = $apId;
        $this->who =  $who;
        $this->args = &$args;
    }

    protected function writeBeforeInfo(string $operationName, \Closure $closure = null)
    {
        $this->scope = GlobalTracer::get()->startActiveSpan($operationName);
        $this->scope->getSpan()->setTag('method', $this->apId);
        $this->scope->getSpan()->setTag('who', is_object($this->who) ? get_class($this->who) : (string)$this->who);
        $this->scope->getSpan()->setTag('args', is_array($this->args) ? json_encode($this->args ?: []) : (string)$this->args);
        if (is_callable($closure)){
            call_user_func($closure);
        }
    }

    protected function writeEndInfo(&$ret, \Closure $closure = null) {
        $this->scope->getSpan()->log((array)$ret);
        if (is_callable($closure)){
            call_user_func($closure);
        }
        $this->scope->close();
    }

    /**
     * @param \Throwable $e
     * @param \Closure|null $closure
     */
    protected function writeExceptionInfo(\Throwable $e, \Closure $closure = null){
        if (is_callable($closure)){
            call_user_func($closure);
        }
        $this->scope->getSpan()->setTag('exception', $e->getMessage());
        $this->scope->getSpan()->log([
            'msg'  => $e->getMessage(),
            'file' =>$e->getFile(),
            'lien' =>$e->getLine(),
            'trace'=>$e->getTraceAsString(),
        ]);
        $this->scope->close();
    }

    public function __destruct(){}

    abstract function onBefore();

    abstract function onEnd(&$ret);

    /**
     * @param \Throwable $e
     * @return mixed
     */
    abstract function onException(\Throwable $e);
}
