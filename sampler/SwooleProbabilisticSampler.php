<?php


namespace Plugins\sampler;

use OpenTracing\Tags;
use Jaeger\Constants;
use Jaeger\Sampler\Sampler;


class SwooleProbabilisticSampler implements Sampler {

    // min 0, max 1
    private $rate = 0;

    private $tags = [];


    public function __construct($ip, $port, $rate = 0.0001){
        $this->rate = $rate;
        $this->tags[Constants\SAMPLER_TYPE_TAG_KEY] = 'probabilistic';
        $this->tags[Constants\SAMPLER_PARAM_TAG_KEY] = $rate;
        $this->tags[Tags\PEER_HOST_IPV4] = $ip;
        $this->tags[Tags\PEER_PORT] = $port;
    }


    public function IsSampled(){
        return true;
        if(mt_rand(1, 1 / $this->rate) == 1){
            return true;
        }else{
            return false;
        }
    }


    public function Close(){
        //nothing to do
    }


    public function getTags(){
        return $this->tags;
    }
}
