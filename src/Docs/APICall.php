<?php


namespace EMedia\Api\Docs;


use Illuminate\Http\Response;

class APICall
{

	const CONSUME_JSON = 'application/json';
	const CONSUME_MULTIPART_FORM = 'multipart/form-data';
	const CONSUME_FORM_URLENCODED = 'application/x-www-form-urlencoded';

	protected $version = '1.0.0';
	protected $method = '';
	protected $name;
	protected $route;
	protected $group;
	protected $params = [];
	protected $description;

//	protected $successResponse;
//	protected $successResponseName;
	protected $successParams = [];
	protected $requestExample = [];

	protected $headers = [];
	protected $define = [];
	protected $use = [];

	protected $addDefaultHeaders = true;

	protected $successExamples = [];
	protected $errorExamples = [];
	protected $successObject;
	protected $successPaginatedObject;

	protected $consumes = [];

	/**
	 *
	 * Returns the composed apiDoc
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getApiDoc()
	{
		$lines = [];

		$lines[] = "###";

		if (!empty($define = $this->getDefine())) {
			$lines[] = "@apiDefine {$define['title']} {$define['description']}";
		}

		$description = $this->getDescription();
		if (!empty($description)) {
			$lines[] = "@apiDescription {$description}";
		}

		$lines[] = "@apiVersion {$this->getVersion()}";
		$lines[] = "@api {{$this->getMethod()}} {$this->getRoute()} {$this->getName()}";
		$lines[] = "@apiGroup " . ucwords($this->getGroup());

		// params
		foreach ($this->params as $param) {
			if ($param instanceof Param) {
				// $param = $value;
				/** @var Param $param */
				$fieldName = $param->getName();

				if (empty($fieldName)) throw new \Exception('The parameters requires a fieldname');

