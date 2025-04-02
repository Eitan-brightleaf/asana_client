<?php

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId     = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$tokenPath = __DIR__ . '/token.json';
$tokenData = json_decode(file_get_contents($tokenPath), true);

$asanaClient = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenData);

try {
    $tasks = $asanaClient->tasks()->getTasksByProject($_GET['project'], [
            'opt_fields' => 'name',
            'limit' => 100,
        ]);
    echo '<ol>';
    foreach ($tasks as $task) {
        echo '<li><a href="viewTask.php?task=' . $task['gid'] . '">' . $task['name'] . '</a></li>';
    }
    echo '</ol>';
    $project = $asanaClient->projects()->getProject($_GET['project'], ['opt_fields' => 'workspace.gid']);
    $workspace = $project['workspace']['gid'];
    echo '<a href="projects.php?workspace=' . $workspace . '">Back to projects</a>';
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
