<?php

error_reporting(E_ALL);

try {

	/**
	 * Read the configuration
	 */
	$config = include(__DIR__."/../app/config/config.php");

	$loader = new \Phalcon\Loader();

	/**
	 * We're a registering a set of directories taken from the configuration file
	 */
	$loader->registerDirs(
		array(
			$config->application->controllersDir,
			$config->application->modelsDir
		)
	)->register();

	/**
	 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
	 */
	$di = new \Phalcon\DI\FactoryDefault();

	/**
	 * Include the application routes
	 */
	$di->set('router', function(){
		return include(__DIR__."/../app/config/routes.php");
	});

	/**
	 * The URL component is used to generate all kind of urls in the application
	 */
	$di->set('url', function() use ($config) {
		$url = new \Phalcon\Mvc\Url();
		$url->setBaseUri($config->application->baseUri);
		return $url;
	});

	/**
	 * Setting up the view component
	 */
	$di->set('view', function() use ($config) {
		$view = new \Phalcon\Mvc\View();
		$view->setViewsDir($config->application->viewsDir);
		return $view;
	});

	/**
	 * Database connection is created based in the parameters defined in the configuration file
	 */
	$di->set('db', function() use ($config) {
		return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
			"host" => $config->database->host,
			"username" => $config->database->username,
			"password" => $config->database->password,
			"dbname" => $config->database->name
		));
	});

	/**
	 * Register the flash service with custom CSS classes
	 */
	$di->set('flash', function(){
		return new Phalcon\Flash\Direct(array(
			'error' => 'alert alert-danger',
			'success' => 'alert alert-success',
			'notice' => 'alert alert-info',
			'warning' => 'alert alert-warning'
		));
	});

	/**
	 * Start the session the first time some component request the session service
	 */
	$di->set('session', function() {
		$session = new \Phalcon\Session\Adapter\Files();
		$session->start();
		return $session;
	});

	/**
	 * Handle the request
	 */
	$application = new \Phalcon\Mvc\Application();
	$application->setDI($di);
	echo $application->handle()->getContent();

} catch (Phalcon\Exception $e) {
	echo $e->getMessage();
} catch (PDOException $e){
	echo $e->getMessage();
}
