<?php
/**
 * Yelp API v2.0 code sample.
 *
 * This program demonstrates the capability of the Yelp API version 2.0
 * by using the Search API to query for businesses by a search term and location,
 * and the Business API to query additional information about the top result
 * from the search query.
 * 
 * Please refer to http://www.yelp.com/developers/documentation for the API documentation.
 * 
 * This program requires a PHP OAuth2 library, which is included in this branch and can be
 * found here:
 *      http://oauth.googlecode.com/svn/code/php/
 * 
 * Sample usage of the program:
 * `php sample.php --term="bars" --location="San Francisco, CA"`
 */
// Enter the path that the oauth library is in relation to the php file
require_once('lib/OAuth.php');
// Set your OAuth credentials here  
// These credentials can be obtained from the 'Manage API Access' page in the
// developers documentation (http://www.yelp.com/developers)
$CONSUMER_KEY = 'AjDUcnCvqTK0qcgHd18Rlw';
$CONSUMER_SECRET = 'J1GT4XQpPb-7tJEhbCsudFoz26I';
$TOKEN = 'eoh7ClXK39yCqpmfAVptjIm1uKC4UeeI';
$TOKEN_SECRET = 'gSPjdVeOY46nzzpeRsP_Rzijkv4';
$API_HOST = 'api.yelp.com';
$DEFAULT_TERM = 'dinner';
$DEFAULT_LOCATION = 'San Francisco, CA';
$SEARCH_LIMIT = 3;
$SEARCH_PATH = '/v2/search/';
$BUSINESS_PATH = '/v2/business/';
$DEFAULT_RADIUS = 1609;
/** 
 * Makes a request to the Yelp API and returns the response
 * 
 * @param    $host    The domain host of the API 
 * @param    $path    The path of the APi after the domain
 * @return   The JSON response from the request      
 */
/*?>
<html>
<body>
<form action="test.php" method="post">
Name: <input type="text" name="name"><br>
E-mail: <input type="text" name="email"><br>
<input type="submit">
</form>
</body>
</html>
<?*/
function request($host, $path) {
    $unsigned_url = "http://" . $host . $path;
    // Token object built using the OAuth library
    $token = new OAuthToken($GLOBALS['TOKEN'], $GLOBALS['TOKEN_SECRET']);
    // Consumer object built using the OAuth library
    $consumer = new OAuthConsumer($GLOBALS['CONSUMER_KEY'], $GLOBALS['CONSUMER_SECRET']);
    // Yelp uses HMAC SHA1 encoding
    $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
    $oauthrequest = OAuthRequest::from_consumer_and_token(
        $consumer, 
        $token, 
        'GET', 
        $unsigned_url
    );
    
    // Sign the request
    $oauthrequest->sign_request($signature_method, $consumer, $token);
    
    // Get the signed URL
    $signed_url = $oauthrequest->to_url();
    
    // Send Yelp API Call
    $ch = curl_init($signed_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    
    return $data;
}
/**
 * Query the Search API by a search term and location 
 * 
 * @param    $term        The search term passed to the API 
 * @param    $location    The search location passed to the API 
 * @param    $limit
 * @param    $radius
 * @return   The JSON response from the request 
 */
function search($term, $limit, $radius, $latitude, $longitude) {
    $url_params = array();
    
    $url_params['term'] = $_POST["term"] ?: $GLOBALS['DEFAULT_TERM'];
    //$url_params['location'] = $_POST["location"]?: $GLOBALS['DEFAULT_LOCATION'];
    $url_params['limit'] = $_POST["limit"] ?: $GLOBALS['SEARCH_LIMIT'];
    $url_params['radius_filter'] = $_POST["radius"] ?: $GLOBALS['DEFAULT_RADIUS'];
    $url_params['latitude'] = $_POST["lat"];
    $url_params['longitude'] = $_POST["long"];
    $search_path = $GLOBALS['SEARCH_PATH'] . "?" . http_build_query($url_params);
    
    return request($GLOBALS['API_HOST'], $search_path);
}
/**
 * Query the Business API by business_id
 * 
 * @param    $business_id    The ID of the business to query
 * @return   The JSON response from the request 
 */
function get_business($business_id) {
    $business_path = $GLOBALS['BUSINESS_PATH'] . $business_id;
    
    return request($GLOBALS['API_HOST'], $business_path);
}
/**
 * Queries the API by the input values from the user 
 * 
 * @param    $term        The search term to query
 * @param    $location    The location of the business to query
 * @param    $limit
 * @param    $radius
 */
function query_api($term, $limit, $radius, $latitude, $longitude) {
    $response = json_decode(search($term, $limit, $radius, $latitude, $longitude));
    $business_id = $response->businesses[0]->id;
    $info = "";
    foreach($response->businesses as $business){
        // print sprintf(
        //     "%d businesses found, querying business info for the top result \"%s\"\n\n",         
        //     count($response->businesses),
        //     $business_id
        // );

        $response = get_business($business->id);
        $result = json_decode($response, true);
    
        // print sprintf("Result for business \"%s\" found:", $business->id);
        // echo "<br/>";
        
        // $info .= '<a href="' . $result['url'] . '">' . $result['name'] . '</a>' . "<br/>";
        // $info .= '<img src="' . $result['rating_img_url'] . '"/>' . " " . $result['review_count'] . " reviews" . "<br/>";
        // $info .= $result['display_phone'] . "<br/>";
        // foreach ($result['categories'] as $category)
        //         $info .= $category[0] . " - ";
        // $info .= "<br/>";
        // foreach ($result['location']['display_address'] as $address)
        //     $info .= $address . "<br/>";
        
        // $info .= "<br/>";
        
        //$result["json"] = json_encode($result);
        //echo json_encode($result); THIS ONE FOR GOOGLE MAPS
        print $result;
    }
    //echo $info;
}
/**
 * User input is handled here 
 */
$longopts  = array(
    "term::",
    "location::",
    "limit::",
    "radius::",
    "latitude::",
    "longitude::"
);

//$term = $_POST['term'];    
$options = getopt("", $longopts);
$term = $options['term'] ?: '';
$location = $options['location'] ?: '';
$limit = $options['limit'] ?: '';
$radius = $options['radius'] ?: '';
$latitude = $options['latitude'] ?: '';
$longitude = $options['longitude'] ?: '';
query_api($term, $limit, $radius, $latitude, $longitude);
?>