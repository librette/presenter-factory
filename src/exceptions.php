<?php
namespace Librette\Application\PresenterFactory;

interface Exception
{

}


class UnexpectedValueException extends \UnexpectedValueException implements Exception
{

}


class InvalidStateException extends \RuntimeException implements Exception
{

}