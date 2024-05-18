<?php declare(strict_types = 1);

namespace PHPStan\Type\Nette;

use Composer\InstalledVersions;
use OutOfBoundsException;
use PHPStan\Testing\TypeInferenceTestCase;
use function class_exists;
use function version_compare;

class ComponentModelContainerDynamicReturnTypeExtensionTest extends TypeInferenceTestCase
{

	/**
	 * @return iterable<string, mixed[]>
	 */
	public function dataFileAsserts(): iterable
	{
		$componentModelVersion = self::getInstalledVersion('nette/component-model');

		$suffix = $componentModelVersion !== null && version_compare($componentModelVersion, '3.1.0', '>=') ? '31' : '';

		yield from self::gatherAssertTypes(__DIR__ . '/data/componentModelContainer' . $suffix . '.php');
	}

	/**
	 * @dataProvider dataFileAsserts
	 * @param mixed ...$args
	 */
	public function testFileAsserts(
		string $assertType,
		string $file,
		...$args
	): void
	{
		$this->assertFileAsserts($assertType, $file, ...$args);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/phpstan.neon',
		];
	}

	private static function getInstalledVersion(string $package): ?string
	{
		if (!class_exists(InstalledVersions::class)) {
			return null;
		}

		try {
			$installedVersion = InstalledVersions::getVersion($package);
		} catch (OutOfBoundsException $e) {
			return null;
		}

		return $installedVersion;
	}

}
