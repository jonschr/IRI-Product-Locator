<?php

// if ( isset( $_SERVER['HTTP_ORIGIN'] ) )
// 	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);

header('Content-type: text/plain');

//* These settings are always the result of queries and are not set directly by any shortcode attributes
$products = (isset($_REQUEST['products']) && '' != $_REQUEST['products']) ? $_REQUEST['products'] : '';
$location = (isset($_REQUEST['location']) && '' != $_REQUEST['location']) ? $_REQUEST['location'] : '';
$postalCode = (isset($_REQUEST['zip']) && '' != $_REQUEST['zip']) ? $_REQUEST['zip'] : '';
$productID = (isset($_REQUEST['upc']) && '' != $_REQUEST['upc']) ? $_REQUEST['upc'] : '';

//* These settings can be controlled through the shortcode
$radius = (isset($_REQUEST['radius']) && '' != $_REQUEST['radius']) ? $_REQUEST['radius'] : '';
$brand_id = (isset($_REQUEST['brandid']) && '' != $_REQUEST['brandid']) ? $_REQUEST['brandid'] : 'FRUS';
$client_id = (isset($_REQUEST['clientid']) && '' != $_REQUEST['clientid']) ? $_REQUEST['clientid'] : '148';

if ( '' != $products ) {

	//* Grab the list of groups
	$xmlString = file_get_contents("http://productlocator.infores.com/productlocator/products/products.pli?client_id=$client_id&brand_id=$brand_id&prod_lvl=group");

	$xml = new SimpleXMLElement($xmlString);

    $number_of_groups = count( $xml );
    $count_groups = 1;

	// add groups
	$groupArray = array();

	foreach ($xml as $group) {

        //* If we have more than one group, we'll treat this normally
        if ( $number_of_groups != 1 )
            $groupArray[strval($group->group_id)] = '{"GROUP_' . $group->group_id . '": "' . $group->group_name . '"}';

        //* If there's just one, then it's an "all products" group, so let's make it say that
        if ( ( $number_of_groups == $count_groups ) && ( $number_of_groups == 1 ) )
            $groupArray[strval($group->group_id)] = '{"GROUP_' . $group->group_id . '": "All Products"}';

        $count_groups++;
	}

    //* If there's just one group, we'll use it, if more than one, then we skip the first group (the 'all products' group)

	$prodArray = array();
	foreach ($groupArray as $group_id => $group) {
		$prodArray[$group_id] = $group;

		// if ($group_id == 'any_frusion')
        // continue; // don't need to add all the products for this group

        //* Logic to not include the "any" group if there are a bunch of groups (it results in duplicate products)
		// if ( ( $number_of_groups == $count ) && ( $number_of_groups != 1 ) )

		// grab list of products and return it to the screen as json array
		$xmlString = file_get_contents("http://productlocator.infores.com/productlocator/products/products.pli?client_id=$client_id&brand_id=$brand_id&group_id=$group_id");


		$xml = new SimpleXMLElement($xmlString);

		// add products
		foreach ($xml as $products) {

            // if ( ( $number_of_groups == $count ) && ( $number_of_groups == 1 ) )
            //     $products->upc_code = 'All Products';

			$prodArray[$group_id . strval($products->upc_code)] = '{"' . $products->upc_code . '": "' . $products->upc_name . '"}';
		}
	}

    echo '[' . implode(',', $prodArray) . ']';
    exit();
}

if ( '' != $location ) {
    // echo 'http://productlocator.infores.com/productlocator/servlet/ProductLocatorEngine?clientid=$client_id&productfamilyid=$brand_id&searchradius=' . $radius . '&producttype=upc&productid=' . $productID . '&zip=' . $postalCode;

	$is_group = false;
	if (strpos($productID, 'GROUP_') !== false) {
		$is_group = true;
		$productID = str_replace('GROUP_', '', $productID);
	}

	$svc_url = sprintf("http://productlocator.infores.com/productlocator/servlet/ProductLocatorEngine?clientid=$client_id&productfamilyid=$brand_id&searchradius=%s&producttype=%s&productid=%s&zip=%s",
		$radius, $is_group ? 'agg' : 'upc', $productID, $postalCode);
    $productResultslist = file_get_contents($svc_url);

    $xml = simplexml_load_string($productResultslist, null, LIBXML_NOCDATA);
    $count = count($xml->STORES->STORE);
    if($count > 1) {
        $json = json_encode($xml);
        echo $json;
    } else {
        echo json_encode("No Stores");
    }

    exit();
}
