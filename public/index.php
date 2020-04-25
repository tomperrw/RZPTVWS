<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require 'rb.php';

R::setup('mysql:host=localhost;dbname=dhbwvs20_rzptvw', 'dhbwvs20_dbuser1', 'dbuser1pwd');

$app = AppFactory::create();
$app->setBasePath((function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return $_SERVER['SCRIPT_NAME'];
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }
    return '';
})());


$app->get('/rezepte', function (Request $request, Response $response, $args) {
	$rezepte = R::findAll('rezept');
	foreach($rezepte as $rezept) {
		$rezept->person;
	}
    $response->getBody()->write(json_encode(R::exportAll( $rezepte )));
    return $response;
});

$app->get('/rezepte/{rezeptid}', function (Request $request, Response $response, $args) {
	$rezept = R::load('rezept', $args['rezeptid']);
	$first = reset( $rezept->ownZutatList );
	$last = end( $rezept->ownZutatList ); 
	$rezept->person;
	$response->getBody()->write(json_encode($rezept));
    return $response;
});

$app->get('/rezepte/findByPerson/', function (Request $request, Response $response, $args) {
	$rezepte = R::findAll('rezept', 'person_id=:pid', [':pid'=>$request->getQueryParams()['pid']]);
	foreach($rezepte as $rezept) {
		$rezept->person;
	}
    $response->getBody()->write(json_encode(R::exportAll( $rezepte )));
    return $response;
});

$app->delete('/rezepte/{rezeptid}', function (Request $request, Response $response, $args) {
	$rezept = R::load('rezept', $args['rezeptid']);
	//R::trash($rezept);
	$response->getBody()->write(json_encode($rezept));
    return $response;
});


$app->post('/rezepte', function (Request $request, Response $response, $args) {
	$parsedBody = $request->getParsedBody();
	
	$rezept = R::dispense('rezept');
	$rezept->name = $parsedBody['name'];
	$rezept->schwierigkeit = $parsedBody['schwierigkeit'];
	$rezept->zubereitungszeit = $parsedBody['zubereitungszeit'];
	$rezept->zubereitung = $parsedBody['zubereitung'];
	
	$p = R::load('person', $parsedBody['person_id']);
	$rezept->person = $p;
	$rezept->person_id = 1;
	
	R::store($rezept);
	
	$response->getBody()->write(json_encode($rezept));
    return $response;
});


$app->put('/rezepte', function (Request $request, Response $response, $args) {
	$parsedBody = json_decode((string)$request->getBody(), true);
	
	
	$rezept = R::load('rezept', $parsedBody['id']);
	$rezept->name = $parsedBody['name'];
	$rezept->schwierigkeit = $parsedBody['schwierigkeit'];
	$rezept->zubereitungszeit = $parsedBody['zubereitungszeit'];
	$rezept->zubereitung = $parsedBody['zubereitung'];
	
	R::store($rezept);
	
	$response->getBody()->write(json_encode($rezept));
    return $response;
});


$app->run();
?>
