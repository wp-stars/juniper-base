<?php
/**
 * Class WCPay_Multi_Currency_WooCommercePointsAndRewards_Tests
 *
 * @package WooCommerce\Payments\Tests
 */

use WCPay\MultiCurrency\Compatibility\WooCommercePointsAndRewards;
use WCPay\MultiCurrency\MultiCurrency;
use WCPay\MultiCurrency\Utils;
use WCPay\MultiCurrency\Currency;

/**
 * WCPay\MultiCurrency\Compatibility\WooCommercePointsAndRewards unit tests.
 */
class WCPay_Multi_Currency_WooCommercePointsAndRewards_Tests extends WCPAY_UnitTestCase {

	/**
	 * Mock WCPay\MultiCurrency\MultiCurrency.
	 *
	 * @var WCPay\MultiCurrency\MultiCurrency|PHPUnit_Framework_MockObject_MockObject
	 */
	private $mock_multi_currency;

	/**
	 * Mock WCPay\MultiCurrency\Utils.
	 *
	 * @var WCPay\MultiCurrency\Utils|PHPUnit_Framework_MockObject_MockObject
	 */
	private $mock_utils;

	/**
	 * WCPay\MultiCurrency\Compatibility\WooCommercePointsAndRewards instance.
	 *
	 * @var WCPay\MultiCurrency\Compatibility\WooCommercePointsAndRewards
	 */
	private $wc_points_rewards;

	/**
	 * Pre-test setup
	 */
	public function set_up() {
		parent::set_up();

		$this->mock_multi_currency = $this->createMock( MultiCurrency::class );
		$this->mock_utils          = $this->createMock( Utils::class );

		$this->wc_points_rewards = new WooCommercePointsAndRewards( $this->mock_multi_currency, $this->mock_utils );
	}

	/**
	 * @dataProvider filters_provider
	 */
	public function test_registers_woocommerce_filters_properly( $filter, $function_name ) {
		$priority = has_filter( $filter, [ $this->wc_points_rewards, $function_name ] );
		$this->assertGreaterThan(
			10,
			$priority,
			"Filter '$filter' was not registered with '$function_name' with a priority higher than the default."
		);
	}

	public function filters_provider() {
		return [
			[ 'option_wc_points_rewards_earn_points_ratio', 'convert_points_ratio' ],
			[ 'option_wc_points_rewards_redeem_points_ratio', 'convert_points_ratio' ],
		];
	}

	public function test_get_selected_currency_called_only_once() {
		$this->mock_multi_currency
			->expects( $this->once() )
			->method( 'get_selected_currency' );

		$this->wc_points_rewards->convert_points_ratio();
		$this->wc_points_rewards->convert_points_ratio();
	}


	public function test_convert_points_ratio_skip_on_select_and_default_currency_match() {
		$ratio = '';

		$this->mock_multi_currency
			->expects( $this->once() )
			->method( 'get_selected_currency' );

		$this->mock_utils
			->expects( $this->never() )
			->method( 'is_call_in_backtrace' );

		$this->assertEquals( $ratio, $this->wc_points_rewards->convert_points_ratio( $ratio ) );
	}

	public function test_convert_points_ratio_skip_on_discount_backtrace() {
		$ratio = '';

		$this->mock_multi_currency
			->expects( $this->once() )
			->method( 'get_selected_currency' )
			->willReturn( new Currency( 'EUR' ) );

		$this->mock_utils
			->expects( $this->once() )
			->method( 'is_call_in_backtrace' )
			->with( [ 'WC_Points_Rewards_Discount->get_discount_data' ] )
			->willReturn( true );

		$this->assertEquals( $ratio, $this->wc_points_rewards->convert_points_ratio( $ratio ) );
	}

	/**
	 * @dataProvider points_ratio_provider
	 */
	public function test_convert_points_ratio( $rate, $ratio, $converted_ratio ) {
		$this->mock_multi_currency
			->expects( $this->once() )
			->method( 'get_selected_currency' )
			->willReturn( new Currency( 'EUR', $rate ) );

		$this->mock_utils
			->expects( $this->once() )
			->method( 'is_call_in_backtrace' )
			->willReturn( false );

		$this->assertEquals( $converted_ratio, $this->wc_points_rewards->convert_points_ratio( $ratio ) );
	}

	public function points_ratio_provider() {
		return [
			[ 0, '', '0:0' ],
			[ 0, '1', '1:0' ],
			[ 0.5, '1:1', '1:0.5' ],
			[ 2, '1:1.23', '1:2.46' ],
			[ 20, '1:10', '1:200' ],
		];
	}

}
