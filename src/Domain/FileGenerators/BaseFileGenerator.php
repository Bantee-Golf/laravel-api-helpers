<?php
namespace EMedia\Api\Domain\FileGenerators;

use EMedia\Api\Exceptions\FileGenerationFailedException;
use EMedia\PHPHelpers\Files\DirManager;
use Illuminate\Contracts\Filesystem\FileExistsException;

abstract class BaseFileGenerator
{

	protected $schema = [];

	abstract public function getOutput();

	/**
	 *
	 * Add MetaData for Environment
	 *
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function addToSchema($key, $value)
	{
		$this->schema[$key] = $value;

		return $this;
	}

	/**
	 *
	 * Write generated output to a JSON file
	 *
	 * @param $outputFilePath
	 * @param bool $overwrite
	 * @return bool
	 * @throws FileExistsException
	 * @throws FileGenerationFailedException
	 * @throws \EMedia\PHPHelpers\Exceptions\FIleSystem\DirectoryNotCreatedException
	 */
	public function writeOutputFile($outputFilePath, $overwrite = true)
	{
		if (!$overwrite && file_exists($outputFilePath)) {
			throw new FileExistsException("File {$outputFilePath} already exists.");
		}

		try {
			$outputString = json_encode($this->getOutput(), JSON_PRETTY_PRINT);
		} catch (\Exception $ex) {
			throw new FileGenerationFailedException("Failed to generate a valid output. " . $ex->getMessage());
		}

		$outputDir = pathinfo($outputFilePath, PATHINFO_DIRNAME);
		DirManager::makeDirectoryIfNotExists($outputDir);

		file_put_contents($outputFilePath, $outputString);

		return true;
	}

}