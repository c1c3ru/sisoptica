<?php
namespace Sisoptica\Util;

namespace App\Util {
    class CtrlBuffer {

        private $controller;
        private $getAction;
        private $data = array();

        private function __construct() { }

        public function getEntry($id) {
            if (!isset($this->data[$id])) {
                $get = $this->getAction;
                $this->data[$id] = $this->controller->$get($id);
            }
            return $this->data[$id];
        }

        public static function newInstance($controller, $getAction) {
            $buffer = new CtrlBuffer();
            $buffer->controller = $controller;
            $buffer->getAction = $getAction;

            return $buffer;
        }

    }
}

namespace Mapper {

    function object_mapper(/* object attribute names ... */) {

        $attrs = func_get_args();

        return function($o) use ($attrs) {

            $_o = array();
            foreach ($attrs as $attr) {
                $_o[$attr] = isset($o->{$attr}) ? $o->{$attr} : null;
            }
            return (object) $_o;

        };

    }

}

namespace Analyser {

    function coalesce(/* values */) {

        $values = func_get_args();

        foreach ($values as $k => $v) {
            if (!!$v) return array($k, $v);
        }

        return array(-1, null);

    }

}

?>
