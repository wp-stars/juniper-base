<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by storeabill on 31-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Vendidero\StoreaBill\Vendor\Mpdf\File;

class LocalContentLoader implements \Vendidero\StoreaBill\Vendor\Mpdf\File\LocalContentLoaderInterface
{

	public function load($path)
	{
		return file_get_contents($path);
	}

}
