<?php

/**
 * MODIF IMPORTANTE Tonic/Request.php
 * protected $uri; -> public $uri;
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

$config = [
    //'baseUri' => '/api',
    'load' => LOAD_ENTRY_POINTS,
    'mount' => MOUNT_ENTRY_POINTS
    // 'mount' => array(
    //     'WineTrip\\Winemaker' => '/winemaker'
    //     // 'WineTrip' => '/winemaker'
    // )
    #'mount' => array('Tyrell' => '/nexus'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCacheFile('/tmp/tonic.cache'), // use the metadata cache
    #'cache' => new Tonic\MetadataCacheAPC, // use the metadata cache
];

# USE CACHE IN STAG & PROD
if(ENV_MODE==='STAG' || ENV_MODE==='PROD')
{
    $config['cache'] = new Tonic\MetadataCacheFile('/tmp/'.TONIC_CACHE.'-'.ENV_MODE.'.cache');
}


$app = new Tonic\Application($config);
#echo $app; die;

$request = new Tonic\Request();
#echo $request; die;

try
{
    # Remove /api part from URL (app subfolder to not interfer with angular which redirect all non files to index.html)
    // error_log( "URI 1 : " . $request->uri );
    
    // $Uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // $Uri = str_replace('/api', '', $Uri);
    // error_log( "URI 2 : " . $Uri );
    // $request->uri = $Uri;

    $resource = $app->getResource($request);
    #echo $resource; die;
    $response = $resource->exec();
}
catch(Tonic\NotFoundException $e)
{
    $response = new Tonic\Response(404, $e->getMessage());
}
catch(Tonic\UnauthorizedException $e)
{
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';
}
catch(Tonic\MethodNotAllowedException $e)
{
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
    $response->allow = implode(', ', $resource->allowedMethods());
}
catch(Tonic\Exception $e)
{
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}

$response->output();
