<?php

namespace Stjornvisi\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

class Values implements EventManagerAwareInterface
{
    protected $events;

    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
        return $this;
    }

    /**
     * Get event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    public function getBusinessTypes()
    {
        return array(
            'Einkahlutafélag (ehf)'     => 'Einkahlutafélag (ehf)',
            'Hlutafélag (hf)'           => 'Hlutafélag (hf)',
            'Opinbert hlutafélag (ohf)' => 'Opinbert hlutafélag (ohf)',
            'Opinber stofnun'           => 'Opinber stofnun',
            'Sameignafélag (sf)'        => 'Sameignafélag (sf)',
            'Samvinnufélag (svf)'       => 'Samvinnufélag (svf)',
            'Samlagsfélag (slf)'        => 'Samlagsfélag (slf)',
            'Háskóli'                   => 'Háskóli',
            'Félagasamtök'              => 'Félagasamtök',
            'Einstaklingur'             => 'Einstaklingur'
        );
    }

    public function getPostalCode()
    {
        return array(
            '101 Reykjavík'                => '101 Reykjavík',
            '103 Reykjavík'                => '103 Reykjavík',
            '104 Reykjavík'                => '104 Reykjavík',
            '105 Reykjavík'                => '105 Reykjavík',
            '107 Reykjavík'                => '107 Reykjavík',
            '108 Reykjavík'                => '108 Reykjavík',
            '109 Reykjavík'                => '109 Reykjavík',
            '110 Reykjavík'                => '110 Reykjavík',
            '111 Reykjavík'                => '111 Reykjavík',
            '112 Reykjavík'                => '112 Reykjavík',
            '113 Reykjavík'                => '113 Reykjavík',
            '116 Reykjavík'                => '116 Reykjavík',
            '121 Reykjavík (pósthólf)'       => '121 Reykjavík (pósthólf)',
            '123 Reykjavík (pósthólf)'       => '123 Reykjavík (pósthólf)',
            '124 Reykjavík (pósthólf)'       => '124 Reykjavík (pósthólf)',
            '125 Reykjavík (pósthólf)'       => '125 Reykjavík (pósthólf)',
            '127 Reykjavík (pósthólf)'       => '127 Reykjavík (pósthólf)',
            '128 Reykjavík (pósthólf)'       => '128 Reykjavík (pósthólf)',
            '129 Reykjavík (pósthólf)'       => '129 Reykjavík (pósthólf)',
            '130 Reykjavík (pósthólf)'       => '130 Reykjavík (pósthólf)',
            '132 Reykjavík (pósthólf)'       => '132 Reykjavík (pósthólf)',
            '150 Reykjavík'                => '150 Reykjavík',
            '170 Seltjarnarnes'             => '170 Seltjarnarnes',
            '172 Seltjarnarnes (pósthólf)'    => '172 Seltjarnarnes (pósthólf)',
            '190 Vogum'                     => '190 Vogum',
            '200 Kópavogur'                => '200 Kópavogur',
            '201 Kópavogur'                    => '201 Kópavogur',
            '202 Kópavogur (pósthólf)'       =>  '202 Kópavogur (pósthólf)',
            '203 Kópavogur'                => '203 Kópavogur',
            '210 Garðabæ'                     => '210 Garðabæ',
            '212 Garðabæ (pósthólf)'        => '212 Garðabæ (pósthólf)',
            '220 Hafnarfirði'              => '220 Hafnarfirði',
            '221 Hafnarfirði'              => '221 Hafnarfirði',
            '222 Hafnarfirði (pósthólf)'     =>  '222 Hafnarfirði (pósthólf)',
            '225 Álftanesi'                => '225 Álftanesi',
            '230 Reykjanesbæ'              => '230 Reykjanesbæ',
            '232 Reykjanesbæ (pósthólf)'     => '232 Reykjanesbæ (pósthólf)',
            '233 Reykjanesbæ'              => '233 Reykjanesbæ',
            '235 Reykjanesbæ'              => '235 Reykjanesbæ',
            '240 Grindavík'                => '240 Grindavík',
            '245 Sandgerði'                => '245 Sandgerði',
            '250 Garði'                        => '250 Garði',
            '260 Reykjanesbæ'              => '260 Reykjanesbæ',
            '270 Mosfellsbæ'               => '270 Mosfellsbæ',
            '271 Mosfellsbæ'               => '271 Mosfellsbæ',
            '276 Mosfellsbæ'               => '276 Mosfellsbæ',
            '300 Akranesi'                  => '300 Akranesi',
            '301 Akranesi'                  => '300 Akranesi',
            '302 Akranesi (pósthólf)'         => '302 Akranesi (pósthólf)',
            '310 Borgarnesi'                => '310 Borgarnesi',
            '311 Borgarnesi'                => '311 Borgarnesi',
            '320 Reykholt í Borgarfirði'  => '320 Reykholt í Borgarfirði',
            '340 Stykkishólmi'                 => '340 Stykkishólmi',
            '345 Flatey á Breiðafirði'       => '345 Flatey á Breiðafirði',
            '350 Grundarfirði'                 => '350 Grundarfirði',
            '355 Ólafsvík'                    => '355 Ólafsvík',
            '356 Snæfellsbæ'              => '356 Snæfellsbæ',
            '360 Hellissandi'               => '360 Hellissandi',
            '370 Búðardal'                    => '370 Búðardal',
            '371 Búðardal'                    => '371 Búðardal',
            '380 Reykhólahreppi'           => '380 Reykhólahreppi',
            '400 Ísafirði'                    => '400 Ísafirði',
            '401 Ísafirði'                    => '401 Ísafirði',
            '410 Hnífsdal'                     => '410 Hnífsdal',
            '415 Bolungarvík'              => '415 Bolungarvík',
            '420 Súðavík'                    => '420 Súðavík',
            '425 Flateyri'                  => '425 Flateyri',
            '430 Suðureyri'                => '430 Suðureyri',
            '450 Patreksfirði'                 => '450 Patreksfirði',
            '451 Patreksfirði'                 => '451 Patreksfirði',
            '460 Tálknafirði'                 => '460 Tálknafirði',
            '465 Bíldudal'                     => '465 Bíldudal',
            '470 Þingeyri'                     => '470 Þingeyri',
            '471 Þingeyri'                     => '471 Þingeyri',
            '500 Stað'                         => '500 Stað',
            '510 Hólmavík'                    => '510 Hólmavík',
            '512 Hólmavík'                    => '512 Hólmavík',
            '520 Drangsnesi'                => '520 Drangsnesi',
            '524 Árneshreppi'              => '524 Árneshreppi',
            '530 Hvammstanga'               => '530 Hvammstanga',
            '531 Hvammstanga'               => '530 Hvammstanga',
            '540 Blönduósi'                   => '540 Blönduósi',
            '541 Blönduósi'                   => '541 Blönduósi',
            '545 Skagaströnd'              => '545 Skagaströnd',
            '550 Sauðárkróki'                => '550 Sauðárkróki',
            '551 Sauðárkróki'                => '551 Sauðárkróki',
            '560 Varmárhlíð'                 => '560 Varmárhlíð',
            '565 Hofsós'                   => '565 Hofsós',
            '566 Hofsós'                   => '566 Hofsós',
            '570 Fljótum'                  => '570 Fljótum',
            '580 Siglufirði'               => '580 Siglufirði',
            '600 Akureyri'                  => '600 Akureyri',
            '601 Akureyri'                  => '601 Akureyri',
            '602 Akureyri'                  => '602 Akureyri',
            '603 Akureyri'                  => '603 Akureyri',
            '610 Grenivík'                     => '610 Grenivík',
            '611 Grímsey'                  => '611 Grímsey',
            '620 Dalvík'                   => '620 Dalvík',
            '621 Dalvík'                   => '621 Dalvík',
            '625 Ólafsfirði'              => '625 Ólafsfirði',
            '630 Hrísey'                   => '630 Hrísey',
            '640 Húsavík'                     => '640 Húsavík',
            '641 Húsavík'                     => '641 Húsavík',
            '645 Fosshóli'                     => '645 Fosshóli',
            '650 Laugum'                    => '650 Laugum',
            '660 Mývatni'                  => '660 Mývatni',
            '670 Kópaskeri'                    => '670 Kópaskeri',
            '671 Kópaskeri'                    => '671 Kópaskeri',
            '675 Raufarhöfn'               => '675 Raufarhöfn',
            '680 Þórshöfn'                   => '680 Þórshöfn',
            '681 Þórshöfn'                   => '681 Þórshöfn',
            '685 Bakkafirði'               => '685 Bakkafirði',
            '690 Vopnafirði'               => '690 Vopnafirði',
            '700 Egilsstöðum'                 => '700 Egilsstöðum',
            '701 Egilsstöðum'                 => '701 Egilsstöðum',
            '710 Seyðisfirði'                 => '710 Seyðisfirði',
            '715 Mjóafirði'                   => '715 Mjóafirði',
            '720 Borgarfirði (eystri)'         => '720 Borgarfirði (eystri)',
            '730 Reyðarfirði'                 => '730 Reyðarfirði',
            '735 Eskifirði'                    => '735 Eskifirði',
            '740 Neskaupsstað'                 => '740 Neskaupsstað',
            '750 Fáskrúðsfirði'                 => '750 Fáskrúðsfirði',
            '755 Stöðvarfirði'               => '755 Stöðvarfirði',
            '760 Breiðdalsvík'                => '760 Breiðdalsvík',
            '765 Djúpavogi'                    => '765 Djúpavogi',
            '780 Höfn í Hornafirði'          => '780 Höfn í Hornafirði',
            '781 Höfn í Hornafirði'          => '781 Höfn í Hornafirði',
            '785 Öræfum'                  => '785 Öræfum',
            '800 Selfossi'                  => '800 Selfossi',
            '801 Selfossi'                  => '801 Selfossi',
            '802 Selfossi (pósthólf)'         => '802 Selfossi (pósthólf)',
            '810 Hveragerði'               => '810 Hveragerði',
            '815 Þorlákshöfn'                => '815 Þorlákshöfn',
            '816 Þorlákshöfn'                => '816 Þorlákshöfn',
            '820 Eyrarbakka'                => '820 Eyrarbakka',
            '825 Stokkseyri'                => '825 Stokkseyri',
            '840 Laugarvatni'               => '840 Laugarvatni',
            '845 Flúðum'                  => '845 Flúðum',
            '850 Hellu'                         => '850 Hellu',
            '851 Hellu'                         => '851 Hellu',
            '860 Hvolsvelli'                => '860 Hvolsvelli',
            '861 Hvolsvelli'                => '861 Hvolsvelli',
            '870 Vík'                      => '870 Vík',
            '880 Kirkjubæjarklaustri'      => '880 Kirkjubæjarklaustri',
            '900 Vestmannaeyjum'            => '900 Vestmannaeyjum',
            '902 Vestmannaeyjum (pósthólf)'   => '902 Vestmannaeyjum (pósthólf)'
        );
    }

    public function getCompanySizes()
    {
        return array(
            'Einstaklingur'    => 'Einstaklingur',
            'Færri en 5'       => 'Færri en 5',
            '6-25'             => '6-25',
            '25-49'            => '25-49',
            '50-99'            => '50-99',
            '100-199'          => '100-199',
            '200 eða fleiri'   => '200 eða fleiri'
        );
    }

    public function getTitles()
    {
        return array(
            "Annað" => "Annað",
            "Deildarstjóri" => "Deildarstjóri",
            "Doktorsnemi" => "Doktorsnemi",
            "Fjármálastjóri" => "Fjármálastjóri",
            "Forstjóri" => "Forstjóri",
            "Forstöðumaður" => "Forstöðumaður",
            "Forstöðumaður þróunarsviðs" => "Forstöðumaður þróunarsviðs",
            "Framkvæmdastjóri" => "Framkvæmdastjóri",
            "Framleiðslustjóri" => "Framleiðslustjóri",
            "Gæðastjóri" => "Gæðastjóri",
            "Háskólanemi" => "Háskólanemi",
            "Innkaupastjóri" => "Innkaupastjóri",
            "Lögreglustjóri" => "Lögreglustjóri",
            "Mannauðsstjóri" => "Mannauðsstjóri",
            "Mannauðstjóri" => "Mannauðstjóri",
            "Markaðsfulltrúi" => "Markaðsfulltrúi",
            "Markaðsstjóri" => "Markaðsstjóri",
            "Markaðstjóri" => "Markaðstjóri",
            "Markþjálfi" => "Markþjálfi",
            "Millistjórnandi" => "Millistjórnandi",
            "Sérfræðingur" => "Sérfræðingur",
            "Skrifstofustjóri" => "Skrifstofustjóri",
            "Sölustjóri" => "Sölustjóri",
            "Starfsmannastjóri" => "Starfsmannastjóri",
            "Stjórnendamarkþjálfi" => "Stjórnendamarkþjálfi",
            "Stjórnunarráðgjafi" => "Stjórnunarráðgjafi",
            "Sviðsstjóri" => "Sviðsstjóri",
            "Tölvunarfræðingur" => "Tölvunarfræðingur",
            "Upplýsingafulltrúi" => "Upplýsingafulltrúi",
            "Verkefnastjóri" => "Verkefnastjóri",
            "Þjónustustjóri" => "Þjónustustjóri",
        );
    }
}
