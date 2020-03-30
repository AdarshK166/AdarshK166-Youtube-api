<?php
if ($_GET['q'] && $_GET['maxResults']) {
  // Call set_include_path() as needed to point to your client library.
  require_once ($_SERVER["DOCUMENT_ROOT"].'/youtube/google-api-php-client/src/Google_Client.php');
  require_once ($_SERVER["DOCUMENT_ROOT"].'/youtube/google-api-php-client/src/contrib/Google_YouTubeService.php');

  /* Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
  Google APIs Console <http://code.google.com/apis/console#access>
  Please ensure that you have enabled the YouTube Data API for your project. */
  $DEVELOPER_KEY = 'AIzaSyDerC4M3Vj7Wl88iO28F6B2FfaECium2Iw';

  $client = new Google_Client();
  $client->setDeveloperKey($DEVELOPER_KEY);

  $youtube = new Google_YoutubeService($client);

  $yt_search = $_GET['q'];
  $yt_source = file_get_contents('https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=1&order=relevance&q='.urlencode($yt_search).'&key=AIzaSyDerC4M3Vj7Wl88iO28F6B2FfaECium2Iw');
  $yt_decode = json_decode($yt_source, true);
  if ($yt_decode['pageInfo']['totalResults']>0) {
    if (strlen($yt_decode['items'][0]['id']['videoId'])>5) {
        $yt_videoid = trim($yt_decode['items'][0]['id']['videoId']);
    }
}

  $mainFrame .='<iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$yt_videoid.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
	
  
  try {
    $searchResponse = $youtube->search->listSearch('id,snippet', array(
      'q' => $_GET['q'],
      'maxResults' => $_GET['maxResults'],
    ));
	

    $videos = '';
    $channels = '';
    foreach ($searchResponse['items'] as $searchResult) {
      switch ($searchResult['id']['kind']) {
        case 'youtube#video':
          $videos .= sprintf('<li>%s (%s)</li>', $searchResult['snippet']['title'],
            $searchResult['id']['videoId']."<a href=http://www.youtube.com/watch?v=".$searchResult['id']['videoId']." target=_blank>   Watch This Video</a>");
			$thumbnail .= sprintf('<img src="http://img.youtube.com/vi/'.$searchResult['id']['videoId'].'/0.jpg"  width="250">');
      
      
      $vHold .= sprintf('<p class="divcont"><img class="imagecont" src="http://img.youtube.com/vi/'.$searchResult['id']['videoId'].'/0.jpg" width="250">'.$searchResult['snippet']['title'].'</p>');
          break;
        case 'youtube#channel':
          $channels .= sprintf('<li>%s (%s)</li>', $searchResult['snippet']['title'],
            $searchResult['id']['channelId']);
          break;
       }
    }
	

   } catch (Google_ServiceException $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  }
}
?>

<!doctype html>
<html>
  <head>
    <title>YouTube Search</title>
<link href="//www.w3resource.com/includes/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">

  </head>
  <body>
    <form method="GET">
  <div>
    Search Term: <input type="search" id="q" name="q" placeholder="Enter Search Term">
  </div>
  <div>
    Max Results: <input type="number" id="maxResults" name="maxResults" min="1" max="50" step="1" value="25">
  </div>
  <input type="submit" value="Search">
</form>
<h3>Videos</h3>
    <div>
    <?php echo $mainFrame; ?>
    </div>
    <div class="displayv">
		<?php echo $vHold; ?>
	</div>
	
</body>
</html>
