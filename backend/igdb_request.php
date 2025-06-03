<?php

$clientId="fw91418ehnsllnxqwrne5fg6xlnsv5";
$clientSecret="aixaw40xgj13whe8ngkkpwk2juiqpl";

$connect = new mysqli("localhost", "root", "", "mygamelist");
if($connect->connect_error) {
  die("Connection failed: " . $connect->connect_error);
}

// IGDB access token
function getAccessToken($clientId, $clientSecret) {
  $url = "https://id.twitch.tv/oauth2/token";
  $data = [
    "client_id" => $clientId,
    "client_secret" => $clientSecret,
    "grant_type" => "client_credentials"
  ];
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($httpCode !== 200) {
    die("Failed to get access token. HTTP Status: $httpCode\nResponse: $response\n");
  }

  $json = json_decode($response, true);
  if(!isset($json["access_token"])) {
    die("Access token not fount in response: $response\n");
  }

  return $json["access_token"];
}

// Fetch games from IGDB
function fetchGames($accessToken, $clientId, $offset) {
  $url = "https://api.igdb.com/v4/games";
  $query = "
    fields name, summary, first_release_date, rating, cover.image_id, involved_companies.company.name, involved_companies.developer, involved_companies.publisher;
    sort first_release_date desc;
    limit 500;
    offset $offset;
    where cover != null & first_release_date != null & first_release_date <= 1767225600;
  ";

  $headers = [
    "Client-Id: $clientId",
    "Authorization: Bearer $accessToken",
    "Content-Type: text/plain"
  ];

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  if(!$response) {
    die ("cURL error: " . curl_error($ch));
  }
  curl_close($ch);

  echo "RAW IGDB response: \n$response\n";
  return json_decode($response, true);
}

// Extract developer/publisher names
function extractCompanies($involvedCompanies) {
  $developer = $publisher = null;
  if(is_array($involvedCompanies)) {
    foreach($involvedCompanies as $company) {
      if(($company["developer"] ?? false) && !$developer) {
        $developer = $company["company"]["name"] ?? null;
      }
      if(($company["publisher"] ?? false) && !$publisher) {
        $publisher = $company["company"]["name"] ?? null;
      }
    }
  }

  return [$developer, $publisher];
}

function generateGameHash($title, $releaseDate) {
  return hex2bin(md5($title . $releaseDate));
}

// Insert games 
function insertgames($connect, $games) {
  $stmt = $connect->prepare ("
    INSERT IGNORE INTO game
    (title, description, publisher, developer, cover_url, release_date, rating, unique_hash)
    VALUES(?, ?, ?, ?, ?, ?, ?, ?)
  ");

  foreach ($games as $game) {
    $title = $game["name"] ?? null;
    $description = $game["summary"] ?? null;
    list($developer, $publisher) = extractCompanies($game["involved_companies"] ?? []);
    $coverUrl = isset($game["cover"]["image_id"])
      ? "https://images.igdb.com/igdb/image/upload/t_cover_big/" . $game["cover"]["image_id"] . ".jpg"
      : null;
    $releaseDate = isset($game["first_release_date"])
      ? date("Y-m-d", $game["first_release_date"])
      : null;
    $rating = $game["rating"] ?? null;

    if(!$title || !$releaseDate) continue;

    $hash = generateGameHash($title, $releaseDate);
    $stmt->bind_param(
      "ssssssds",
      $title,
      $description,
      $publisher,
      $developer,
      $coverUrl,
      $releaseDate,
      $rating,
      $hash
    );
    $stmt->send_long_data(8, $hash);
    $stmt->execute();
  }

  $stmt->close();
}

$accessToken = getAccessToken($clientId, $clientSecret);

for($offset = 0; $offset < 30000; $offset += 500) {
  echo "Fetching games at offset $offset...\n";
  $games = fetchGames($accessToken, $clientId, $offset);
  if (empty($games)) {
    echo "No more games to fetch.\n";
    break;
  }
  insertGames($connect, $games);
  echo "Inserted " . count($games) . " games.\n";
  sleep(1);
}

$connect->close();
?>