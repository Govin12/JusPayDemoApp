<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require('../vendor/autoload.php');

Juspay\JuspayConfiguration::configureAndSetUp(Juspay\JuspayConfiguration::ENVIRONMENT_SANDBOX, 'sriduth_sandbox_test', 'F740059B99694ABF83A7C1C879B6892B', 15, 30);

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
	$orderId = $request->get('order_id');
	$amount = $request->get('amount');

	$response = Juspay\ExpressCheckout::$Order->createOrder(new Juspay\Core\OrderCreateParams($orderId, $amount));
	
	return new Response(json_encode($response), 200);
});

$app->get('/order/payment-response', function (Request $request) {
	$orderId = $request->get('order_id');
	$status = $request->get('status');

	if($status == 'SUCCESS') {
		
	}
});

$app->run();
