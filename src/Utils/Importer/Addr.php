<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Addr as MAddr;
class Addr
{
    /**
     * PhpGedcom\Record\Refn $noteref
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($addr)
    {
        $id = null;
        if($addr == null) {
            return $id;
        }
        $adr1 = $addr->getAdr1();
        $adr2 = $addr->getAdr2();
        $city = $addr->getCity();
        $stae = $addr->getStae();
        $post = $addr->getPost();
        $ctry = $addr->getCtry();

        $addr = MAddr::where([
            ['adr1','=', $adr1],
            ['adr2','=', $adr2],
            ['city','=', $city],

            ['stae','=', $stae],
            ['post','=', $post],
            ['ctry','=', $ctry],
            ])->first();
        if($addr !== null) {
            $id = $addr->id;
        }else {
            $addr = MAddr::create(compact('adr1','adr2','city','stae','post','ctry'));
            $id = $addr->id;
        }
        return $id;
    }
}