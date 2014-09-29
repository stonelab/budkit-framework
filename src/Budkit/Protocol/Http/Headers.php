<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 04/07/2014
 * Time: 19:45
 */

namespace Budkit\Protocol\Http;

use Budkit\Parameter\Factory as Parameters;

class Headers extends Parameters{

    public function __construct(array $headers = array()){

        parent::__construct("headers", $headers );

        //Just so we have things in a readable format
        foreach($headers as $key=>$value){
            $this->set($key, $value);
        }
    }

    public function set($key, $values, $replace = true)
    {
        $key = implode("-", array_map("ucfirst", explode("-", strtr(strtolower($key), '_', '-'))));

        $values = array_values((array) $values);

        if (true === $replace || !isset($this->headers[$key])) {
            $this->parameters[$key] = $values;
        } else {
            $this->parameters[$key] = array_merge($this->headers[$key], $values);
        }

        if ('Cache-Control' === $key) {
            $this->cacheControl = $this->parseCacheControl($values[0]);
        }
    }

    protected function getCacheControlHeader()
    {
        $parts = array();
        ksort($this->cacheControl);
        foreach ($this->cacheControl as $key => $value) {
            if (true === $value) {
                $parts[] = $key;
            } else {
                if (preg_match('#[^a-zA-Z0-9._-]#', $value)) {
                    $value = '"'.$value.'"';
                }

                $parts[] = "$key=$value";
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Parses a Cache-Control HTTP header.
     *
     * @param string $header The value of the Cache-Control HTTP header
     *
     * @return array An array representing the attribute values
     */
    protected function parseCacheControl($header)
    {
        $cacheControl = array();
        preg_match_all('#([a-zA-Z][a-zA-Z_-]*)\s*(?:=(?:"([^"]*)"|([^ \t",;]*)))?#', $header, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $cacheControl[strtolower($match[1])] = isset($match[3]) ? $match[3] : (isset($match[2]) ? $match[2] : true);
        }

        return $cacheControl;
    }

} 