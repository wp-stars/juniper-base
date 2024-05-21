<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by storeabill on 31-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Vendidero\StoreaBill\Vendor\Mpdf\Http;

use Vendidero\StoreaBill\Vendor\Psr\Http\Message\RequestInterface;

interface ClientInterface
{

	public function sendRequest(RequestInterface $request);

}
