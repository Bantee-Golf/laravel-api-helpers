<?php


namespace EMedia\Api\Domain\Postman;

// Refer to
// https://learning.postman.com/docs/writing-scripts/script-references/variables-list/
class PostmanVar
{

    // Pre-defined
    public const REGISTERED_USER_EMAIL 	= 'login_user_email';
    public const REGISTERED_USER_PASS 	= 'login_user_pass';

    // Common
    public const UUID = '$guid';

    // Names
    public const LAST_NAME 	= '$randomLastName';
    public const FIRST_NAME = '$randomFirstName';

    // Domains, Emails, Usernames
    public const EXAMPLE_EMAIL = '$randomExampleEmail';
    public const EMAIL = '$randomEmail';

    // Phone, Address and Location
    public const PHONE = '$randomPhoneNumber';

    // Grammar
    public const PHRASE = '$randomPhrase';

    // TODO: add other variables

    /**
     *
     * Map Postman Dynamic variable names to faker variable names
     * See list at
     * https://learning.postman.com/docs/writing-scripts/script-references/variables-list/
     *
     * @param $varName
     * @return string
     */
    public static function postmanToFaker($varName): string
    {
        switch ($varName) {
            case self::UUID:
                return '$faker->uuid';
                break;
            case self::FIRST_NAME:
                return '$faker->firstName';
                break;
            case self::LAST_NAME:
                return '$faker->lastName';
                break;
            case self::EXAMPLE_EMAIL:
            case self::EMAIL:
                return '$faker->safeEmail';
                break;
            case self::PHONE:
                return '$faker->phoneNumber';
                break;
            case self::PHRASE:
                return '$faker->sentence';
                break;
            default:
                // remove $ sign to avoid conflicts
                return "'" . ltrim($varName, " \t\n\r\0\x0B$") . "'";
        }
    }
}
