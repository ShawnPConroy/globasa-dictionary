<?php
namespace WorldlangDict;

/**
 * Calls the appropriate function based on the url.
 */
function processRequest($app, $appOld = null)
{
    $app->page = new \stdClass();
    $parsedUrl = parse_url(strtolower($_SERVER['REQUEST_URI']));
    $app->page->path = explode('/', $parsedUrl['path']);
    $app->page->title = $app->siteName;
    $app->page->content = "";
    
    if (isset($parsedUrl['query'])) {
        $app->page->linkQuery = '?'.$parsedUrl['query'];
        parse_str($parsedUrl['query'], $app->page->options);
        processOptions($app);
    } else {
        $app->page->linkQuery = null;
    }
    
    // Find num of parameters by comparing path to URI (minus 3 for domain)
    $requestSize = sizeof($app->page->path);
    if (empty($app->page->path[$requestSize-1])) {
        $requestSize -= 1;
    }
    $requestSkip = sizeof(explode('/', $app->siteUri))-3;
    $requestSize = $requestSize-$requestSkip;
    
    // Let's get to the content!
    $app->setLang('eng');
    $app->request = 'index';
    $app->option = null;
    $app->argument = null;
    
    if ($requestSize >= 1) {
        $app->setLang($app->page->path[$requestSkip]);
        if ($requestSize >= 2) {
            $app->request = $app->page->path[$requestSkip+1];
            if ($requestSize >= 3) {
                $app->option = urldecode($app->page->path[$requestSkip+2]);
                if ($requestSize >= 4) {
                    $app->argument = urldecode($app->page->path[$requestSkip+3]);
                }
            }
        }
    }
    
    $request = new Request($app);
    
    
    switch ($request->controller) {
            
        case 'tule':
            $app->page->content .= ToolController::run($app, $app->option, $app->argument);
            break;
            
        case 'cel-ruke':
            $app->page->content .= WordListController::getWord($app, $app->lang, $app->option);
            break;
        
        case 'search':
            $app->page->content .= SearchController::search($app, $request);
            break;
            
        case 'leksi':
            $app->page->content .= WordListController::getWord($app, 'glb', $app->option);
            break;
            
        default:
            $app->page->content .= IndexController::home($app);
            break;
    }
    include_once($app->templatePath.'index.php');
}

function processOptions($app)
{
    foreach ($app->page->options as $option=>$value) {
        switch ($option) {
            case 'template':
                $app->setTemplate($value);
                // no break
            case 'full':
                
        }
    }
}
