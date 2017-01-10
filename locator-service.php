<?php
if ( isset( $_SERVER['HTTP_ORIGIN'] ) )
	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);

header('Content-type: text/plain');

/**
 * Created by PhpStorm.
 * User: john.macon
 * Date: 2/28/16
 * Time: 2:59 PM
 */
$products = (isset($_REQUEST['products']) && '' != $_REQUEST['products']) ? $_REQUEST['products'] : '';
$location = (isset($_REQUEST['location']) && '' != $_REQUEST['location']) ? $_REQUEST['location'] : '';
$postalCode = (isset($_REQUEST['zip']) && '' != $_REQUEST['zip']) ? $_REQUEST['zip'] : '';
$productID = (isset($_REQUEST['upc']) && '' != $_REQUEST['upc']) ? $_REQUEST['upc'] : '';
$radius = (isset($_REQUEST['radius']) && '' != $_REQUEST['radius']) ? $_REQUEST['radius'] : '100';
$brand_id = (isset($_REQUEST['brandid']) && '' != $_REQUEST['brandid']) ? $_REQUEST['brandid'] : 'FRUS';

if ( '' != $products ) {

	//grab list of groups
	$xmlString = file_get_contents("http://productlocator.infores.com/productlocator/products/products.pli?client_id=148&brand_id=FRUS&prod_lvl=group");

	$xml = new SimpleXMLElement($xmlString);

	// add groups
	$groupArray = array();
	foreach ($xml as $group) {
		$groupArray[strval($group->group_id)] = '{"GROUP_' . $group->group_id . '": "' . $group->group_name . '"}';
	}

	$prodArray = array();
	foreach ($groupArray as $group_id => $group) {
		$prodArray[$group_id] = $group;


		// if ($group_id == 'any_frusion')
		// 	continue; // don't need to add all the products for this group

		// grab list of products and return it to the screen as json array
		$xmlString = file_get_contents("http://productlocator.infores.com/productlocator/products/products.pli?client_id=148&brand_id=FRUS&group_id=$group_id");

		$xml = new SimpleXMLElement($xmlString);

		// add products
		foreach ($xml as $products) {
			$prodArray[$group_id . strval($products->upc_code)] = '{"' . $products->upc_code . '": "' . $products->upc_name . '"}';
		}
	}

    echo '[' . implode(',', $prodArray) . ']';
    exit();
}

if ( '' != $location ) {
    // echo 'http://productlocator.infores.com/productlocator/servlet/ProductLocatorEngine?clientid=148&productfamilyid=FRUS&searchradius=' . $radius . '&producttype=upc&productid=' . $productID . '&zip=' . $postalCode;

	$is_group = false;
	if (strpos($productID, 'GROUP_') !== false) {
		$is_group = true;
		$productID = str_replace('GROUP_', '', $productID);
	}

	$svc_url = sprintf("http://productlocator.infores.com/productlocator/servlet/ProductLocatorEngine?clientid=148&productfamilyid=FRUS&searchradius=%s&producttype=%s&productid=%s&zip=%s",
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
