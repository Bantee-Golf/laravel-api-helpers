<?php

namespace Illuminate\Contracts\Routing;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

/**
 * @method JsonResponse apiSuccess($data = null, $message = '') Send an API success response.
 * @method JsonResponse apiSuccessPaginated(Paginator $data, $message) Send a paginated response.
 *
 * @method JsonResponse apiErrorUnauthorized($message = null) Send 401 response.
 * @method JsonResponse apiErrorAccessDenied($message = null) Send 403 response.
 * @method JsonResponse apiError($message = null, $data = null, $statusCode = 422) Send a generic error response.
 */
interface ResponseFactory
{
}