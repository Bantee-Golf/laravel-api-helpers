<?php
namespace EMedia\Api;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use App\Http\Responses\Response as BaseResponse;

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
		Response::macro('apiSuccess', function ($data = null, $message = '') {
			return Response::json([
				'data'		=> $data,
				'message' 	=> $message,
				'result' 	=> true,
			]);
		});

		Response::macro('apiSuccessPaginated', function ($data = null, $message = '') {
			$data['message'] = $message;
			$data['result']  = true;
			return Response::json($data);
		});

		//
		// Error Messages
		//

		// unauthorized
		Response::macro('apiUnauthorized', function ($message = 'Authentication failed. Try to login again.') {
			return Response::json([
				'message' => $message,
				'result'  => false,
			], BaseResponse::HTTP_UNAUTHORIZED); // 401 Error
		});

		// Generic API authorization error
		Response::macro('apiAccessDenied', function ($message = 'Access denied.') {
			return Response::json([
				'message' => $message,
				'result'  => false,
			], BaseResponse::HTTP_FORBIDDEN); // 403 Error
		});

		// Generic error
		Response::macro('apiError',
			function ($message = 'Unable to process request. Please try again later.',
				$data = null,
				$statusCode = BaseResponse::HTTP_UNPROCESSABLE_ENTITY) {

				return Response::json([
					'message' => $message,
					'data' => $data,
					'result'  => false,
				], $statusCode);

			});
	}


}