<?php
declare(strict_types=1);

namespace SaQle\Core\Assert;

use SaQle\Core\Assert\Exceptions\AssertException;
use ArrayAccess;
use BadMethodCallException;
use Closure;
use Countable;
use DateTime;
use DateTimeImmutable;
use Exception;
use ResourceBundle;
use SimpleXMLElement;
use Throwable;
use Traversable;

class Assert{
	public static function is_instance_of($value, $class, $message = ''){

		if (!($value instanceof $class)){
             static::throw_exception(\sprintf(
                $message ?: 'Expected an instance of %2$s. Got: %s',
                static::type_to_string($value),
                $class
             ));
        }

	}

	public static function is_iterable($value, $message = ''){
        if (!\is_array($value) && !($value instanceof Traversable)){
            static::throw_exception(\sprintf(
                $message ?: 'Expected an iterable. Got: %s',
                static::type_to_string($value)
            ));
        }
    }

	protected static function type_to_string($value){
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }

    protected static function value_to_string($value){
        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (\is_array($value)) {
            return 'array';
        }

        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return \get_class($value).': '.self::value_to_string($value->__toString());
            }

            if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
                return \get_class($value).': '.self::value_to_string($value->format('c'));
            }

            return \get_class($value);
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if (\is_string($value)) {
            return '"'.$value.'"';
        }

        return (string) $value;
    }

	public static function __callStatic($name, $arguments){
        if ('null_or_' === substr($name, 0, 8)){
            if (null !== $arguments[0]){
                $method = lcfirst(substr($name, 8));
                call_user_func_array(array(static::class, $method), $arguments);
            }

            return;
        }

        if ('all_' === substr($name, 0, 4)){
            static::is_iterable($arguments[0]);

            $method = lcfirst(substr($name, 4));
            $args = $arguments;

            foreach ($arguments[0] as $entry){
                $args[0] = $entry;

                call_user_func_array(array(static::class, $method), $args);
            }

            return;
        }

        throw new BadMethodCallException('No such method: '.$name);
    }

	protected static function throw_exception($message){
        throw new AssertException($message);
    }

    private function __construct(){
    }
}

?>