<?php
namespace WorldlangDict;

/**
 * Calls the appropriate function based on the url.
 */
function router($config)
{
    $page = new Page($config->getTrans('App Name'));

    $request = new Request($config);

    if ($request->incomplete) {
        WorldlangDictUtils::redirect($config, "");
    }
    $config->setLang($request->lang);

    switch ($request->controller) {

        case 'tule': // TODO: Delete this old term
        case 'tul':
            ToolController::run($config, $request, $page);
            break;

        case 'leksi': // TODO: Delete this old term
        case 'lexi':
            $word = isset($request->arguments[0]) ? $request->arguments[0] : null;
            WordController::addEntry($config, $request, $word, $page);
            include_once($config->templatePath.'view-default.php');
            break;

        case 'cel-ruke':
            WordController::addNatWord($config, $request, $config->lang, $page);
            include_once($config->templatePath.'view-default.php');
            break;

        case 'search': // deprecatd 2022-12-01
        case 'xerca':
            SearchController::search($config, $request, $page);
            include_once($config->templatePath.'view-default.php');
            break;

        case 'am-reporte':
            FeedbackController::feedback($config, $request, $page);
            include_once($config->templatePath.'view-default.php');
            break;

        case 'leksilar': // TODO: delete? deprecated 2020/2021?
        case 'lexilar': // TODO: delete? deprecated 2021-11-19
        case 'lexilari':
            WordController::addTags($config, $request, $page);
            break;
        case 'kentanible-menalari': // never publicized
        case 'abeceli-menalari':
            BrowseController::default($config, $request, $page);
            include_once($config->templatePath.'view-browse.php');
            break;
        case 'test':
            TestController::helloWorld($config, $request, $page);
            include_once($config->templatePath.'view-default.php');
            break;
        default:
            IndexController::home($config, $request, $page);
            include_once($config->templatePath.'view-default.php');
            break;
    }
}
