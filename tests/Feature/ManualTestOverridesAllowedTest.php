<?php


namespace EMedia\Api\Tests\Feature;

use App\Controllers\Http\TestController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Manual\API\V1\TestMANUAL_OVERRIDEAPITest;

class ManualTestOverridesAllowedTest extends \EMedia\Api\Tests\TestCase
{

	public function setUp(): void
	{
		parent::setUp();

		$this->setApiKey();

		$this->mockDataSources(self::getTestUserId());
	}

	protected function getTestUserId()
	{
		return '4';
	}

	public function testManualOverrideTestsSkipAutoGeneratedOnes()
	{
		$source = $this->getLocalRoot('tests/Feature/Manual/API/V1/TestMANUAL_OVERRIDEAPITest.php');
		self::assertFileExists($source);

		// create the manual file
		$manualFile = base_path('tests/Feature/Manual/API/V1/TestMANUAL_OVERRIDEAPITest.php');
		File::ensureDirectoryExists(dirname($manualFile));
		File::copy($source, $manualFile);

		$autoGenFile = base_path('tests/Feature/AutoGen/API/V1/TestMANUAL_OVERRIDEAPITest.php');
		if (file_exists($autoGenFile)) {
			unlink($autoGenFile);
			self::assertFileDoesNotExist($autoGenFile);
		}

		Route::get('/api/v1/manualOverride', '\App\Controllers\Http\TestController@manualOverride');

		$this->artisan("generate:docs --test-user-id=".self::getTestUserId());
		$this->artisan("generate:api-tests --force");

		// auto-gen test must not be generated, because there's a manual file
		self::assertFileDoesNotExist($autoGenFile);

		// cleanup
		File::delete($manualFile);
		File::delete($autoGenFile);
	}
}
