<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Subn as MSubn;
class Subn
{
    /**
     * PhpGedcom\Record\Subn $subn
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($subn, $subm_ids)
    {
        if($subn == null || is_array($subn)) {
            return ;
        }
        $_subm = $subn->getSubm();
        $subm = null;
        if(isset($subm_ids[$subm])) { 
            $subm = $subm_ids[$_subm];
        }
        $famf = $subn->getFamf();
        $temp = $subn->getTemp();
        $ance = $subn->getAnce();
        $desc = $subn->getDesc();
        $ordi = $subn->getOrdi();
        $rin = $subn->getRin();
        $_subn = MSubn::updateOrCreate(compact('subm', 'famf', 'temp', 'ance', 'desc','ordi', 'rin'), compact('subm', 'famf', 'temp', 'ance', 'desc','ordi', 'rin'));
        return ;
    }
}