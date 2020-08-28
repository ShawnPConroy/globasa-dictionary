<?php
namespace WorldlangDict;

// Added at home in remote. Add in project. Added offline.
class Word
{
    public $term;
    public $category;
    public $part;
    public $etymology;
    public $relatedWords;
    public $ipaLink;

    // added before online.
    // Create object from CSV dictionary array
    public function __construct($config, $rawWords, $wordKey, $d)
    {
        $this->term       = $rawWords[$wordKey]['Word'];
        $this->termIndex  = $wordKey;
        $this->category   = $rawWords[$wordKey]['Category'];
        $this->part       = $rawWords[$wordKey]['Part Of Speech'];
        $this->etymology  = $rawWords[$wordKey]['LeksiliAsel'];

        $this->translation['eng'] = $rawWords[$wordKey]['TranslationEng'];
        $this->translation['fra'] = $rawWords[$wordKey]['TranslationFra'];
        $this->translation['rus'] = $rawWords[$wordKey]['TranslationRus'];
        $this->translation['spa'] = $rawWords[$wordKey]['TranslationSpa'];
        $this->translation['zho'] = $rawWords[$wordKey]['TranslationZho'];

        $this->parseEtymology($config, $d);
        $this->generateSearchTerms($config->worldlang, $d);
        $this->generateIpa($config);
    }

    public static function createDictionary($config, $rawWords)
    {
        $dictionary = new \StdClass();
        $dictionary->index = [];
        $dictionary->words = [];
        $dictionary->derived = [];
        foreach ($rawWords as $wordKey=>$wordData) {
            $dictionary->words[$wordKey] =
                new \WorldlangDict\Word(
                    $config,
                    $rawWords,
                    $wordKey,
                    $dictionary
                );
        }
        foreach($dictionary->index as $lang=>$indexList) {
            ksort($dictionary->index[$lang]);
        }
        Word::generateRelatedWords($dictionary);
        return $dictionary;

    }

    private function generateIpa($config)
    {
        $phrase = strtolower($this->term);
        /*
        c - 'tʃ'
        j - 'dʒ'
        r - 'ɾ'
        x - 'ʃ'
        y - 'j'
        h - 'x'
        */
        $pattern     = [ '/c/', '/j/', '/r/', '/x/', '/y/', '/h/' ];
        $replacement = [ 'tʃ',  'dʒ',   'ɾ',   'ʃ',   'j',   'x'  ];
        $result = preg_replace($pattern, $replacement, $phrase);
        $result = "http://ipa-reader.xyz/?text=".$result."&voice=Carla";
        $result = '<a href="'.$result.'"><span class="fa fa-volume-up"></span> '.$config->getTrans('ipa link').'</a>';
        $this->ipaLink = $result;
    }

    // Should this be makeRelatedWords ?
    // Take $relatedWords from etymoloy and add any logged afixes
    public static function generateRelatedWords($d)
    {
        foreach($d->words as $i=>$cur) {
            foreach($d->derived[$i] as $word) {
                $d->words[$i]->relatedWords[] = $word;
            }
        }
    }

    private function generateSearchTerms($worldlang, $d)
    {
        $this->generateWorldlangTerms($worldlang, $d);
        $this->generateNatlangTerms($worldlang, $d);
    }

    private function generateNatlangTerms($worldlang, $d)
    {
        $pd = new \Parsedown();
        foreach($this->translation as $lang=>$trans) {
            // Remove anything between brackets [{) or _underscore_ markdown.
            $trans = preg_replace('/[\[{\(_].*[\]}\)_]/U' , '', $trans);
            $trans = explode(', ', $trans);
            $trans = extractWords($trans);
            foreach ($trans as $naturalWord) {
                $naturalWord = strtolower(trim($naturalWord));
                if (!empty($naturalWord)) {
                    $d->index[$lang][$naturalWord][$this->termIndex] =
                        $this->termIndex;
                }
            }
            $this->translation[$lang] = $pd->line($this->translation[$lang]);
        }
        return;
    }

