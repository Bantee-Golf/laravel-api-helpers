<?php


namespace EMedia\Api\Tests;


use App\Controllers\Http\TestController;
use App\Entities\Auth\UsersRepository;
use App\User;
use EMedia\Api\ApiServiceProvider;
use EMedia\Api\Domain\Traits\NamesAndPathLocations;
use EMedia\Devices\Auth\DeviceAuthenticator;
use EMedia\PHPHelpers\Exceptions\FileSystem\FileNotFoundException;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class TestCase extends \Orchestra\Testbench\TestCase
{

	public function setUp(): void
	{
		parent::setUp();

		$this->cleanupGeneratedFiles();

		$this->registerRoutes();

		$this->withoutMockingConsoleOutput();

		$this->beforeApplicationDestroyed(function () {
			// if you need to debug, comment this and see the files generated in
			// vendor/orchestra/testbench-core
			$this->cleanupGeneratedFiles();
		});
	}

	protected function getEnvironmentSetUp($app)
	{
		$app->useEnvironmentPath(__DIR__);
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);
	}

	protected function registerRoutes()
	{
		Route::get('/api/v1/index', '\App\Controllers\Http\TestController@index');
	}

	protected function generatedTestFilePaths()
	{
		$testsDir = NamesAndPathLocations::getTestsAutoGenDir('v1');

		return [
			base_path('tests/'.$testsDir.'/APIBaseTestCase.php'),
			base_path('tests/'.$testsDir.'/Test'.TestController::INDEX_METHOD_NAME.'APITest.php'),
		];
	}

	public function cleanupGeneratedFiles()
	{
		$dirs = [
			public_path('docs'),
			resource_path('docs'),
			base_path('tests/'.NamesAndPathLocations::getTestsAutoGenDir()),
		];

		foreach ($dirs as $path) {
			File::deleteDirectory($path);
		}
	}

	protected function getPackageProviders($app)
	{
		return [
			ApiServiceProvider::class,
			RouteServiceProvider::class,
		];
	}

	protected function getLocalRoot($suffix = null)
	{
		$path = __DIR__.'/../laravel';

		if ($suffix) {
			$path .= '/'. ltrim($suffix, '/');
		}

		return $path;
	}

	protected function getConsoleOutput()
	{
		return Artisan::output();
	}

	protected function echoConsoleOutput()
	{
		echo $this->getConsoleOutput();
	}

	protected function expectsConsoleOutput(array $lines)
	{
		$output = $this->getConsoleOutput();

		foreach ($lines as $line) {
			$this->assertStringContainsString($line,$output);
		}

		return $output;
	}

	protected function expectsNotInConsoleOutput(array $lines)
	{
		$output = $this->getConsoleOutput();

		foreach ($lines as $line) {
			$this->assertStringNotContainsString($line,$output);
		}

		return $output;
	}

	protected function assertFileHasText($path, $text)
	{
		if (!file_exists($path)) {
			throw new FileNotFoundException("File $path not found");
		}

		$this->assertStringContainsString($text, file_get_contents($path));

		return $this;
	}

	protected function setApiKey()
	{
		putenv('API_KEY="123-123-123-123"');

		return $this;
	}

	protected function mockDataSources($testUserId)
	{
		$this->mock(UsersRepository::class, function ($mock) use ($testUserId) {
			/** @var Mockery $mock */
			$mock->shouldReceive()->find($testUserId)
				->once()
				->andReturn(new User(['id' => $testUserId]));

			$mock->shouldReceive()->find('3')->once()
				->andReturn(new User(['id' => 3]));
		});

		$this->partialMock(DeviceAuthenticator::class, function ($mock) use ($testUserId) {
			/** @var Mockery $mock */
			$mock->shouldReceive()->getAnAccessTokenForUserId($testUserId)
				->andReturn('1234-1234');
		});
	}
}
