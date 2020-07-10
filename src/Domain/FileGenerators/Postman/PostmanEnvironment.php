<?php


namespace EMedia\Api\Domain\FileGenerators\Postman;


use EMedia\Api\Exceptions\FileGenerationFailedException;
use EMedia\PHPHelpers\Files\DirManager;
use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Support\Collection;

class PostmanEnvironment
{

	protected $meta = [];

	/**
	 * @var Collection
	 */
	protected $variables;

	public function __construct()
	{
		$this->addMeta('_postman_variable_scope', 'environment');
		$this->variables = new Collection();
	}

	/**
	 *
	 * Set name of the Environment
	 *
	 * @param $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->addMeta('name', $name);

		return $this;
	}

	/**
	 *
	 * Add MetaData for Environment
	 *
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function addMeta($key, $value)
	{
		$this->meta[$key] = $value;

		return $this;
	}

	/**
	 *
	 * Add a new Variable
	 *
	 * @param $variableName
	 * @param $initialValue
	 * @return $this
	 */
	public function addVariable($variableName, $initialValue)
	{
		$existingVariable = $this->getVariable($variableName);

		if ($existingVariable) {
			$existingVariable['value'] = $initialValue;
		} else {
			$this->variables->push([
				'key' => $variableName,
				'value' => $initialValue,
			]);
		}

		return $this;
	}

	/**
	 *
	 * Get an existing variable (as an array) if exists
	 *
	 * @param $variableName
	 * @return mixed
	 */
	public function getVariable($variableName)
	{
		return $this->variables->first(function ($var) use ($variableName) {
			return $var['key'] === $variableName;
		});
	}

	/**
	 *
	 * Get generated output array
	 *
	 * @return array
	 */
	public function getOutput()
	{
		$output = $this->meta;

		$output['values'] = $this->variables;

		return $output;
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
			throw new FileGenerationFailedException("Failed to generate a valid output.");
		}

		$outputDir = pathinfo($outputFilePath, PATHINFO_DIRNAME);
		DirManager::makeDirectoryIfNotExists($outputDir);

		file_put_contents($outputFilePath, $outputString);

		return true;
	}


}