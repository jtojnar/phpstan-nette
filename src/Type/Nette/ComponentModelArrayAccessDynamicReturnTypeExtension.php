<?php declare(strict_types = 1);

namespace PHPStan\Type\Nette;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function count;
use function sprintf;
use function ucfirst;

class ComponentModelArrayAccessDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return 'Nette\Application\UI\Component';
	}

	public function isMethodSupported(
		MethodReflection $methodReflection
	): bool
	{
		return $methodReflection->getName() === 'offsetGet';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$calledOnType = $scope->getType($methodCall->var);
		$mixedType = new MixedType();
		$args = $methodCall->getArgs();
		if (count($args) !== 1) {
			return $mixedType;
		}

		$argType = $scope->getType($args[0]->value);
		if (count($argType->getConstantStrings()) === 0) {
			return $mixedType;
		}

		$types = [];
		foreach ($argType->getConstantStrings() as $constantString) {
			$componentName = $constantString->getValue();

			$methodName = sprintf('createComponent%s', ucfirst($componentName));
			if (!$calledOnType->hasMethod($methodName)->yes()) {
				return $mixedType;
			}

			$method = $calledOnType->getMethod($methodName, $scope);

			$types[] = ParametersAcceptorSelector::selectSingle($method->getVariants())->getReturnType();
		}

		return TypeCombinator::union(...$types);
	}

}
