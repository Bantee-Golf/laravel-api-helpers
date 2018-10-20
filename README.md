# API Helpers for Laravel 5

This package adds the following.

- Custom request responses.

## Install
Add the repository to `composer.json`
```
"repositories": [
	{
	    "type":"vcs",
	    "url":"git@bitbucket.org:elegantmedia/laravel-api-helpers.git"
	}
]
```

```
composer require emedia/api
```

The package will be auto-discovered in Laravel 5.7.

## Usage

### Success
```
return response()->apiSuccess($transaction, $optionalMessage);
```

This will return a JSON response as,
```
{
	payload: {transactionObject or Array},
	message: '',
	result: true
}
```

### Returning a paginated response

Do this because you may need to attach a message and the result type to the message.
```
$users = User::paginate(20);
return response()->apiSuccessPaginated($users, $optionalMessage);
```

Returns
```
{
	message: 'Optional message',
	payload: { paginated data }
	result: true
}
```

### Unauthorized - 401, General Error - 403 (Forbidden)

```
return response()->apiUnauthorized($optionalMessage);
return response()->apiAccessDenied($optionalMessage);
```

Returns (Status code: 401 or 403)
```
{
	message: 'Optional message',
	result: false
}
```

### Generic error
```
return response()->apiError($optionalMessage, $optionalData, $optionalStatusCode);
```

Returns (Unprocessable Entity - 422 by default)
```
{
	message: 'Optional message',
	payload: {object or array},
	result: false
}
```