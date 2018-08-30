<?php

$fb = new \Facebook\Facebook([
    'app_id' => '318607418887545',
    'app_secret' => 'c40b19bb41871f72626bce151701a70c',
    'default_graph_version' => 'v3.1',
    //'default_access_token' => '{access-token}', // optional
]);

// Use one of the helper classes to get a Facebook\Authentication\AccessToken entity.
//   $helper = $fb->getRedirectLoginHelper();
//   $helper = $fb->getJavaScriptHelper();
//   $helper = $fb->getCanvasHelper();
//   $helper = $fb->getPageTabHelper();

try {
    // Get the \Facebook\GraphNodes\GraphUser object for the current user.
    // If you provided a 'default_access_token', the '{access-token}' is optional.
    $response = $fb->get('/me', 'EAAEhxZAIGjXkBAIrFUJ20aWPCFgp8ZBVuabxzsqi64o2mZAoZB2iyrib9qeVhJ4KqW3OHbOu7ziwCaVD4HhjCTZAuxHI4ypgd4KPHNY2IVgmcKG3Kyih2cnC6GiNPM7VZAGR7dFwU0cMsD2olihZCDDoBIq0us9LkbhCQEb2gp5KjwQ95uW5ITO8sZCMzjTdrVVZBpwKmlJUVowZDZD');
} catch (\Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (\Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

$me = $response->getGraphUser();
echo 'Logged in as ' . $me->getId();
$response = $fb->get('/me/inbox', 'EAAEhxZAIGjXkBAIrFUJ20aWPCFgp8ZBVuabxzsqi64o2mZAoZB2iyrib9qeVhJ4KqW3OHbOu7ziwCaVD4HhjCTZAuxHI4ypgd4KPHNY2IVgmcKG3Kyih2cnC6GiNPM7VZAGR7dFwU0cMsD2olihZCDDoBIq0us9LkbhCQEb2gp5KjwQ95uW5ITO8sZCMzjTdrVVZBpwKmlJUVowZDZD');
echo $response;
