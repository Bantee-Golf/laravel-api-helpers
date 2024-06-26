# Change Log

## Version Compatibility

Use versions as below.

| Laravel Version | Api Helpers Version      | Branch         |
| --------------- |:------------------------:|:--------------:|
| v9              | 5.x                      | master         |  
| v8              | 4.x                      | 4.x			  |  
| v7              | 3.1.x                    | version/v3.x   |
| v6              | 3.0.x                    |                |
| v5.7            | 2.1.x                    |                |  

## v5.0.0
- Laravel 9 Support
- Drop Php 7 Support

## v4.0.0
- Laravel 8 Support
- Replaced PHPHelpers with PHPToolkit

## v3.2.4
- PHPUnit tests added to verify generated tests
- Allow overriding AutoGenerated tests

## v3.2
- New commands added `generate:tests`, `generate:docs-tests`
- Parameters can now be passed as strings to APICall
- Auto configured Postman environments for local and sandboxes
- Postman dynamic variable support
- API responses auto fetched and stored from tests
- Auto-generated API Tests

## v3
- Laravel 7 Support

## v2.1.1
- Added auto generated model definitions 
- Prevent running the command in production env.
- Fix null description and query param.
- Allow user to customise consume param on APICall

## v2.1
- Changed `ApiSuccessPaginated` to split the pagination data.
- Documentation builder changed to create swagger and postman files separately.
- Fixed swagger syntax 

## v2.0
- Added documentation builder

## v1.1.0
- Changed `data` to `payload`
- Fixed controller trait to handle `ValidationException` for json.
