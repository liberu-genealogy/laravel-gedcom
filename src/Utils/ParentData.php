<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils;

use GenealogiaWebsite\LaravelGedcom\Models\Family;
use GenealogiaWebsite\LaravelGedcom\Models\Person;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Anci;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Chan;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Alia;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Asso;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Desi;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Even;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Name;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\NoteRef;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\ObjeRef;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Refn;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\SourRef;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Subm;
use GenealogiaWebsite\LaravelGedcom\Utils\otherFields;

class ParentData
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    protected $persons_id = [];
    protected $subm_ids = [];
    protected $sour_ids = [];
    protected $obje_ids = [];
    protected $note_ids = [];
    protected $repo_ids = [];
    protected $conn = '';

    public static function getPerson($conn, $individuals, $obje_ids)
    {
        $ParentData = [];
        foreach($individuals as $k=>$individual){
            $g_id = $individual->getId();
            $name = '';
            $givn = '';
            $surn = '';
            $name = '';
            $npfx = '';
            $givn = '';
            $nick = '';
            $spfx = '';
            $surn = '';
            $nsfx = '';
            $type = '';
            $fone = null; // PhpGedcom/
            $romn = null;
            $names = $individual->getName();
            $attr = $individual->getAllAttr();
            $events = $individual->getAllEven();
            $note = $individual->getNote();
            $indv_sour = $individual->getSour();
            $alia = $individual->getAlia(); // string array
            $asso = $individual->getAsso();
            $subm = $individual->getSubm();
            $anci = $individual->getAnci();
            // $desi = $individual->getDesi();
            $refn = $individual->getRefn(); //
            $obje = $individual->getObje();
            // object
            $bapl = $individual->getBapl();
            $conl = $individual->getConl();
            $endl = $individual->getEndl();
            $slgc = $individual->getSlgc();
            $chan = $individual->getChan();
            $g_id = $individual->getId();
        

            if (!empty($names)) {
                $name = current($names)->getName();
                $npfx = current($names)->getNpfx();
                $givn = current($names)->getGivn();
                $nick = current($names)->getNick();
                $spfx = current($names)->getSpfx();
                $surn = current($names)->getSurn();
                $nsfx = current($names)->getNsfx();
                $type = current($names)->getType();
            }

            // array value
            $fams = $individual->getFams();  // self family, leave it now, note would be included in family
            $famc = $individual->getFamc();  // parent family , leave it now, note and pedi would be included in family

            // added to database
            // string value
            $sex = preg_replace('/[^MF]/', '', $individual->getSex());
            $uid = $individual->getUid();
            $resn = $individual->getResn();
            $rin = $individual->getRin();
            $rfn = $individual->getRfn();
            $afn = $individual->getAfn();
            

            if ($givn == '') {
                $givn = $name;
            }
            $config = json_encode(config('database.connections.'.$conn));
            $key = [
                    ['name',$name],['givn',$givn],['surn',$surn],['sex',$sex]
                ];
            $check = Person::where($key)->first();
            if(empty($check)){
                $value = ['name'=>$name,'givn'=>$givn,'surn'=>$surn,'sex'=>$sex,'uid'=>$uid,'rin'=>$rin,'resn'=>$resn,'rfn'=>$rfn,'afn'=>$afn];

                $ParentData[] = $value;
            }
            // $person = Person::on($conn)->updateOrCreate($key,$value);
            // otherFields::insertOtherFields($conn,$individual,$obje_ids,$person);
        }
        
        foreach (array_chunk($ParentData, 200) as $chunk)
        {
          Person::on($conn)->insert($chunk);
        }
        otherFields::insertOtherFields($conn,$individuals,$obje_ids);
    }

}