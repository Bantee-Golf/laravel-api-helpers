<?php


namespace EMedia\Api\Domain;


use EMedia\PHPHelpers\Files\Loader;
use Illuminate\Database\Eloquent\Model;

class ModelDefinition
{


	/**
	 *
	 * Get all model definitions
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	public function getAllModelDefinitions()
	{
		if (!config('oxygen.api.includeModelDefinitions', false)) return [];

		$models = $this->getAllModels();

		$hiddenModelDefinitions = config('oxygen.api.hiddenModelDefinitionClasses', []);

		$definitions = [];
		foreach ($models as $model) {
			$definition = $this->getModelDefinition($model);
			if (!in_array($definition['name'], $hiddenModelDefinitions)) {
				$definitions[$definition['name']] = $definition['definition'];
			}
		}

		return $definitions;
	}

	/**
	 *
	 * Return the default error definitions
	 *
	 * @return array
	 */
	public function getAllErrorDefinitions()
	{
		return [
			'ApiErrorUnauthorized' => [
				'type' => 'object',
				'properties' => [
					'message' => [ 'type' => 'string' ],
					'result'  => [ 'type' => 'boolean', 'default' => true ],
					'payload' => [ 'type' => 'object' ],
				],
			],
			'ApiErrorAccessDenied' => [
				'type' => 'object',
				'properties' => [
					'message' => [ 'type' => 'string' ],
					'result'  => [ 'type' => 'boolean', 'default' => true ],
					'payload' => [ 'type' => 'object' ],
				],
			],
			'ApiError' => [
				'type' => 'object',
				'properties' => [
					'message' => [ 'type' => 'string' ],
					'result'  => [ 'type' => 'boolean', 'default' => true ],
					'payload' => [ 'type' => 'object' ],
				],
			],
		];
	}

	/**
	 *
	 * Return all declared definitions
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	public function getAllDefinitions()
	{
		return array_merge($this->getAllModelDefinitions(), $this->getAllErrorDefinitions());
	}

	/**
	 *
	 * Get all models for this project
	 *
	 * @return array
	 */
	protected function getAllModels($directoryList = [])
	{
		if (empty($directoryList)) {
			$directories = [
				app_path('Entities'),
			];
		}

		foreach ($directories as $dirPath) {
			Loader::includeAllFilesFromDirRecursive($dirPath);
		}

		$response = [];
		foreach (get_declared_classes() as $class) {
			if (is_subclass_of($class, Model::class)) {
				$response[] = $class;
			}
		}

		return $response;
	}

	/**
	 *
	 * Get column definitions for a model
	 * Logic from https://github.com/beyondcode/laravel-er-diagram-generator
	 *
	 * @param string $model
	 *
	 * @return mixed
	 */
	public function getTableColumnsFromModel(string $model)
	{
		$model = app($model);

		$table = $model->getConnection()->getTablePrefix() . $model->getTable();
		$schema = $model->getConnection()->getDoctrineSchemaManager($table);
		$databasePlatform = $schema->getDatabasePlatform();
		$databasePlatform->registerDoctrineTypeMapping('enum', 'string');
		$database = null;
		if (strpos($table, '.')) {
			list($database, $table) = explode('.', $table);
		}
		return $schema->listTableColumns($table, $database);
	}


	/**
	 *
	 * Build the model definition for a given model by reading the database columns
	 *
	 * @param $class
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	protected function getModelDefinition($class): array
	{
		$columns = $this->getTableColumnsFromModel($class);
		/** @var \Doctrine\DBAL\Schema\Column $column */
		$model = new $class;

		$fields = [];
		foreach ($columns as $column) {
			$fields[$column->getName()] = $column->getType()->getName();
		}

		// append visible fields
		$visibleFields = $model->getVisible();
		$filteredFields = [];
		if (empty($visibleFields)) {
			$filteredFields = $fields;
		} else {
			foreach ($visibleFields as $visibleKey) {
				if (isset($fields[$visibleKey])) {
					$filteredFields[$visibleKey] = $fields[$visibleKey];
				} else {
					$filteredFields[$visibleKey] = 'string';
				}
			}
		}

		// remove hidden fields
		foreach ($model->getHidden() as $hiddenKey) {
			unset($filteredFields[$hiddenKey]);
		}

		$properties = [];
		foreach ($filteredFields as $key => $dataType) {
			$properties[$key] = ['type' => $dataType];
		}

		$reflect = new \ReflectionClass($model);

		return [
			'name' => $reflect->getShortName(),
			'definition' => [
				'type' => 'object',
				'properties' => $properties,
			],
		];
	}


}