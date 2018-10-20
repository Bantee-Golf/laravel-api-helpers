<?php
namespace EMedia\Api;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use EMedia\Api\Http\Responses\Response as BaseResponse;

class ApiServiceProvider extends ServiceProvider
{

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerCustomResponses();
	}


	protected function registerCustomResponses()
	{
		// success
		Response::macro('apiSuccess', function ($payload = null, $message = '') {
			return Response::json([
				'payload'	=> $payload,
				'message' 	=> $message,
				'result' 	=> true,
			]);
		});

		Response::macro('apiSuccessPaginated', function ($payload = null, $message = '') {
			return Response::json([
				'payload' => $payload,
				'message' => $message,
				'result'  => true,
			]);
		});

		//
		// Error Messages
		//

		// unauthorized
		Response::macro('apiErrorUnauthorized', function ($message = 'Authentication failed. Try to login again.') {
			return Response::json([
				'message' => $message,
				'payload' => null,
				'result'  => false,
			], BaseResponse::HTTP_UNAUTHORIZED); // 401 Error
		});

		// Generic API authorization error
		Response::macro('apiErrorAccessDenied', function ($message = 'Access denied.') {
			return Response::json([
				'message' => $message,
				'payload' => null,
				'result'  => false,
			], BaseResponse::HTTP_FORBIDDEN); // 403 Error
		});

		// Generic error
		Response::macro('apiError',
			function ($message = 'Unable to process request. Please try again later.',
				$payload = null,
				$statusCode = BaseResponse::HTTP_UNPROCESSABLE_ENTITY) {

				return Response::json([
					'message' => $message,
					'payload' => $payload,
					'result'  => false,
				], $statusCode);

			});
	}


}