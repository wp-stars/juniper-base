<?php

$juniper_blocks = new \Juniper\Blocks\JuniperBlocks();
$juniper_blocks->include_blocks_functions();
$juniper_cpt_jobs = new \Juniper\Cpt\Jobs();
$juniper_cpt_metalprices = new \Juniper\Cpt\MetalPrices();
$juniper_taxonomy_replace_rewrite_name = new \Juniper\Taxonomies\MetalsAndAccessories();
$juniper_taxonomy_replace_rewrite_name = new \Juniper\Taxonomies\Purchasability();
