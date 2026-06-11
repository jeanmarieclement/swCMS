<?php

namespace App\Helpers;

/**
 * Validation Helper
 * Provides validation rules for complex input validation
 *
 * @package App\Helpers
 * @author swCMS Team
 */
class ValidationHelper
{
    /**
     * Validate required field
     *
     * @param mixed $value Value to validate
     * @return bool True if not empty
     */
    public static function required($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && count($value) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Validate minimum length
     *
     * @param string $value Value to validate
     * @param int $min Minimum length
     * @return bool True if meets minimum
     */
    public static function minLength(string $value, int $min): bool
    {
        return mb_strlen($value) >= $min;
    }

    /**
     * Validate maximum length
     *
     * @param string $value Value to validate
     * @param int $max Maximum length
     * @return bool True if within maximum
     */
    public static function maxLength(string $value, int $max): bool
    {
        return mb_strlen($value) <= $max;
    }

    /**
     * Validate value is in allowed list
     *
     * @param mixed $value Value to validate
     * @param array $allowed Allowed values
     * @return bool True if in list
     */
    public static function in($value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    /**
     * Validate slug format (alphanumeric with dashes)
     *
     * @param string $value Value to validate
     * @return bool True if valid slug
     */
    public static function slug(string $value): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1;
    }

    /**
     * Validate username format
     *
     * @param string $value Username to validate
     * @return bool True if valid username
     */
    public static function username(string $value): bool
    {
        // Alphanumeric, underscores, 3-20 characters
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $value) === 1;
    }

    /**
     * Validate password strength
     *
     * @param string $password Password to validate
     * @param int $minLength Minimum length (default 8)
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function password(string $password, int $minLength = 8): array
    {
        $errors = [];

        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate data against multiple rules
     *
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldErrors = [];

            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    // Simple rule like 'required', 'email'
                    $ruleName = $rule;
                    $ruleParams = [];
                } elseif (is_array($rule)) {
                    // Rule with parameters like ['min_length', 8]
                    $ruleName = $rule[0];
                    $ruleParams = array_slice($rule, 1);
                } else {
                    continue;
                }

                $valid = match ($ruleName) {
                    'required' => self::required($value),
                    'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                    'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
                    'int' => filter_var($value, FILTER_VALIDATE_INT) !== false,
                    'min_length' => self::minLength($value, $ruleParams[0] ?? 0),
                    'max_length' => self::maxLength($value, $ruleParams[0] ?? 255),
                    'in' => self::in($value, $ruleParams[0] ?? []),
                    'slug' => self::slug($value),
                    'username' => self::username($value),
                    default => true
                };

                if (!$valid) {
                    $fieldErrors[] = "Field '{$field}' failed validation rule '{$ruleName}'";
                }
            }

            if (!empty($fieldErrors)) {
                $errors[$field] = $fieldErrors;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
