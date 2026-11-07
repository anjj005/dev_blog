<?php
// GitHub REST API client: GET /users/{username}/repos (public data, no login needed).
// Optional: set env var GITHUB_TOKEN to raise the rate limit (60/h unauthenticated).
// Cleans pasted input: removes @, github.com/ URLs, spaces and invisible characters (zero-width, non-breaking space).
function gh_clean($s){
  $s = preg_replace('/[\x{200B}-\x{200D}\x{2060}\x{FEFF}\x{00A0}\s]+/u', '', (string)$s);
  if (preg_match('#github\.com/([^/?\#]+)#i', $s, $m)) $s = $m[1];
  return ltrim($s, '@');
}
function gh_valid($u){ return (bool)preg_match('/^[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}$/i', $u); }

function gh_http($url){
  $h = ['User-Agent: DevBlog-App','Accept: application/vnd.github+json','X-GitHub-Api-Version: 2022-11-28'];
  $t = trim((string)getenv('GITHUB_TOKEN'));
  if ($t !== '') $h[] = 'Authorization: Bearer ' . $t;
  if (function_exists('curl_init')) {
    $c = curl_init($url);
    $verify = true;
    if ($ca = ini_get('curl.cainfo') ?: ini_get('openssl.cafile')) {
      curl_setopt($c, CURLOPT_CAINFO, $ca);
    }
    curl_setopt_array($c, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => $h,
      CURLOPT_TIMEOUT => 12,
      CURLOPT_SSL_VERIFYPEER => $verify,
      CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $b = curl_exec($c);
    $er = curl_error($c);
    if ($b === false && $er && str_contains($er, 'SSL certificate') && str_contains($er, 'unable to get local issuer certificate')) {
      curl_setopt_array($c, [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
      ]);
      $b = curl_exec($c);
      $er = curl_error($c);
    }
    $code = (int)curl_getinfo($c, CURLINFO_RESPONSE_CODE);
    curl_close($c);
    return [$code, $b, $er];
  }
  $ctx = stream_context_create(['http'=>['header'=>implode("\r\n",$h),'timeout'=>12,'ignore_errors'=>true]]);
  $b = @file_get_contents($url, false, $ctx); $code = 0;
  if (!empty($http_response_header[0]) && preg_match('/ (\d{3})/', $http_response_header[0], $m)) $code = (int)$m[1];
  return [$code, $b, $b === false ? (error_get_last()['message'] ?? 'request failed') : ''];
}

// Returns [repos[], errorMessage|null]. Results are cached 10 minutes in SQLite.
function gh_repos($db, $user){
  $key = strtolower($user);
  $st = $db->prepare("SELECT json,fetched FROM gh_cache WHERE username=?"); $st->execute([$key]); $c = $st->fetch();
  if ($c && time() - $c['fetched'] < 600) return [json_decode($c['json'], true), null];

  [$code, $body, $why] = gh_http('https://api.github.com/users/'.rawurlencode($user).'/repos?per_page=100&sort=updated&type=owner');
  $data = $code === 200 ? json_decode($body, true) : null;
  if (is_array($data)) {
    $repos = [];
    foreach ($data as $r) {
      if (!empty($r['fork'])) continue; // show original work only
      $repos[] = ['name'=>$r['name'], 'description'=>$r['description'], 'language'=>$r['language'],
                  'stars'=>(int)$r['stargazers_count'], 'forks'=>(int)$r['forks_count'],
                  'url'=>$r['html_url'], 'updated'=>$r['updated_at']];
    }
    $db->prepare("INSERT OR REPLACE INTO gh_cache(username,json,fetched) VALUES(?,?,?)")->execute([$key, json_encode($repos), time()]);
    return [$repos, null];
  }
  $err = $code === 404 ? 'GitHub user not found.'
       : (in_array($code, [403,429]) ? 'GitHub rate limit reached. Try again later, or set a GITHUB_TOKEN.' : 'Could not reach GitHub.'.($why ? ' Details: '.$why : ''));
  if ($c) return [json_decode($c['json'], true), $err.' Showing cached data.'];
  return [[], $err];
}
