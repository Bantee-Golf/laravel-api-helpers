<?php


namespace EMedia\Api\Console\Commands;


use App\Entities\Auth\UsersRepository;
use App\Http\Kernel;
use Closure;
use EMedia\Api\Docs\APICall;
use EMedia\Api\Docs\Param;
use EMedia\Api\Exceptions\APICallsNotDefinedException;
use EMedia\Api\Exceptions\DocumentationModeEnabledException;
use EMedia\Api\Exceptions\UndocumentedAPIException;
use EMedia\Devices\Auth\DeviceAuthenticator;
use EMedia\PHPHelpers\Files\DirManager;
use Illuminate\Console\Command;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class GenerateDocsCommand extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'generate:docs 
								{--user-id=3 : Default user ID to access the API}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate API documentation';

	/**
	 * The router instance.
	 *
	 * @var \Illuminate\Routing\Router
	 */
	protected $router;

	/**
	 * An array of all the registered routes.
	 *
	 * @var \Illuminate\Routing\RouteCollection
	 */
	protected $routes;

	protected $docBuilder;

	protected $user;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Router $router)
	{
		parent::__construct();

		$this->router = $router;
		$this->routes = $router->getRoutes();
	}

	/**
	 *
	 * Define the default headers for all routes
	 *
	 */
	protected function defineDefaultHeaders()
	{
		try {
			document(function () {
				return (new APICall)->setDefine('default_headers')
									->setHeaders([
										(new Param('Accept', 'String', 'Set to `application/json`'))->setDefaultValue('application/json'),
										(new Param('x-api-key', 'String', 'API Key')),
										(new Param('x-access-token', 'String', 'Unique user authentication token')),
									]);
			});
		} catch (DocumentationModeEnabledException $ex) {
			// Do Nothing
			// This exception will always be thrown while in documentation mode
		}
	}

	/**
	 * Execute the console command.
	 *
	 */
	public function handle()
	{
		$user = (app(UsersRepository::class))->find($this->option('user-id'));

		if (!$user) {
			$this->error('A user with an ID' . $this->option('user-id') . ' is not found. Provide a new user user with the `--user-id` option.');
			return;
		}
		$this->user = $user;

		if (count($this->routes) === 0) {
			$this->error("Your application doesn't have any routes. Aborting...");
			return;
		}

		putenv('DOCUMENTATION_MODE=true');
		$this->docBuilder = app('emedia.api.builder');

		$this->defineDefaultHeaders();
		try {
			$this->hitRoutesAndLoadDocs();
			$this->createDocSourceFiles();
			$this->createSwaggerJson();

			$this->info('');
			$this->info('To complete, run `apidoc -i resources/docs -o public_html/docs/api`');

		} catch (UndocumentedAPIException $ex) {
			$this->error($ex->getMessage());
		} catch (APICallsNotDefinedException $ex) {
			$this->error('No APICalls defined. Defined the APICalls before trying to generate the documents again. Aborting...');
		} catch (\BadMethodCallException $ex) {
			// Exception caught earlier, nothing to do here
		} catch (MethodNotAllowedHttpException $ex) {
			// Exception caught earlier, nothing to do here
		} catch (ValidationException $ex) {
			$this->error('ValidationException detected. Have you documented this API?');
			$this->error($ex->getMessage());
		}
	}


	/**
	 *
	 * Hit the API routes and load the defined route documentation to memory
	 *
	 * @throws UndocumentedAPIException
	 */
	protected function hitRoutesAndLoadDocs()
	{
		// filter the API routes from other HTTP routes
		$apiRoutes = new Collection();
		foreach ($this->routes as $route) {
			if (strpos($route->uri(), 'api') === 0) {
				$apiRoutes->push($route);
			}
		}

		foreach ($apiRoutes as $route) {
			// split route info from a Route
			$routeInfo = $this->getRouteInformation($route);

			// set interceptor
			$this->docBuilder->setInterceptor($routeInfo['method'], $routeInfo['uri'], $routeInfo['action']);

			try {
				// output
				$this->info('Sending ' . $routeInfo['method'] . ' request to ' . $routeInfo['url'] . '...');

				// hit the route, and get the exception
				$this->callRoute($routeInfo['method'], $routeInfo['url']);

				// if the documentation is defined, we should not reach here
				// so throw a new exception
				throw new UndocumentedAPIException("Route {$routeInfo['url']} does not have an API documented");

			} catch (DocumentationModeEnabledException $ex) {
				// do nothing.
				// The exception will always be thrown while in documentation mode
			} catch (\BadMethodCallException $ex) {
				$this->error('Route error on ' . $routeInfo['url']);
				$this->error($ex->getMessage());

				throw $ex;
			} catch (MethodNotAllowedHttpException $ex) {
				$this->error('Route error accessing ' . $routeInfo['url']);
				$this->error('Have you checked your middleware?');
				$this->error(MethodNotAllowedHttpException::class);

				throw $ex;
			}
		}

		// check if we found any defined calls
		$items = $this->docBuilder->getApiCalls();
		$itemCount = count($items);

		$this->info("");
		$this->info("API Doc Builder found {$itemCount} defined APICalls.");

		if (!$itemCount) {
			throw new APICallsNotDefinedException();
		}
	}


	/**
	 *
	 * Create swagger 2.0 json file
	 *
	 * @throws \EMedia\PHPHelpers\Exceptions\FIleSystem\DirectoryNotCreatedException
	 */
	protected function createSwaggerJson()
	{
		$items = $this->docBuilder->getApiCalls();

		$docsFolder = public_path('docs');
		DirManager::makeDirectoryIfNotExists($docsFolder);

		$outputPath = $docsFolder . DIRECTORY_SEPARATOR . 'swagger.json';
		$environmentFilePath = $docsFolder . DIRECTORY_SEPARATOR . 'postman_environment.json';

		$urlParts = parse_url(config('app.url'));

		// Structure
		// https://swagger.io/docs/specification/2-0/basic-structure/
		$schema = [
			'swagger' => '2.0',
			'info' => [
				'title' => config('app.name') . ' Backend API',
				// 'description' => 'The description',
				'version' => '1.0.0',
			],
			'host' => $urlParts['host'],
			'schemes' => [
				$urlParts['scheme'],
			],
			'produces' => [
				'application/json',
			],
			'basePath' => "/",
			'paths' => [],
		];

		$environment = [
			'name' => config('app.name') . ' Environment',
			'_postman_variable_scope' => 'environment',
			'values' => [],
		];

		foreach ($items as $item) {
			/** @var APICall $item */

			$route = $item->getRoute();
			if (empty($route)) continue;

			$parameters = [];

			// set parameters
			/** @var Param $param */
			$params = $item->getParams();
			$headers = $item->getHeaders();
			$allParams = (new Collection())->merge($headers)->merge($params);

			// check for API use calls and merge the headers
			foreach ($item->getUse() as $useName) {
				/** @var APICall $childApiCalls */
				$childApiCalls = $this->docBuilder->findByDefinition($useName);
				if ($childApiCalls) {
					$allParams = $allParams->merge($childApiCalls->getParams())->merge($childApiCalls->getHeaders());
				}
			}

			foreach ($allParams as $param) {
				$dataType = $param->getDataType();

				// if this is an array, skip it
				// because it should be captured by the array's fields anyway
				if ($dataType === 'Array') continue 1;

				// if the parameter contains a `.`, it's a data array
				$name = '';
				$nameParts = explode('.', $param->getName());
				$namePartsCount = count($nameParts);
				if ($namePartsCount <= 1) {
					$name = $nameParts[0];
				} else {
					// rewrite nested variable names
					// clinics.staff.dogs.name
					// clinics[0] [staff][0] [dogs][0] [name]
					for ($i = 0, $iMax = $namePartsCount; $i < $iMax; $i++) {
						if ($i === 0) {
							$name = $nameParts[$i] . '[0]';
						} elseif ($i === $iMax - 1) {
							$name .= '[' . $nameParts[$i] . ']';
						} else {
							$name .= '[' . $nameParts[$i] . '][0]';
						}
					}
				}

				// if `produces` is the same, we don't have to set the header again
				if ($name === 'Accept' && $param->getDefaultValue() === 'application/json') {
					continue;
				}

				$paramData = [
					'name'        => $name,
					'in'          => $param->getLocation(),
					'required'    => $param->getRequired(),
					'description' => $param->getDescription(),
					'type'        => $dataType,
					// 'schema'      => [],
				];
//				if (!empty($default = $param->getDefaultValue())) {
//					$paramData['schema']['default'] = $default;
//					$paramData['schema']['type'] = strtolower($param->getDataType());
//				}
				$parameters[] = $paramData;

				$existingParams = array_filter($environment['values'], function ($value) use ($name) {
					return ($value['key'] === $name);
				});

				if (!count($existingParams)) {
					$environment['values'][] = [
						'key' => $name,
						'value' => $param->getDefaultValue(),
						'description' => $param->getDescription(),
						'type' => $param->getDataType(),
						'enabled' => true,
					];
				}
			}

//			$method = [
//				strtolower($item->getMethod()) => [
//					'summary' => $item->getName(),
//					// 'consumes' => ['application/x-www-form-urlencoded'],
//					'description' => $item->getDescription(),
//					'parameters' => $parameters,
//				]
//			];

			$schema['paths'][$route][strtolower($item->getMethod())] = [
				'summary' => $item->getName(),
				'consumes' => ['application/x-www-form-urlencoded'],
				'description' => $item->getDescription(),
				'parameters' => $parameters,
//					'responses' => [
//						'200' => [
//							'description' => $item->getSuccessResponse(),
//						]
//					]
			];
		}

		file_put_contents($outputPath, json_encode($schema, JSON_PRETTY_PRINT));
		$this->info("Swagger 2.0 File - " . str_replace(base_path(), '', $outputPath));

		file_put_contents($environmentFilePath, json_encode($environment, JSON_PRETTY_PRINT));
		$this->info("Postman Environment File - " . str_replace(base_path(), '', $environmentFilePath));
	}

	/**
	 *
	 * Create the documentation source files
	 *
	 * @throws \EMedia\PHPHelpers\Exceptions\FIleSystem\DirectoryNotCreatedException
	 */
	protected function createDocSourceFiles()
	{
		$items = $this->docBuilder->getApiCalls();

		$docsFolder = resource_path('docs/auto_generated');
		DirManager::makeDirectoryIfNotExists($docsFolder);

		$this->deleteFilesInDirectory($docsFolder, 'coffee');

		foreach ($items as $item) {
			/** @var APICall $item */
			$outputFile = snake_case($item->getGroup() . '.coffee');
			$outputPath = $docsFolder . DIRECTORY_SEPARATOR . $outputFile;

			$lines = [];
			$lines[] = "# ************************************************* #";
			$lines[] = "#       AUTO-GENERATED. DO NOT EDIT THIS FILE.      #";
			$lines[] = "# ************************************************* #";
			$lines[] = "#    Create your files in `resources/docs/manual`   #";
			$lines[] = "# ************************************************* #";
			$lines[] = $item->getApiDoc();
			$lines[] = '';
			file_put_contents($outputPath, implode("\r\n", $lines), FILE_APPEND);
		}

		$this->info("File(s) generated at {$docsFolder}");
	}

	/**
	 *
	 * Delete old files
	 *
	 * @param $dirPath
	 * @param $fileExtension
	 */
	protected function deleteFilesInDirectory($dirPath, $fileExtension)
	{
		array_map('unlink', glob("$dirPath/*.$fileExtension"));
	}


	/**
	 *
	 * Hit a route through application
	 *
	 * @param $method
	 * @param $url
	 */
	public function callRoute($method, $url)
	{
		$request = Request::create($url, $method);

		$apiKeys = env('API_KEY');
		$apiKey  = array_first(explode(',', $apiKeys));
		if (empty($apiKey)) {
			$this->error("AN API_KEY not found on `.env` file");
		}

		$request->headers->set('x-api-key', $apiKey);
		if ($this->user) {
			$accessToken = DeviceAuthenticator::getAnAccessTokenForUserId($this->user->id);
			if (empty($accessToken)) {
				$this->error("An access token not found for user ID {$this->user->id}");
			} else {
				$request->headers->set('x-access-token', $accessToken);
			}
		}

		/** @var Response $response */
		/** @var Kernel $kernel */
		$kernel = app()['Illuminate\Contracts\Http\Kernel'];
		$response = $kernel->handle($request);
		if ($response->exception) {
			throw $response->exception;
		}
	}

	/**
	 * Get the route information for a given route.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return array
	 */
	protected function getRouteInformation(Route $route)
	{
		$methods = $route->methods();
		if (count($methods) === 1) {
			$method = $methods[0];
		} else {
			if (in_array('GET', $methods)) {
				$method = 'GET';
			}
		}

		return [
			'host'   => $route->domain(),
			'method' => $method,
			'methods' => $route->methods(),
			'uri'    => $route->uri(),
			'url'	 => url($route->uri()),
			'name'   => $route->getName(),
			'action' => ltrim($route->getActionName(), '\\'),
			'middleware' => $this->getMiddleware($route),
			// 'controller' => $route->getController(),
		];
	}

	/**
	 * Get before filters.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return string
	 */
	protected function getMiddleware($route)
	{
		return collect($route->gatherMiddleware())->map(function ($middleware) {
			return $middleware instanceof Closure ? 'Closure' : $middleware;
		})->implode(',');
	}

}
