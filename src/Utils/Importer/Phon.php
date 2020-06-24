<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;

class Phon
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID
     * @var string
     */

    public static function read($phon)
    {
        if(is_object($phon)){
            if(method_exists($phon, 'getPhon')) {
                return $phon->getPhon();
            }
        }else{
            if(is_array($phon)){
                $ret = '';
                foreach($phon as $item) {
                    $ret.="/".$item;
                }
                return $ret;
            }else{
                return "$phon";
            }
        }
    }
}
