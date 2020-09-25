<?php
namespace App\Controllers\Http;


use EMedia\Api\Docs\APICall;

class TestController extends Controller
{

	const INDEX_METHOD_NAME = 'INDEX_METHOD_NAME';

	public function undocumented()
	{
		return [];
	}

	public function index()
	{
		document(function () {
			return (new APICall())
				->setName('INDEX_METHOD_NAME');
		});

		return [];
	}

}
