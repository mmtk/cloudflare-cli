<?php

date_default_timezone_set('UTC');
set_time_limit(0);

require 'vendor/autoload.php';

use Silex\Application;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console as Console;
use Guzzle\GuzzleServiceProvider;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;
use Knp\Provider\ConsoleServiceProvider;

$email = getenv('CF_USER');
$tkn   = getenv('CF_TOKEN');

if (empty($email) or empty($tkn)) {
    $config_file = getenv('HOME') . '/.cloudflare.yaml';
    if (file_exists($config_file)) {
        $parsed = Yaml::parse($config_file);
        $email  = $parsed['email'];
        $tkn    = $parsed['token'];
    } else {
        echo("Missing configuration");
        exit(1);
    }
}

$app             = new Silex\Application();
$app['cf.user']  = $email;
$app['cf.token'] = $tkn;

$app->register(
    new GuzzleServiceProvider(),
    array(
        'guzzle.services' => __DIR__ . '/services.json',
    )
);

$app->register(
    new ConsoleServiceProvider(),
    array(
        'console.name'              => 'CloudFlare CLI',
        'console.version'           => '0.2',
        'console.project_directory' => __DIR__ . '/..'
    )
);

$console = $app['console'];
$console->add(new Cloudflare\Command\PurgeCacheCommand($app, 'cache:purge'));
$console->add(new Cloudflare\Command\DnsGetCommand($app, 'dns:list'));

// TODO
// $console->add(new Cloudflare\Command\DnsAddCommand($app, 'dns:add'));
// $console->add(new Cloudflare\Command\DnsUpdateCommand($app, 'dns:update'));
// $console->add(new Cloudflare\Command\DnsDeleteCommand($app, 'dns:delete'));
//
// $console->add(new Cloudflare\Command\VistorRecentCommand($app, 'visitor:recent'));
// $console->add(new Cloudflare\Command\VisitorScoreCommand($app, 'visitor:score'));
// $console->add(new Cloudflare\Command\VisitorWhitelistCommand($app, 'visitor:whitelist'));
// $console->add(new Cloudflare\Command\VisitorBanCommand($app, 'visitor:ban'));
// $console->add(new Cloudflare\Command\VisitorUnbanCommand($app, 'visitor:unban'));
//
// $console->add(new Cloudflare\Command\ZoneListCommand($app, 'zone:list'));
// $console->add(new Cloudflare\Command\ZoneGetCommand($app, 'zone:get'));
// security-level, cache-level, ipv6, devmode, rocket-loader, minify, mirage
// $console->add(new Cloudflare\Command\ZoneSetCommand($app, 'zone:set'));
//
// $console->add(new Cloudflare\Command\StatsGetCommand($app, 'stats:get'));

