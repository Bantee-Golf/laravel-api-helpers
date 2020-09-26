<?php

namespace EMedia\Api\Tests\Feature;

use App\Controllers\Http\TestController;
use Illuminate\Support\Facades\Route;

class RouteDocumentRegistrationTest extends \EMedia\Api\Tests\TestCase
{

	/**
	 *
	 * Test regular HTTP traffic goes through
	 *
	 */
	public function testNormalHTTPRequestsBypassDocFunction()
	{
		$response = $this->get('api/v1/index');
		$response->assertStatus(200);
	}

	/**
	 *
	 * Test undocumented APIs throw errors
	 *
	 */
	public function testHTTPCallTriggersDocumentFunction()
	{
		Route::get('/api/v1/undocumented', '\App\Controllers\Http\TestController@undocumented');

		$testUserId = 4;

		$this->mockDataSources($testUserId);

		$this->artisan("generate:docs --test-user-id={$testUserId}");

		$this->expectsConsoleOutput([
			'does not have an API documented',
			'API_KEY not found'
		]);
	}

	/**
	 *
	 * Test API_KEY is detected
	 *
	 */
	public function testAPIKeyIsDetectedFromEnv()
	{
		$testUserId = 4;
		$this->mockDataSources($testUserId);

		$this->setApiKey();
		$this->artisan("generate:docs --test-user-id={$testUserId}");

		$this->expectsNotInConsoleOutput([
			'API_KEY not found'
		]);

		$this->echoConsoleOutput();
	}

	/**
	 *
	 * Test for generating postman_collection, swagger files, apidoc files
	 *
	 * @throws \EMedia\PHPHelpers\Exceptions\FileSystem\FileNotFoundException
	 */
	public function testDocSourcesGenerated()
	{
		$generatedFiles = [
			public_path('docs/postman_collection.json'),
			public_path('docs/postman_collection.yml'),
			public_path('docs/swagger.json'),
			public_path('docs/swagger.yml'),
			resource_path('docs/apidoc/auto_generated/test.coffee')
		];
		foreach ($generatedFiles as $path) {
			$this->assertFileDoesNotExist($path);
		}

		$testUserId = 4;
		$this->mockDataSources($testUserId);

		$this->setApiKey();
		$this->artisan("generate:docs --test-user-id={$testUserId}");

		$this->expectsNotInConsoleOutput([
			'does not have an API documented',
			'API_KEY not found'
		]);

		foreach ($generatedFiles as $path) {
			$this->assertFileExists($path);
		}

		$this->assertFileHasText(
			resource_path('docs/apidoc/auto_generated/test.coffee'),
			TestController::INDEX_METHOD_NAME
		);
	}
}
