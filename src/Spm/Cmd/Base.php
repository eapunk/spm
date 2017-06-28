<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author Evgeny Pervushin <pea@lab321.ru>
 */

namespace Spm\Cmd;

class Base
{
    protected $spm;

    public function __construct()
    {
        $this->spm = new \Spm\Spm();
        $this->spm->cwd = getcwd();
    }

    public static function getCmdFromCli()
    {
        global $argv;
        if(isset($argv[1])) {
            $cmdClass = '\\Spm\\Cmd\\'.implode('\\', array_map('ucfirst', explode('-', strtolower($argv[1])))).'Cmd';
            if(class_exists($cmdClass)) {
                return new $cmdClass;
            }
        }
        return null;
    }

    protected function getArgvParams($subjectCount, $allowedOptions)
    {
        global $argv;
        $options = array();
        $subjects = array();
        for($i = 2; $i < count($argv); $i++) {
            if($argv[$i][0] == '-') {
                $optPair = explode("=", ltrim($argv[$i], '-'));
                $name = $optPair[0];
                if(in_array($name.':', $allowedOptions)) {
                    if(count($optPair) < 2) {
                        throw new \Exception("Value must be specified for option {$argv[$i]}");
                    }
                    $options[$name] = $optPair[1];
                }
                elseif(in_array($name, $allowedOptions)) {
                    $options[$name] = true;
                }
                else {
                    throw new \Exception("Unknown option {$argv[$i]}");
                }
            }
            else {
                if($subjectCount !== false && count($subjects) >= $subjectCount) {
                    throw new \Exception("Unknown option {$argv[$i]}");
                }
                $subjects[] = $argv[$i];
            }
        }
        if($subjectCount !== false && count($subjects) < $subjectCount) {
            throw new \Exception("Some params are missing. See `spm help`.");
        }
        return array($subjects, $options);
    }

    public static function optionsToString($allowedOptions, $options)
    {
        $str = '';
        foreach($options as $name => $value) {
            if(in_array($name.':', $allowedOptions)) {
                $str .= " --$name=\"$value\"";
            }
            elseif(in_array($name, $allowedOptions)) {
                $str .= " --$name";
            }
        }
        return $str;
    }

    protected static function parsePackageName($fullname)
    {
        if(preg_match("#^(.+)\-(.*[0-9].*)$#", $fullname, $matches)) {
            return array($matches[1], $matches[2]);
        }
        return array($fullname, null);
    }
}