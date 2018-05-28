<?php

namespace Tests\Model;

$container = require __DIR__ . '/../../bootstrap.php';

use App\Model\YearsOverlap;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

final class YearsOverlapTest extends TestCase
{

	/** @var YearsOverlap */
	private $yearsOverlap;

	public function __construct(Container $container)
	{
		$this->yearsOverlap = $container->getByType(YearsOverlap::class);
	}

	public function getLoopArgs(): array
	{
		return [
			[null, null, null, null, true],
			[null, 2005, null, 2010, true],
			[2005, null, 2010, null, true],
			[2005, 2007, 2008, 2010, false],
			[2005, 2007, 2007, 2008, true],
			[null, 2010, 2008, 2011, true],
			[null, 2008, 2010, 2011, false],
			[2010, null, 2008, 2011, true],
			[2010, null, 2008, 2009, false],
			[null, 2010, 2009, null, true],
			[null, 2010, 2011, null, false],
			[2010, null, 2008, 2011, true],
			[2007, null, 2008, 2011, true],
			[2012, null, 2008, 2011, false],
		];
	}

	/**
	 * @dataProvider getLoopArgs
	 */
	public function testOverlap(?int $from1, ?int $to1, ?int $from2, ?int $to2, bool $expected): void
	{
		Assert::equal($expected, $this->yearsOverlap->isOverlapped($from1, $to1, $from2, $to2));
		Assert::equal($expected, $this->yearsOverlap->isOverlapped($from2, $to2, $from1, $to1));
	}

}

if (getenv(\Tester\Environment::RUNNER)) {
	(new YearsOverlapTest($container))->run();
}