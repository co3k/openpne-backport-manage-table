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

use Goutte\Client;

class Issue
{
    public $id, $tracker, $subject, $version, $status, $priority;

    public $backports = array();

    public function __construct($crawler)
    {
        $this->id = $crawler->filter('id')->text();
        $this->tracker = $crawler->filter('tracker')->attr('name');
        $this->subject = $crawler->filter('subject')->text();

        try {
            $this->version = $crawler->filter('fixed_version')->attr('name');
        } catch (Exception $e) {
        }

        $this->status = $crawler->filter('status')->attr('name');
        $this->priority = $crawler->filter('priority')->attr('name');
    }

    public function detectBackports()
    {
        $this->detectBackportsFromRelation();

        sleep(1);  // ... because backport detection needs many times API requests

        return count($this->backports);
    }

    private function detectBackportsFromRelation()
    {
        $client = new Client();
        $crawler = $client->request('GET', REDMINE_BASE_URL.'issues/'.$this->id.'.xml?include=relations');

        $nodes = $crawler->filter('relations relation');
        foreach ($nodes as $node) {
            $crawler = $client->request('GET', REDMINE_BASE_URL.'issues/'.$node->getAttribute('issue_id').'.xml');
            $issue = new Issue($crawler);
            if ('Backport（バックポート）' !== $issue->tracker) {
                continue;
            }

            $this->backports[get_major_version_from_version_string($issue->version)] = $issue;
        }
    }
}

function detect_all_versions()
{
    $results = array();

    $client = new Client();
    $crawler = $client->request('GET', REDMINE_BASE_URL.'projects/'.REDMINE_PROJECT_ID.'/roadmap?completed=1');

    $nodes = $crawler->filter('h3.version a[href]');
    foreach ($nodes as $node) {
        $results[$node->textContent] = $node->getAttribute('href');
    }

    array_key_sort_by_version($results);

    return $results;
}

function get_major_version_from_version_string($full_version)
{
    return preg_filter('/^(?:OpenPNE[\s-]*|)([0-9]+\.[0-9]+)\.?.*$/', '$1', $full_version);
}

function array_key_sort_by_version(&$versions)
{
    uksort($versions, function ($a, $b) {
        return version_compare($a, $b);
    });
}

function get_end_of_life_versions()
{
    return array(
        '3.1', '3.2', '3.3', '3.5',
    );
}

function get_supported_major_version_list($versions)
{
    $results = array();

    foreach ($versions as $k => $v) {
        $major = get_major_version_from_version_string($k);
        if (!in_array($major, get_end_of_life_versions())) {
            $results[] = $major;
        }
    }

    $results = array_unique($results);

    return $results;
}

function get_issues_from_specified_version_url($url)
{
    $results = array();

    $client = new Client();
    $crawler = $client->request('GET', REDMINE_BASE_URL.$url);
    $nodes = $crawler->filter('table.related-issues tr > td:last-child > a');

    $i = 0;

    foreach ($nodes as $node) {
        if ($i && 0 == ($i % 20)) {
            sleep(1);
        }

        $url = REDMINE_BASE_URL.$node->getAttribute('href').'.xml';
        $crawler = $client->request('GET', $url);

        $results[] = new Issue($crawler);

        $i++;
    }

    return $results;
}

function get_issues_of_development_versions($versions)
{
    $results = array();

    foreach ($versions as $k => $v) {
        $major = get_major_version_from_version_string($k);
        if (in_array($major, get_end_of_life_versions())) {
            continue;
        }

        if (0 == ($major * 10 % 2)) {
            continue;
        }

        $results = array_merge($results, get_issues_from_specified_version_url($v));
    }

    return $results;
}

function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function exclude_translation($string)
{
    return preg_replace('/（[^）]+）/u', '', $string);
}
