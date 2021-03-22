<?php

/**
 * Helper functions for user
 */

/**
 * Function to generate password for user. As FusionPBX has it`s own vision
 * how password should be generated and encrypted so we shouldn`t break that logic.
 * This should be used instead of Laravel methods.
 * Function generates salt and calculates the md5 hash of password and salt combination.
 *
 * @param string $password User passowrd
 * @return array|null ['password' => 'value', 'salt' => 'value'] or null.
 */
if (!function_exists('encrypt_password_with_salt')) {

    function encrypt_password_with_salt($password)
    {
        if (!empty($password) && !is_null($password)) {
            $data['salt'] = \Str::uuid();
            $data['password'] = md5($data['salt'] . $password);

            return $data;
        }

        return null;
    }
}
