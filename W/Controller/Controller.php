<?php

namespace W\Controller;

use W\Security\AuthentificationManager;
use W\Security\AuthorizationManager;

class Controller 
{

	/**
	 * Redirige vers une URI
	 * @param  string $uri URI vers laquelle rediriger
	 */
	public function redirect($uri)
	{
		if (filter_var($uri, FILTER_VALIDATE_URL)){
			header("Location: $uri");
			die();
		}

		return false;
	}


	public function redirectToRoute($routeName, $params = array())
	{
		global $app;
    	$router = $app->getRouter();
    	$uri = $router->generate($routeName, $params);
    	return $this->redirect($uri);
	}


	/**
	 * Affiche un template
	 * 
	 * @param  string $file Chemin vers le template, relatif à app/templates/
	 * @param  array  $data Données à rendre disponibles à la vue
	 */
	public function show($file, array $data = array())
	{
		//incluant le chemin vers nos templates
		$engine = new \League\Plates\Engine('../app/templates');

		//charge nos extensions (nos fonctions personnalisées)
		$engine->loadExtension(new \W\View\Plates\PlatesExtensions());

		//rend certaines données disponibles à tous les templates
		//accessible avec $w_user dans les fichiers de vue
		$engine->addData(
			array(
				"w_user" => $this->getUser()
			)
		);

		//retire l'éventuelle extension .php
		$file = str_replace(".php", "", $file);

		// Affiche le template
		echo $engine->render($file, $data);
	}

	/**
	 * Affiche une page 403
	 */
	public function showForbidden()
	{
		//@todo 403
		header('HTTP/1.0 403 Forbidden');
		die("403");
	}

	/**
	 * Affiche une page 404
	 */
	public function showNotFound()
	{
		//@todo 404
		header('HTTP/1.0 404 Not Found');
		die("404");
	}

	/**
	 * Récupère l'utilisateur actuellement connecté
	 */
	public function getUser()
	{
		$authenticationManager = new AuthentificationManager();
		$user = $authenticationManager->getLoggedUser();
		return $user;
	}

	/**
	 * Autorise l'accès à un ou plusieurs rôles
	 * 		
	 * @param  mixed $roles Tableau de rôles, ou chaîne pour un seul
	 */
	public function allowTo($roles)
	{
		if (!is_array($roles)){
			$roles = [$roles];
		}
		$authorizationManager = new AuthorizationManager();
		foreach($roles as $role){
			if ($authorizationManager->isGranted($role)){
				return true;
			}
		}

		$this->showForbidden();
	}

}