<?php
namespace Plugins;


trait InstanceTrait
{
    private static $instance =  null;

    public static function instance($singleton = true, $params = []){

        if($singleton && static::$instance){
            return static::$instance;
        }

        $instance = new static();
        if ($params && is_array($params)) {
            foreach ($params as $k => $v) {
                if(property_exists($instance, $k)){
                    $instance->$k = $v;
                }
            }
        }

        return self::$instance = $instance;
    }
}