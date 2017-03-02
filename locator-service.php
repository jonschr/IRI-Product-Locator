<?php

// if ( isset( $_SERVER['HTTP_ORIGIN'] ) )
// 	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);

header('Content-type: text/plain');

//* These settings are always the result of queries and are not set directly by any shortcode attributes
$products = (isset($_REQUEST['products']) && '' != $_REQUEST['products']) ? $_REQUEST['products'] : '';
$location = (isset($_REQUEST['location']) && '' != $_REQUEST['location']) ? $_REQUEST['location'] : '';
$postalCode = (isset($_REQUEST['zip']) && '' != $_REQUEST['zip']) ? $_REQUEST['zip'] : '';
$productID = (isset($_REQUEST['upc']) && '' != $_REQUEST['upc']) ? $_REQUEST['upc'] : '';

//* These settings are set on an options page on the backend of the site
$brand_id = $_REQUEST['brandid'];
$radius = $_REQUEST['radius'];
// $location = $_REQUEST['location'];
$client_id = $_REQUEST['clientid'];

//* If there are products...
if ( '' != $products ) {

    ////////////////////////////////
    // GETTING THE PRODUCT GROUPS //
    ////////////////////////////////
    
	//* Grab the list of groups
	$xmlString = file_get_contents("http://productlocator.infores.com/productlocator/products/products.pli?client_id=$client_id&brand_id=$brand_id&prod_lvl=group");

	$xml = new SimpleXMLElement($xmlString);

	// add groups
	$groupArray = array();

	//* Check the total number of groups (if there's 1, it should be named "All Products")
	$number_of_groups = count( $xml );

	foreach ($xml as $group) {


		//* Remove the word "Group" from the names of things
		$groupname = str_replace( 'Group ', '', $group->group_name );

		if ( $number_of_groups == 1 )
			$groupname = 'All Products';

		// echo 'GROUP NUMBER ' . $current_group_number . ': ' . $groupname  . '  //  ';

        //* If we have more than one group, we'll treat this normally
        // if ( $current_group_number != 1 )
            // $groupArray[strval($group->group_id)] = '{"GROUP_ID": "GROUPNAME"}';

            $groupArray[strval($group->group_id)] = '{"GROUP_' . $group->group_id . '": "' . $groupname . '"}';

        //* The first group will always be an "All Products" group, so let's make it say that
        // if ( $current_group_number == 1 )
            // $groupArray[strval($group->group_id)] = '{"GROUP_' . $group->group_id . '": "All Products"}';

	}

    /////////////////////////////////
    // GETTING INDIVIDUAL PRODUCTS //
    /////////////////////////////////

	$prodArray = array();
	foreach ($groupArray as $group_id => $group) {
		
		$prodArray[$group_id] = $group;

		// echo 'GROUP ID: ' . $group_id . '   //   ';

		// if ($group_id == 'any_frusion')
        // continue; // don't need to add all the products for this group

        //* Logic to not include the "any" group if there are a bunch of groups (it results in duplicate products)
		// if ( ( $number_of_groups == $count ) && ( $number_of_groups != 1 ) )

		// grab list of products and return it to the screen as json array
		$xmlString = file_get_contents("http://productlocator.infores.com/productlocator/products/products.pli?client_id=$client_id&brand_id=$brand_id&group_id=$group_id");

		$xml = new SimpleXMLElement($xmlString);


		// add products
		foreach ($xml as $products) {

			// BREAK if there's a message indicating an error
			// if ( $products->message )
			// 	break;

			// echo 'PRODUCT:';
			// print_r( $products );

            // if ( ( $number_of_groups == $count ) && ( $number_of_groups == 1 ) )
            //     $products->upc_code = 'All Products';
            //  
            
            
			$subitem_prefix = '';

            if ( $number_of_groups != 1 )
	            $subitem_prefix = '&nbsp;&nbsp;&nbsp;&nbsp;';

			if ( $products->upc_code )
				$prodArray[$group_id . strval($products->upc_code)] = '{"' . $products->upc_code . '": "' . $subitem_prefix . $products->upc_name . '"}';
		}
			

	}

    echo '[' . implode(',', $prodArray) . ']';

    // echo 'ENDING THE PRODUCT LOOP';

    exit();
}


if ( '' != $location ) {

	$is_group = false;
	if (strpos($productID, 'GROUP_') !== false) {
		$is_group = true;
		$productID = str_replace('GROUP_', '', $productID);
	}

	$productID = rawurlencode( $productID );

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
