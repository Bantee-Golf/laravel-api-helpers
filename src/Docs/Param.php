<?php


namespace EMedia\Api\Docs;


class Param
{

	const LOCATION_HEADER = 'header';
	const LOCATION_FORM = 'formData';
	const LOCATION_COOKIE = 'cookie';
	const LOCATION_PATH = 'path';

	protected $fieldName;
	protected $required = true;
	protected $dataType;
	protected $defaultValue;
	protected $description = '';
	protected $location = self::LOCATION_FORM;

	public function __construct($fieldName = null, $dataType = 'String', $description = null)
	{
		$this->fieldName = $fieldName;
		$this->dataType = $dataType;
		if (!$description && $fieldName) {
			$this->description = ucfirst(reverse_snake_case($fieldName));
		} else {
			$this->description = $description;
		}
	}

	/**
	 * @return bool
	 */
	public function getRequired(): bool
	{
		return $this->required;
	}

	/**
	 * @param bool $required
	 */
	public function required()
	{
		$this->required = true;

		return $this;
	}

	public function optional()
	{
		$this->required = false;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataType(): string
	{
		return ucfirst($this->dataType);
	}

	/**
	 * @param string $dataType
	 */
	public function dataType(string $dataType)
	{
		$this->dataType = $dataType;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}

	/**
	 * @param mixed $defaultValue
	 */
	public function defaultValue($defaultValue)
	{
		$this->defaultValue = $defaultValue;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function description(string $description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->fieldName;
	}

	/**
	 * @param mixed $fieldName
	 */
	public function field($fieldName)
	{
		$this->fieldName = $fieldName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLocation(): string
	{
		return $this->location;
	}

	/**
	 * @param string $location
	 */
	public function setLocation(string $location)
	{
		$this->location = $location;

		return $this;
	}

	/**
	 * @param mixed $defaultValue
	 *
	 * @return Param
	 */
	public function setDefaultValue($defaultValue)
	{
		$this->defaultValue = $defaultValue;

		return $this;
	}

}