				if (!$param->getRequired()) {
					$fieldName = '[' . $fieldName . ']';
				}
				$lines[] = "@apiParam {{$param->getDataType()}} {$fieldName} {$param->getDescription()}";
			} else {
				$lines[] = "@apiParam {$param}";
			}
		}

		// success params
		foreach ($this->successParams as $param) {
			if ($param instanceof Param) {
				// $param = $value;
				/** @var Param $param */
				$fieldName = $param->getName();

				if (empty($fieldName)) throw new \Exception('The parameters requires a fieldname');

				if (!$param->getRequired()) {
					$fieldName = '[' . $fieldName . ']';
				}
				$lines[] = "@apiSuccess {{$param->getDataType()}} {$fieldName} {$param->getDescription()}";
			} else {
				$lines[] = "@apiSuccess {$param}";
			}
		}

		// headers
		foreach ($this->headers as $param) {
			if ($param instanceof Param) {
				// $param = $value;
				/** @var Param $param */
				$fieldName = $param->getName();

				if (empty($fieldName)) throw new \Exception('The parameters requires a fieldname');

				if (!$param->getRequired()) {
					$fieldName = '[' . $fieldName . ']';
				}
				$lines[] = "@apiHeader {{$param->getDataType()}} {$fieldName} {$param->getDescription()}";
			} else {
				$lines[] = "@apiHeader {$param}";
			}
		}

		// use
		foreach ($this->use as $use) {
			$lines[] = "@apiUse $use";
		}

		$requestExampleParams = $this->getRequestExample();
		if (!empty($requestExampleParams)) {
			$lines[] = "@apiParamExample {json} Request Example ";
			$lines[] = json_encode($requestExampleParams);
		}

		$successExamples = $this->successExamples;
		foreach ($successExamples as $successExample) {
			$lines[] = "@apiSuccessExample {json} Success-Response / HTTP {$successExample['statusCode']} {$successExample['message']}";
			$lines[] = $successExample['text'];
		}

		$errorExamples = $this->errorExamples;
		foreach ($errorExamples as $errorExample) {
			$lines[] = "@apiErrorExample {json} Error-Response / HTTP {$errorExample['statusCode']} {$errorExample['message']}";
			$lines[] = $errorExample['text'];
		}

		$lines[] = "###";

		return implode("\r\n", $lines);
	}


	/**
	 * @return mixed
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * @param mixed $route
	 */
	public function setRoute($route)
	{
		$this->route = $route;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @param mixed $params
	 */
	public function setParams($params)
	{
		$this->params = $params;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param mixed $group
	 */
	public function setGroup($group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * @param string $method
	 */
	public function setMethod(string $method)
	{
		$this->method = $method;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param mixed $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getRequestExample(): array
	{
		return $this->requestExample;
	}

	/**
	 * @param array $requestExample
	 */
	public function setRequestExample(array $requestExample)
	{
		$this->requestExample = $requestExample;

		return $this;
	}

//	/**
//	 * @return mixed
//	 */
//	public function getSuccessResponse()
//	{
//		return $this->successResponse;
//	}

//	/**
//	 * @param mixed $successResponse
//	 */
//	public function setSuccessResponse($responseText, $label = 'Success Response - HTTP/200 OK')
//	{
//		$this->successResponse = $responseText;
//		$this->successResponseName = $label;
//
//		return $this;
//	}


	/**
	 * @param array $successParams
	 */
	public function setSuccessParams(array $successParams)
	{
		$this->successParams = $successParams;

		return $this;
	}

	/**
	 * @param array $headers
	 */
	public function setHeaders(array $headers)
	{
		foreach ($headers as $header) {
			$this->addHeader($header);
		}

		return $this;
	}

	/**
	 *
	 * Add a header to headers list
	 *
	 * @param Param $param
	 * @param bool $allowDuplicate
	 * @return $this
	 */
	public function addHeader(Param $param, $allowDuplicate = false)
	{
		if ($param instanceof Param) {
			$param->setLocation(Param::LOCATION_HEADER);
		}

		/** @var Param $header */
		$isAdded = false;
		if (!$allowDuplicate) {
			foreach ($this->headers as &$header) {
				if ($header->getName() === $param->getName()) {
					$header = $param;
					$isAdded = true;
				}
			}
		}

		if (!$isAdded) $this->headers[] = $param;

		return $this;
	}


	/**
	 *
	 * Set only the API key header
	 * This is just a helper method that clears the defaults and sets the `x-api-key` header
	 *
	 * @return $this
	 */
	public function requireApiKeyHeader()
	{
		$this->noDefaultHeaders();

		$this->setHeaders([
			(new Param('Accept', Param::TYPE_STRING, '`application/json`'))
									->setDefaultValue(self::CONSUME_JSON),
			(new Param('x-api-key', 'String', 'API Key'))
									->setDefaultValue('{{x-api-key}}'),
		]);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	/**
	 * @param string $version
	 */
	public function setVersion(string $version)
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDefine()
	{
		return $this->define;
	}

	/**
	 * @param mixed $define
	 */
	public function setDefine($title, $description = '')
	{
		$this->define = [
			'title' => $title,
			'description' => $description,
		];

		return $this;
	}

	/**
	 * @return array
	 */
	public function getUse(): array
	{
		return $this->use;
	}

	/**
	 * @param array $use
	 */
	public function setUse($definedName)
	{
		$this->use[] = $definedName;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isAddDefaultHeaders(): bool
	{
		return $this->addDefaultHeaders;
	}

	/**
	 *
	 */
	public function noDefaultHeaders()
	{
		$this->addDefaultHeaders = false;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 *
	 * @param array $successExamples
	 *
	 * @return APICall
	 */
	public function setSuccessExample($successExample, $statusCode = 200, $message = null): APICall
	{
		$this->successExamples[] = [
			'text' => $successExample,
			'statusCode' => $statusCode,
			'message' => $this->getStatusTextByCode($statusCode, $message),
		];

		return $this;
	}

	/**
	 *
	 * @param array $errorExamples
	 *
	 * @return APICall
	 */
	public function setErrorExample($errorExample, $statusCode = 404, $message = null): APICall
	{
		$this->errorExamples[] = [
			'text' => $errorExample,
			'statusCode' => $statusCode,
			'message' => $this->getStatusTextByCode($statusCode, $message),
		];

		return $this;
	}

	/**
	 * @return array
	 */
//	public function getErrorExamples(): array
//	{
//		return $this->errorExamples;
//	}

	/**
	 *
	 * Reverse an HTTP status code and get the label
	 *
	 * @param      $statusCode
	 * @param null $text
	 *
	 * @return null|string
	 */
	protected function getStatusTextByCode($statusCode, $text = null)
	{
		if ($text) return $text;

		$statusCodes = Response::$statusTexts;
		return isset($statusCodes[$statusCode]) ? $statusCodes[$statusCode] : 'Unknown';
	}

	/**
	 * @return array
	 */
	public function getConsumes(): array
	{
		return $this->consumes;
	}

	/**
	 * @param array $consumes
	 */
	public function setConsumes(array $consumes)
	{
		$this->consumes = $consumes;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSuccessObject()
	{
		return $this->successObject;
	}

	/**
	 * @param mixed $successObject
	 */
	public function setSuccessObject($successObject)
	{
		$this->successObject = $successObject;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSuccessPaginatedObject()
	{
		return $this->successPaginatedObject;
	}

	public function setSuccessPaginatedObject($successPaginatedObject)
	{
		$this->successPaginatedObject = $successPaginatedObject;

		return $this;
	}

}
