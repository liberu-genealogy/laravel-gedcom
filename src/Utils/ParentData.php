<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\Person;

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

    public static function getPerson($conn, $individuals, $obje_ids = [], $sour_ids = [])
    {
        $ParentData = [];
        $a = [];

        try {
            foreach ($individuals as $k => $individual) {
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
                $fone = null; // Gedcom/
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
                $refn = $individual->getRefn();
                $obje = $individual->getObje();
                // object
                $bapl = $individual->getBapl();
                $conl = $individual->getConl();
                $endl = $individual->getEndl();
                $slgc = $individual->getSlgc();
                $chan = $individual->getChan();
//                $a = $chan->getDatetime() ? '' : '';
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
                
                $birt = $individual->getBirt();
                $birthday = $birt->dateFormatted ?? null;
                $birth_year = $birt->year ?? null;
                $birthday_dati = $birt->dati ?? null;
                $birthday_plac = $birt->plac ?? null;
                
                $deat = $individual->getDeat();
                $deathday = $deat->dateFormatted ?? null;
                $death_year = $deat->year ?? null;
                $deathday_dati = $deat->dati ?? null;
                $deathday_plac = $deat->plac ?? null;
                $deathday_caus = $deat->caus ?? null;

                $buri = $individual->getBuri();
                $burial_day = $buri->dateFormatted ?? null;
                $burial_year = $buri->year ?? null;
                $burial_day_dati = $buri->dati ?? null;
                $burial_day_plac = $buri->plac ?? null;

                if ($givn == '') {
                    $givn = $name;
                }

                $config = json_encode(config('database.connections.'.$conn));
                $value = [
                    'gid' => $g_id,
                    'name' => $name,
                    'givn' => $givn,
                    'surn' => $surn,
                    'sex' => $sex,
                    'uid' => $uid,
                    'rin' => $rin,
                    'resn' => $resn,
                    'rfn' => $rfn,
                    'afn' => $afn,
                    'nick' => $nick,
                    'type' => $type,
                    'chan' => $chan ? $chan->getDatetime() : null,
                    'nsfx' => $nsfx,
                    'npfx' => $npfx,
                    'spfx' => $spfx,
                    'birthday' => $birthday,
                    'birth_year' => $birth_year,
                    'birthday_dati' => $birthday_dati,
                    'birthday_plac' => $birthday_plac,
                    'deathday' => $deathday,
                    'death_year' => $death_year,
                    'deathday_dati' => $deathday_dati,
                    'deathday_plac' => $deathday_plac,
                    'deathday_caus' => $deathday_caus,
                    'burial_day' => $burial_day,
                    'burial_year' => $burial_year,
                    'burial_day_dati' => $burial_day_dati,
                    'burial_day_plac' => $burial_day_plac,
                ];

                $ParentData[] = $value;
            }

            // it's take only 1 second for 3010 record
            Person::on($conn)->upsert($ParentData, ['uid']);
            otherFields::insertOtherFields($conn, $individuals, $obje_ids, $sour_ids);
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return \Log::error($error);
        }
    }
}
