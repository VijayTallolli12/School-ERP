<?php

namespace App\Modules\Students\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Raised when an authenticated user cannot be safely resolved to a Student.
 *
 * Carries a client-safe message and an explicit HTTP status so controllers
 * and middleware can return a meaningful JSON error instead of a bare 500.
 */
class StudentLinkageException extends HttpException
{
    public static function notLinked(): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            'No student is linked to this account.'
        );
    }

    public static function inactive(string $studentName = ''): self
    {
        $name = $studentName !== '' ? " ({$studentName})" : '';

        return new self(
            Response::HTTP_FORBIDDEN,
            "This student account is not active{$name}."
        );
    }

    public static function archived(): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            'The student record linked to this account has been archived.'
        );
    }

    public static function ambiguous(): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            'The student record for this account is ambiguous. Please contact the administrator.'
        );
    }

    public static function crossSchool(): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            'This student account does not belong to the active school context.'
        );
    }
}
