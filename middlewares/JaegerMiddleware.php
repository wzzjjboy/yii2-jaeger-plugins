<?php


namespace Plugins\middlewares;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use Jaeger\Constants;
use OpenTracing\GlobalTracer;
use Plugins\manager\TracerManager;
use const OpenTracing\Formats\TEXT_MAP;

class JaegerMiddleware extends ActionFilter
{
    /**
     * @var TracerManager
     */
    private $tm;

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return bool whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (!$this->isOpen()){
            return true;
        }
        $this->tm = TracerManager::instance(true);
        $headers = Yii::$app->getResponse()->getHeaders()->toArray();
        if (isset($headers[Constants\Tracer_State_Header_Name])) {
            $headers[strtoupper(Constants\Tracer_State_Header_Name)] = $headers[Constants\Tracer_State_Header_Name];
        }
        $spanContext = GlobalTracer::get()->extract(
            TEXT_MAP,$headers
        );
        $span = GlobalTracer::get()->startSpan('server', ['child_of' => $spanContext]);
        GlobalTracer::get()->inject($span->getContext(), TEXT_MAP,
            $headers
        );
        foreach (Yii::$app->getResponse()->getHeaders() as $k => $v){
            $span->setTag($k, $v);
        }
        $span->setTag('env', YII_ENV);
        $span->setTag('author', 'alan.wang@wetax.com.cn');
        $span->setTag('datetime', date("Y-m-d H:i:s"));
        $this->tm->setServerSpan($span);
        return true;
    }

    /**
     * This method is invoked right after an action is executed.
     * You may override this method to do some postprocessing for the action.
     * @param Action $action the action just executed.
     * @param mixed $result the action execution result
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        if (!$this->isOpen()){
            return true;
        }
        $this->tm->getServerSpan()->finish();
        $this->tm->flush();
        return $result;
    }

    protected function isOpen()
    {
        return \Yii::$app->params['jaeger']['jaeger_open'] ?? false;
    }
}