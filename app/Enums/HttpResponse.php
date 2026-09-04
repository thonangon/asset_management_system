<?php

namespace App\Enums;

enum HttpResponse: string
{
    case SUCCESS = '200';
    case NOT_FOUND = '404';
    case UNAUTHORIZED = '401';
    case FORBIDDEN = '403';
    case VALIDATION_ERROR = '422';
    case INTERNAL_SERVER_ERROR = '500';
    case CREATED = '201';
    case UPDATED = '202';
    case DELETED = '203';
    case BAD_REQUEST = '400';
    case CONFLICT = '409';
    case SERVICE_UNAVAILABLE = '503';
    public function getMessage(): string
    {
        return match ($this) {
            self::SUCCESS => 'Operation completed successfully.',
            self::NOT_FOUND => 'The requested resource was not found.',
            self::UNAUTHORIZED => 'You are not authorized to perform this action.',
            self::FORBIDDEN => 'Access to this resource is forbidden.',
            self::VALIDATION_ERROR => 'There were validation errors with your request.',
            self::INTERNAL_SERVER_ERROR => 'An internal server error occurred.',
            self::CREATED => 'Resource created successfully.',
            self::UPDATED => 'Resource updated successfully.',
            self::DELETED => 'Resource deleted successfully.',
            self::BAD_REQUEST => 'The request was invalid or cannot be served.',
            self::CONFLICT => 'There was a conflict with the current state of the resource.',
            self::SERVICE_UNAVAILABLE => 'The service is currently unavailable. Please try again later.'
        };
    }
}
