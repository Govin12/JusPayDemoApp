<?php

use Juspay\JuspayEnvironment;
use Juspay\Model\Order;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require('../vendor/autoload.php');

JuspayEnvironment::init()
	->withApiKey("4889406C0D164F73B83868F9475F179E")
	->withBaseUrl(JuspayEnvironment::SANDBOX_BASE_URL)
	->withConnectTimeout(15)
	->withReadTimeout(30);

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Our web handlers

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$app->post('/order/create', function (Request $request) {
	$orderId = uniqid();
	$amount = $request->get('amount');
	$response = Order::create(array(
		"order_id" => $orderId,
		"amount" => $amount,
		"return_url" => 'http://juspay-php-demo.herokuapp.com/order/payment-response'
	));
	
	return new Response(json_encode($response), 200);
});

$app->get('/order/payment-response', function (Request $request) use($app) {
	$orderId = $request->get('order_id');
	$status = $request->get('status');

	if($status == 'CHARGED') {
		return $app['twig']->render('success.twig');
	} else {
		return $app['twig']->render('failure.twig');
	}
});

$app->run();
