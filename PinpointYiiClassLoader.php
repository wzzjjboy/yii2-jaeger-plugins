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


class PinpointYiiClassLoader
{
    /**
     * copy from yii autoload
     * @param $className
     * @return bool|mixed|string|null
     */
    public static function findFile($className)
    {
        if (isset(Yii::$classMap[$className])) {
            $classFile = Yii::$classMap[$className];
            if ($classFile[0] === '@') {
                $classFile = Yii::getAlias($classFile);
            }
        } elseif (strpos($className, '\\') !== false) {
            $classFile = Yii::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
            if ($classFile === false || !is_file($classFile)) {
                return null;
            }
        } else {
            return null;
        }
         return $classFile;
    }

    public static function autoload($className)
    {
        Yii::autoload($className); // TODO: Change the autogenerated stub
    }
}
