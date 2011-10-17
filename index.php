<?php

/**
 * Copyright (c) 2011 Kousuke Ebihara
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
*/

set_time_limit(0);

require_once 'goutte.phar';
require_once 'lib.php';

define('REDMINE_BASE_URL', 'http://redmine.openpne.jp/');
define('REDMINE_PROJECT_ID', 'op3');

define('SITE_NAME', 'OpenPNE 3 Backport Manage Table');

if (isset($_GET['refresh'])) {
    apc_delete(sha1(__FILE__));

    $scheme = 'http'; // FIXME
    $host = $_SERVER['SERVER_NAME'];
    $base = str_replace('?refresh', '', $_SERVER['REQUEST_URI']); // FIXME

    $url = $scheme.'://'.$host.$base;
    header('Location: '.$url);
    exit;
}

$versions = detect_all_versions();
$majors = array_reverse(get_supported_major_version_list($versions));

$is_cached = true;
$cache_data = apc_fetch(sha1(__FILE__));
if ($cache_data) {
    $issues = unserialize($cache_data);
}

$cache_data = apc_fetch(sha1(__FILE__).'_ts');
if ($cache_data) {
    $cached_time = date('Y-m-d H:i:s', $cache_data);
}

if (empty($issues)) {
    $is_cached = false;
    $issues = get_issues_of_development_versions($versions);
}

if (empty($cached_time)) {
    $cached_time = 'Unknown';
}

$backport_majors = $majors;
array_shift($backport_majors);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo h(SITE_NAME) ?></title>
    <link rel="stylesheet" href="./tablesorter_skin/style.css" type="text/css" media="print, projection, screen">
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
    <script type="text/javascript" src="./jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="./lib.js"></script>
</head>
<body>
    <header id="page_header">
        <h1><?php echo h(SITE_NAME) ?></h1>
        <?php if ($is_cached): ?>
        <p>The followings are cached data. (Cached Time: <?php echo h($cached_time) ?>) <a href="?refresh">[refresh]</a></p>
        <?php endif; ?>
    </header>
    <section id="main_content">
        <table id="issues_filter">
            <tr>
                <th>Tracker</th>
                <td><select id="filter_tracker" multiple><option>Bug</option><option>Enhancement</option><option>Backport</option></select></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><select id="filter_status" multiple><option>New</option><option>Pending Fixing</option><option>Accepted</option><option>Pending Review</option><option>Pending Testing</option><option>Rejected</option><option>Fixed</option><option>Works for me</option><option>Invalid</option><option>Won't fix</option></select>
                </td>
            </tr>
            <tr>
                <th>Priority</th>
                <td><select id="filter_priority" multiple><option>Low</option><option>Normal</option><option>High</option><option>Urgent</option><option>Immediate</option></select></td>
            </tr>
            <tr>
                <th>3.6</th>
                <td><select id="filter_36" multiple><option>-</option><option>New</option><option>Pending Fixing</option><option>Accepted</option><option>Pending Review</option><option>Pending Testing</option><option>Rejected</option><option>Fixed</option><option>Works for me</option><option>Invalid</option><option>Won't fix</option></select>
                </td>
            </tr>
            <tr>
                <th>3.4</th>
                <td><select id="filter_34" multiple><option>-</option><option>New</option><option>Pending Fixing</option><option>Accepted</option><option>Pending Review</option><option>Pending Testing</option><option>Rejected</option><option>Fixed</option><option>Works for me</option><option>Invalid</option><option>Won't fix</option></select>
                </td>
            </tr>
            <tr>
                <th>3.0</th>
                <td><select id="filter_30" multiple><option>-</option><option>New</option><option>Pending Fixing</option><option>Accepted</option><option>Pending Review</option><option>Pending Testing</option><option>Rejected</option><option>Fixed</option><option>Works for me</option><option>Invalid</option><option>Won't fix</option></select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><button id="filter_button">Filter</button></td>
            </tr>
        </table>

        <?php if ($issues): ?>
            <table id="issues_table" class="tablesorter">
                <thead>
                    <tr>
                        <th class="id">#</th>
                        <th class="tracker">Tracker</th>
                        <th class="status">Status</th>
                        <th class="priority">Priority</th>
                        <th class="subject">Subject</th>
                        <th class="version">Version</th>
                        <?php foreach ($backport_majors as $major): ?>
                        <th class="backport"><?php echo h($major) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($issues as $issue): ?>
                    <?php $issue->detectBackports(); ?>
                    <tr>
                        <td><?php echo h($issue->id) ?></td>
                        <td><?php echo h(exclude_translation($issue->tracker)) ?></td>
                        <td><?php echo h(exclude_translation($issue->status)) ?></td>
                        <td><?php echo h(exclude_translation($issue->priority)) ?></td>
                        <td><a href="<?php echo h(REDMINE_BASE_URL) ?>issues/<?php echo urlencode($issue->id) ?>"><?php echo h($issue->subject) ?></a></td>
                        <td><?php echo h(exclude_translation($issue->version)) ?></td>
                        <?php foreach ($backport_majors as $major): ?>
                        <td>
                            <?php if (isset($issue->backports[$major])): ?>
                            <a href="<?php echo h(REDMINE_BASE_URL) ?>issues/<?php echo urlencode($issue->backports[$major]->id) ?>">
                            <?php echo h(exclude_translation($issue->backports[$major]->status)) ?>
                            </a>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>There are no issues to display here.</p>
            <h2>Why?</h2>
            <ul>
                <li>Your Redmine may have no issues</li>
                <li>This system can't fetch any data to display</li>
                <li>........</li>
            </ul>
        <?php endif; ?>
    </section>
</body>
</html>
<?php
if (!$is_cached) {
    apc_store(sha1(__FILE__), serialize($issues));
    apc_store(sha1(__FILE__).'_ts', time());
}
?>
