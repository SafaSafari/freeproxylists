<?php
$page = 1;
$proxy = null;
$i = 0;
$ips = [];
if (file_exists('proxy.txt'))
    $proxies = explode(PHP_EOL, file_get_contents('proxy.txt'));
do {
    do {
        $html = get($proxy, $page);
        echo $proxy . '   ' . $page . '   ' . count($proxies) . PHP_EOL;
        file_put_contents('temp', $html . 'NEXT', FILE_APPEND);
        if (count($ips) === 0)
            if (count($proxies) === 0)
                exit('SHIT');
            else $proxy = trim($proxies[$i++]);
        $ips = filter($html);
    } while (count($ips) === 0);
    if (!isset($last))
        $last = lastpage($html);
    $page++;
    $proxies = array_merge($proxies, $ips);
    file_put_contents('proxy.txt', implode(PHP_EOL, $proxies));
} while ($page !== $last);
function get($proxy = null, $page = 1)
{
    $header = 'Referer: http://www.freeproxylists.net/&&&Upgrade-Insecure-Requests: 1&&&User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36&&&Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9&&&Accept-Encoding: gzip, deflate&&&Accept-Language: en-US,en;q=0.9';
    $ch = curl_init('http://www.freeproxylists.net/' . ($page > 1 ? '?page=' . $page : null));
    $options[CURLOPT_FOLLOWLOCATION] = true;
    $options[CURLOPT_RETURNTRANSFER] = true;
    $options[CURLOPT_ACCEPT_ENCODING] = 'gzip, deflate';
    if (!is_null($proxy)) {
        $e = explode('#', $proxy);
        $options[CURLOPT_PROXY] = $e[0];
        $options[CURLOPT_PROXYTYPE] = str_ireplace(['https', 'http', 'socks4a', 'socks5', 'socks4'], [2, 0, 6, 5, 4], $e[1]);
    }
    $options[CURLOPT_HTTPHEADER] = explode('&&&', $header);
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
function filter($html)
{
    $ips = [];
    try {
        preg_match_all('/IPDecode\(\"(.*)\"\)\<\/script\>\<\/td\>\<td align=\"center\"\>(.*)\<\/td\>\<td align=\"center\"\>(.*)\<\/td\>/U', $html, $arr);
        foreach ($arr[1] as $i => $encoded) {
            preg_match('/\"\>(.*)\<\/a\>/', rawurldecode($encoded), $decode);
            $ips[] = $decode[1] . ':' . $arr[2][$i] . '#' . $arr[3][$i];
        }
        if (count($ips) > 0)
            return $ips;
        return [];
    } catch (Exception $e) {
        unset($e);
    }
}
function lastpage($html)
{
    preg_match_all('/\<a href\=\"\.\/\?page\=\d*\"\>(\d*)\<\/a\>/U', $html, $result);
    if (isset($result[0][0]))
        return array_reverse($result[1])[0];
}