    private function generateWorldlangTerms($worldlang, $d)
    {
        // Add full term to search terms
        $d->index[$worldlang][$this->termIndex] = $this->termIndex;
        //var_dump($d);
        //die("whelp, we made it this far in word.php but why isn't \$d a StdClass object?");
        // If has optional part, remove and add to index
        if (strpos($this->term, "(") !== false) {
            // Add to index the full term without brackets
            $searchTerm =
                trim(preg_replace('/[^A-Za-z0-9 \-]/', '', $this->term));
            $d->index[$worldlang][$searchTerm] = $this->termIndex;
            // Adds shortened term, removing bracketted text
            $searchTerm =
                trim(preg_replace('/[\[{\(_].*[\]}\)_]/U' , '', $this->term));
            $d->index[$worldlang][$searchTerm] = $this->termIndex;
        }

        // Add all terms not in brackets
        $words = explode(
            ' ',
            preg_replace('/[\[{\(_].*[\]}\)_]/U' , '', $this->term)
        );
        foreach($words as $word) {
            $d->index[$worldlang][$word] = $this->termIndex;
        }
    }

    private function makeRelatedWordsUl($config, $listItems)
    {
        if ($listItems !== null && sizeof($listItems) > 0) {
            $result ='<ul>';
            foreach ($listItems as $item) {
                if (isset($config->dictionary['glb'])&&isset($config->dictionary['glb'][$item])) {
                    $result .= '<li>'.$config->makeLink('leksi/'.$item, $item) .': '.$config->dictionary['glb'][$item]['Translation'.$config->langCap].'</li>';
                } else {
                    $result .= '<li>'.$config->makeLink('leksi/'.$item, $item) .': *ERROR*</li>';
                }
            }
            $result .='</ul>';
        } else {
            $result = "";
        }
        return $result;
    }

    // log root of derived words and generate etymology links
    private function parseEtymology($config, &$d)
    {
        if (!empty($this->etymology)) {
            // Find related words if it does not refernce other languages in ().
            if (strpos($this->etymology, '(') === false) {
                if(substr($this->etymology, 0, 6) == "am oko") {
                    // Remove 'am oko' and formatting for links.
                    $etymology = preg_replace('/[^A-Za-z0-9, \-]/', '', substr($this->etymology, 7));
                } else {
                    $etymology = $this->etymology;
                }

                // Replace + and , with | and explode on that to get words
                $this->relatedWords = explode('|', str_replace([" + ",", "],"|",$etymology));
                foreach ($this->relatedWords as $word) {
                    if (strpos($word, '-') === false) {
                        $d->derived[$word][] = $this->termIndex;
                    }
                }
            }
            $pd = new \Parsedown();
            $this->etymology = $pd->line($this->etymology);
        }
    }

    private function processEntryPart($config, $data, $part)
    {
        if (!empty($data[$part.$config->langCap])) {
            return $data[$part.$config->langCap];
        } else {
            $result = "";
            if (!empty($data[$part.$config->defaultLangCap])) {
                $result = $data[$part.$config->defaultLangCap];
            } elseif (!empty($data[$part.$config->auxLangCap])) {
                $result = $data[$part.$config->auxLangCap];
            }
            return $config->getTrans('Missing Word Translation').$result;
        }
    }

    private function processEntryList($words)
    {
        if (!empty($words) > 0) {
            $words = explode(";", $words);
            foreach ($words as $index=>$word) {
                $words[$index] = trim($word);
            }
            return $words;
        } else {
            return null;
        }
    }

    public static function saveDictionary($config, $dictionary)
    {
        $fp = fopen($config->serializedLocation, 'w');
        fwrite($fp, serialize($dictionary));
        fclose($fp);

        // $yamlDictionary['words'] = $dictionary->words;
        // $yamlDictionary['engIndex'] = $dictionary->engIndex;
        // $yamlDictionary['glbIndex'] = $dictionary->glbIndex;
        // yaml_emit_file($config->YamlLocation, $yamlDictionary);

        // $fp = fopen($config->JsonLocation, 'w');
        // fwrite($fp, json_encode($dictionaryFile, JSON_UNESCAPED_UNICODE));
        // fclose($fp);

        // $fp = fopen($config->JsLocation, 'w');
        // fwrite($fp, "var dictionary = ".json_encode($dictionaryFile));
        // fclose($fp);
    }

}