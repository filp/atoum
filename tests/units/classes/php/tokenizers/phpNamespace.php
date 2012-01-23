<?php

namespace mageekguy\atoum\tests\units\php\tokenizers;

require_once __DIR__ . '/../../../runner.php';

use
	mageekguy\atoum,
	mageekguy\atoum\php\tokenizer,
	mageekguy\atoum\php\tokenizers
;

class phpNamespace extends atoum\test
{
	public function testClass()
	{
		$this->assert->testedClass->isSubclassOf('mageekguy\atoum\php\tokenizer');
	}

	public function testCanTokenize()
	{
		$this->assert
			->if($tokenizer = new tokenizers\phpNamespace())
			->and($tokens = new tokenizer\tokens())
			->then
				->boolean($tokenizer->canTokenize($tokens))->isFalse()
			->if($tokens = new tokenizer\tokens('<?php namespace foo; ?>'))
			->then
				->boolean($tokenizer->canTokenize($tokens))->isFalse()
			->if($tokens->next())
			->then
				->boolean($tokenizer->canTokenize($tokens))->isTrue()
			->if($tokens->next())
			->then
				->boolean($tokenizer->canTokenize($tokens))->isFalse()
		;
	}

	public function testGetIteratorInstance()
	{
		$this->assert
			->if($tokenizer = new tokenizers\phpNamespace())
			->then
				->object($tokenizer->getIteratorInstance())->isEqualTo(new tokenizers\phpNamespace\iterator())
		;
	}
}

?>