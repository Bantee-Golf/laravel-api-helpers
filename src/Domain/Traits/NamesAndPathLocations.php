<?php


namespace EMedia\Api\Domain\Traits;

use EMedia\PHPHelpers\Files\DirManager;

trait NamesAndPathLocations
{
	protected static function getDocsDir($createIfNotExists = false)
	{
		$dirPath = resource_path('docs');

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	/**
	 *
	 * Get storage path for API responses
	 *
	 * @param bool $createIfNotExists 	Create directory if it doesn't exist
	 * @return string 		Directory Path
	 *
	 * @throws \EMedia\PHPHelpers\Exceptions\FIleSystem\DirectoryNotCreatedException
	 */
	protected static function getApiResponsesStorageDir($createIfNotExists = false)
	{
		$dirPath = self::getDocsDir() . DIRECTORY_SEPARATOR . 'api_responses';

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	protected static function getApiResponsesAutoGenDir($createIfNotExists = false)
	{
		$dirPath = self::getApiResponsesStorageDir() . DIRECTORY_SEPARATOR . 'auto_generated';

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	protected static function getApiResponsesManualDir($createIfNotExists = false)
	{
		$dirPath = self::getApiResponsesStorageDir() . DIRECTORY_SEPARATOR . 'manual';

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	public static function getDocsOutputDir($createIfNotExists = false)
	{
		$dirPath = public_path(DIRECTORY_SEPARATOR . 'docs');

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	protected static function getApiDocsOutputDir($createIfNotExists = false)
	{
		$dirPath = self::getDocsOutputDir() . DIRECTORY_SEPARATOR . 'api';

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	protected static function getApiDocsDir($createIfNotExists = false)
	{
		$dirPath = self::getDocsDir() . DIRECTORY_SEPARATOR . 'apidoc';

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	protected static function getApiDocsAutoGenDir($createIfNotExists = false)
	{
		$dirPath = self::getApiDocsDir() . DIRECTORY_SEPARATOR . 'auto_generated';

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	protected static function getApiDocsManualDir($createIfNotExists = false)
	{
		$dirPath = self::getApiDocsDir() . DIRECTORY_SEPARATOR . 'manual';

		if ($createIfNotExists) {
			DirManager::makeDirectoryIfNotExists($dirPath);
		}

		return $dirPath;
	}

	public static function getTestsAutoGenDir($apiVersion = null)
	{
		$path = 'Feature' . DIRECTORY_SEPARATOR . 'AutoGen' . DIRECTORY_SEPARATOR . 'API';

		if ($apiVersion) {
			$path .= DIRECTORY_SEPARATOR . strtoupper($apiVersion);
		}

		return $path;
	}

	public static function getTestFilePath($apiVersion, $relativePath)
	{
		return base_path('tests/'.self::getTestsAutoGenDir($apiVersion).DIRECTORY_SEPARATOR.$relativePath);
	}

	/**
	 *
	 * Delete old files
	 *
	 * @param $dirPath
	 * @param $fileExtension
	 */
	public static function deleteFilesInDirectory($dirPath, $fileExtension)
	{
		array_map('unlink', glob("$dirPath/*.$fileExtension"));
	}
}
