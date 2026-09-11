<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a customer or guest tries to open another chat session while already at
 * ChatService::MAX_OPEN_SESSIONS open (waiting/active) sessions. Kept as its own class,
 * rather than a bare \Exception, so callers can catch this specific condition without
 * accidentally swallowing unrelated failures (e.g. a broadcasting error) as if they were a
 * "too many open conversations" response.
 */
class ChatSessionLimitExceededException extends Exception {}
