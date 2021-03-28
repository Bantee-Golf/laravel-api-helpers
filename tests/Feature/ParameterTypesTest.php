<?php


namespace EMedia\Api\Tests\Feature;

use App\Controllers\Http\TestController;
use Illuminate\Support\Facades\Route;

class ParameterTypesTest extends \EMedia\Api\Tests\TestCase
{

	public function setUp(): void
	{
		parent::setUp();

		$this->setApiKey();

		$this->mockDataSources(self::getTestUserId());
	}

	public function testBooleanParamTypeDetected()
	{
		Route::get('/api/v1/correctParameterTypes', '\App\Controllers\Http\TestController@correctParameterTypes');

		$this->artisan("generate:docs --no-authenticate-web-apis --test-user-id=" . self::getTestUserId());
		$this->artisan("generate:api-tests --force");

		$path = $this->assertTestGenerated(TestController::PARAM_TYPES);

		$this->assertFileHasText($path, [
			"\$data['is_boolean'] = true;",
		]);
	}
}
