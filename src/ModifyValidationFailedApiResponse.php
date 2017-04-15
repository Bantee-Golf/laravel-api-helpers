<?php
namespace EMedia\Api;

use Illuminate\Http\Request;

trait ModifyValidationFailedAPIResponse
{
	/**
	 * Create the response for when a request fails validation.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  array  $errors
	 * @return \Illuminate\Http\Response
	 */
	protected function buildFailedValidationResponse(Request $request, array $errors)
	{
		if (($request->ajax() && ! $request->pjax()) || $request->wantsJson()) {
			$errors = array_flatten($errors);
			$errors = implode(' ', $errors);
			return response()->apiError($errors, 422);	// HTTP_UNPROCESSABLE_ENTITY
		}

		return redirect()->to($this->getRedirectUrl())
						 ->withInput($request->input())
						 ->withErrors($errors, $this->errorBag());
	}
}