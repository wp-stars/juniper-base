<?php

namespace frontend\ProductCard;

class MockedProductCardRenderer extends CardRenderer {

	public static function generate( $reference, $encoding ): MockedProductCardRenderer {
		return new MockedProductCardRenderer(
			'Some Title',
			false,
			'#',
			'',
			'SOME|TERMS|HERE',
			'0.00',
			false,
			'#',
			['animate-pulse', 'blur']
		);
	}
}