<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Router;
use ReflectionClass;
use ReflectionMethod;

/**
 * Routed actions signature Test
 *
 * Router::dispatch() calls actions with no arguments ($controllerObject->$action()),
 * handing route captures to the controller as $this->params instead. An action
 * declaring a required parameter therefore fails with an ArgumentCountError the
 * moment its route is requested.
 *
 * @package Tests\Unit
 */
class RoutedActionsSignatureTest extends TestCase
{
    /**
     * @return array<int, array{0: string, 1: string}> [class, method] pairs
     */
    private function routedActions(): array
    {
        $router = new Router();
        $routesProperty = (new ReflectionClass($router))->getProperty('routes');
        $routesProperty->setAccessible(true);

        $actions = [];
        foreach ($routesProperty->getValue($router) as $params) {
            if (empty($params['controller']) || empty($params['action'])) {
                continue;
            }

            $method = $this->toCamelCase($params['action']) . 'Action';

            foreach (['App\\Controllers\\Admin\\', 'App\\Controllers\\Frontend\\'] as $namespace) {
                $class = $namespace . $params['controller'] . 'Controller';
                if (class_exists($class) && method_exists($class, $method)) {
                    $actions[$class . '::' . $method] = [$class, $method];
                    break;
                }
            }
        }

        return array_values($actions);
    }

    private function toCamelCase(string $value): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value))));
    }

    public function testEveryRoutedActionIsCallableWithoutArguments()
    {
        $actions = $this->routedActions();

        $this->assertNotEmpty($actions, 'No routed actions were resolved — the test would prove nothing');

        $offenders = [];
        foreach ($actions as [$class, $method]) {
            $required = (new ReflectionMethod($class, $method))->getNumberOfRequiredParameters();
            if ($required > 0) {
                $offenders[] = $class . '::' . $method . '() requires ' . $required . ' argument(s)';
            }
        }

        $this->assertSame([], $offenders, "Router dispatches actions with no arguments:\n" . implode("\n", $offenders));
    }
}
