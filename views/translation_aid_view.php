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

<? require_once($config->templatePath . "partials/page-header.php"); ?>

<main class="translation_aid">

    
    <h1><?=$config->getTrans('translation aide title');?></h1>
    <p><?=$config->getTrans('translation aide description');?></p>

<? $words = isset($_REQUEST['text']) ? $_REQUEST['text'] : null; ?>
    <form action="<?=WorldlangDictUtils::makeUri($config, "tul/basatayti", $request);?>" method="post">
        <textarea name="text"><?=$words;?></textarea>
        <input type="submit" value="<?=$config->getTrans('translation aide translate button')?>" />
    </form>
<? if (!empty($sentences)) : ?>

    <? foreach($sentences as $current) : ?>
        <h3><?=$current->sentence;?></h3>
            <dl>
        <? foreach($current->words as $word) :
            $wordClass = "";
            $trans = "";
            
            if (!ctype_alpha($word)) {
                continue;
            }

            if (!empty($word) && !empty($dict[$word])) {
                $trans = $dict[$word];
            }
            else if (!empty($word) && isset($dict[$word]) && empty($dict[$word])) {
                $trans = '[Translation not found in this language]';
            }
            else {
                // Line feed / new paragraph
                $trans = '[Word not found in dictionary]';
            }

            if (isset($dict[$word])) {
                $word = WorldlangDictUtils::makeLink(
                    $config,
                    'lexi/'.urlencode($word),
                    $request,
                    $word
                );
            } ?>
            <div>
                <dt><?=$word;?></dt>
                <dd><?=$trans;?></dt>
            </div>
        <? endforeach; ?>
        </dl>
    <? endforeach; ?>
<? endif;?>


</main>

<? require_once($config->templatePath . "partials/page-footer.php"); ?>

</body>

</html>