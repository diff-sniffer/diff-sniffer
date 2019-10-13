<?php declare(strict_types=1);

namespace DiffSniffer\Command\Exception;

use BadMethodCallException;
use DiffSniffer\Exception;

/**
 * Exception indicating bad command usage
 */
class BadUsage extends BadMethodCallException implements Exception
{
}
