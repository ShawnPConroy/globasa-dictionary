<?php
namespace WorldlangDict;

?>
<!doctype html>
<html class="no-js" lang="">
<? require_once($config->templatePath . "partials/html-head.php"); ?>
<body id="htmlBody">
  <!--[if IE]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
  <![endif]-->

<?
$page->description = $entry['term'] . ': ' . htmlspecialchars($entry['raw data']['trans'][$config->lang]);

require_once($config->templatePath . "partials/page-header.php");
?>

<main id="content" class="w3-main">


<div id="<?=$entry['term']?>" class="dictionaryEntry w3-card">
    <header class="w3-container">
        <h1 id="entryTerm"><?=$entry['term']?></h1>
<? if (!empty($entry['word class'])): ?>
    <div class="wordClass">(<a href="https://xwexi.globasa.net/<?=$config->lang;?>/gramati/lexiklase"><?=$entry['word class']?></a>)</div>
<? endif; ?>
    </header>


<div class="w3-container entryContent">

<!--             -->
<!-- Translation -->
<!--             -->
<section class="translation">
    <p><?

 if (!empty($entry['trans'][$config->lang])):
    $i = 0;
    foreach($entry['trans'][$config->lang] as $group):
        $j = 0;
        foreach($group as $translation):
            ?><span class="w3-tag w3-round w3-light-grey"><?=$translation?></span><?
            if (++$j < count($group)):
                ?>, <?
            endif;
        endforeach;
        if (++$i < count($entry['trans'][$config->lang])):
            ?>; <?
        endif;
    endforeach;
 else:
    echo(sprintf($config->getTrans("Missing Word Translation")));
 endif;
 
 ?></p>
</section>
 
 
 
 <!--             -->
 <!-- Examples    -->
 <!--             -->
 <? if (!empty($entry['examples'])): ?>
    <section>
    <h2><?=sprintf($config->getTrans('Example'), "")?></h2>
    <ul class="examples">
    <? foreach($entry['examples'] as $example): ?>
        <li><?=$example?></li>
    <? endforeach; ?>
    </ul>
    </section>
 <? endif; ?>
    


<!--             -->
<!-- Synonyms    -->
<!--             -->
<?
if (!empty($entry['synonyms'])):
    $words = [];
    if (count($entry['synonyms']) == 1) {
        $trans = 'synonym sentence';
    } else {
        $trans = 'synonyms sentence';
    }
    foreach ($entry['synonyms'] as $cur) {
        $words[] = WorldlangDictUtils::makeLink(
            $config,
            'lexi/'.$cur,
            $request,
            $cur
        );
    } ?>
    <section>
    <h2><?=sprintf($config->getTrans($trans), "");?></h2>
    <?=implode(', ', $words);?>
    </section>
<? endif; ?>



<!--             -->
<!-- Antonyms    -->
<!--             -->
<?
if (!empty($entry['antonyms'])) {
    $words = [];
    if (count($entry['antonyms']) == 1) {
        $trans = 'antonym sentence';
    } else {
        $trans = 'antonyms sentence';
    }
    foreach ($entry['antonyms'] as $cur) {
        $words[] = WorldlangDictUtils::makeLink(
            $config,
            'lexi/'.$cur,
            $request,
            $cur
        );
    } ?>
    <section>
    <h2><?=sprintf($config->getTrans($trans), "")?></h2>
    <?=implode(', ', $words)?>
    </section>
<? } ?>




<!--             -->
<!-- Etymology   -->
<!--             -->
<section class="etymology">
<h2><?= sprintf($config->getTrans('Etymology'), "")?></h2>
<?

// Derived
if (isset($entry['etymology']['derived'])): ?>
        <p class="etymology" style="margin-left: 40px;"><?=$entry['etymology']['derived']?></p>
<? endif;

// Natlang
if (isset($entry['etymology']['natlang'])):
    $list = &$entry['etymology']['natlang'];
    include($config->templatePath . "partials/entry_language_list.php");
endif;

// kwasilexi
if (isset($entry['etymology']['kwasilexi'])): ?>
    <div>
    <h3>Kwasilexi</h3>
    <? $list = &$entry['etymology']['kwasilexi'];
    include($config->templatePath . "partials/entry_language_list.php"); ?>
    </div>
<? endif;

// am oko pia
if (isset($entry['etymology']['am oko pia'])): ?>
    <div>
    <h3>Am oko pia</h3>
    <? $list = &$entry['etymology']['am oko pia'];
    include($config->templatePath . "partials/entry_language_list.php"); ?>
    </div>
<? endif;

// am oko
if (isset($entry['etymology']['am oko'])): ?>
    <div>
    <h3>Am oko</h3>
    <? $list = &$entry['etymology']['am oko'];
    include($config->templatePath . "partials/entry_word_list.php"); ?>
    </div>
<? endif;

// am kompara
if (isset($entry['etymology']['am kompara'])): ?>
    <div>
    <h3>Am kompara</h3>
    <? $list = &$entry['etymology']['am kompara'];
    include($config->templatePath . "partials/entry_word_list.php");
endif;

// link
if (isset($entry['etymology']['link'])): ?>
        <p><?=$entry['etymology']['link']?></p>;
<? endif; ?>
</section>


<!--                 -->
<!-- Related Words   -->
<!--                 -->
<? if (!empty($entry['relatedWords'])):
    foreach ($entry['relatedWords'] as $i=>$cur) {
        $entry['relatedWords'][$i] = WorldlangDictUtils::makeLink($config, 'lexi/'.$cur, $request, $cur);
    } ?>
    <p class="alsosee"><?=sprintf($config->getTrans('Also See Sentence'), implode(', ', $entry['relatedWords']))?></p>
<? endif; ?>



<!--         -->
<!-- Tags    -->
<!--         -->
<? if (!empty($entry['tags'])):
    foreach ($entry['tags'] as $i=>$tag) {
        $entry['tags'][$i] = WorldlangDictUtils::makeLink(
            $config,
            "lexilari/".$tag,
            $request,
            $tag
        );
    } ?>
    <p class="tags"><?=sprintf($config->getTrans('tags links'), implode(', ', $entry['tags']))?></p>
<? endif; ?>



</div>



<!--                 -->
<!-- Entry Footer    -->
<!--                 -->
<footer class="w3-container">
    <?=WorldlangDictUtils::makeLink($config, 'lexi/'.$entry['term'], $request,
        '<span class="fa fa-link"></span> '.$config->getTrans('Word Link')) ?>
    &bull; <a href="<?=$entry['ipa link']?>"><span class="fa fa-volume-up"></span> <?=$config->getTrans('ipa link')?></a>
</footer>

</div>
    
</main>

<? require_once($config->templatePath . "partials/page-footer.php"); ?>

</body>

</html>