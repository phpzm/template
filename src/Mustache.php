<?php

namespace Simples\Template;

/**
 * Trait Mustache
 * @package Simples\Template
 */
trait Mustache
{
    /**
     * @param $string
     * @param $data
     * @return mixed
     */
    protected function resolveMustaches($string, $data)
    {
        $replace = function ($match) use ($data) {
            $string = trim($match[1]);
            if (isset($data[$string])) {
                return $data[$string];
            }
            if (preg_match('/\w+\(.*?\)/', $string)) {
                return $this->resolveFunctions($string, $data);
            }
            return '';
        };
        return preg_replace_callback('/{{((?:[^}]|}[^}])+)}}/', $replace, $string);
    }

    /**
     * @param $string
     * @param $data
     * @return mixed
     */
    private function resolveFunctions($string, $data)
    {
        $replace = function ($match) use ($data) {
            $function = trim($match[1]);
            $parameters = [];
            if (isset($match[2])) {
                $parameters = array_map('trim', explode(',', $match[2]));
            }
            $parameters = array_map(function ($value) use ($data) {
                if (isset($data[$value])) {
                    return $data[$value];
                }
                if (strpos($value, "'") === 0) {
                    return trim($value, "'");
                }
                return trim($value, '"');
            }, $parameters);

            if (function_exists($function)) {
                return call_user_func_array($function, $parameters);
            }
            return '';
        };
        return preg_replace_callback('/(\w+)\((.*?)\)/', $replace, $string);
    }
}
