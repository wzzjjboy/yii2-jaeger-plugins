<?php


namespace Plugins\manager;

use Plugins\InstanceTrait;
use Yii;
use Jaeger\Config;
use OpenTracing\GlobalTracer;
use OpenTracing\Span;
use const OpenTracing\Formats\TEXT_MAP;
use Plugins\transport\JaegerTransportLog;
use Plugins\transport\JaegerTransportUdp;
use Plugins\sampler\SwooleProbabilisticSampler;

class TracerManager
{
    use InstanceTrait;

    /**
     * @var Span
     */
    protected $serverSpans;

    protected $configs;

    public function __construct()
    {
        $config = Config::getInstance();
        $yConfig = Yii::$app->params['jaeger'] ?? [];
        if ($yConfig){
            $config->setSampler(new SwooleProbabilisticSampler($this->getIp(), $yConfig['http_port'], $yConfig['jaeger_rate']));
            $mode = $yConfig['jaeger_mode'] ?? 1;
            if ($mode == 1) {
                $config->setTransport(new JaegerTransportUdp($yConfig['jaeger_server_host'], 8000));
            } elseif ($mode == 2) {
                $config->setTransport(new JaegerTransportLog(4000));
            } else {
                throw new \Exception("jaeger's mode is not set");
            }
            $tracer = $config->initTracer($yConfig['jaeger_pname']);
            $tracer->gen128bit();
            GlobalTracer::set($tracer); // optional
            $this->configs = $config;
        }
    }

    public function setServerSpan(Span $span)
    {
        $this->serverSpans = $span;
    }

    public function getServerSpan()
    {
        return $this->serverSpans;
    }


    public function getHeader()
    {
        $headers = [];
        if (isset($this->serverSpans)) {
            GlobalTracer::get()->inject($this->serverSpans->getContext(), TEXT_MAP,
                $headers);

            return $headers;
        } else {
            return [];
        }

    }


    public function flush()
    {
        $this->configs->flush();
        $this->configs = null;
        $this->serverSpans = null;
    }


    private function getIp()
    {
        $result = shell_exec("/sbin/ifconfig");
        if (preg_match_all("/inet (\d+\.\d+\.\d+\.\d+)/", $result, $match) !== 0)  // 这里根据你机器的具体情况， 可能要对“inet ”进行调整， 如“addr:”，看如下注释掉的if
        {
            foreach ($match [0] as $k => $v) {
                if ($match [1] [$k] != "127.0.0.1") {
                    return $match[1][$k];
                }
            }
        }
        return '127.0.0.1';
    }
}