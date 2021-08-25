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
                $birthday = strlen($individual->getBirthday()) > 4 ? $individual->getBirthday() : null;
                $birth_year = strlen($individual->getBirthday()) === 4 ? $individual->getBirthday() : null;
                $deathday = strlen($individual->getDeathday()) > 4 ? $individual->getDeathday() : null;
                $death_year = strlen($individual->getDeathday()) === 4 ? $individual->getDeathday() : null;
                $burial_day = strlen($individual->getBurialday()) > 4 ? $individual->getBurialday() : null;
                $burial_year = strlen($individual->getBurialday()) === 4 ? $individual->getBurialday() : null;

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
                    'birthday' => $birthday,
                    'birth_year' => $birth_year,
                    'deathday' => $deathday,
                    'death_year' => $death_year,
                    'burial_day' => $burial_day,
                    'burial_year' => $burial_year,
                    'nick' => $nick,
                    'type' => $type,
                    'chan' => $chan->getDatetime(),
                    'nsfx' => $nsfx,
                    'npfx' => $npfx,
                    'spfx' => $spfx
                ];

                $ParentData[] = $value;
            }

            // it's take only 1 second for 3010 record
            Person::on($conn)->insert($ParentData);
            otherFields::insertOtherFields($conn, $individuals, $obje_ids, $sour_ids);
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return \Log::error($error);
        }
    }
}
