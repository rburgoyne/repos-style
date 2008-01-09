<?php
/**
 * Repos Style log reader (c) 2007 Staffan Olsson www.reposstyle.com
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

// === Print svn log --xml to response ===
$repo = '@@Repository@@'; // repository root, no trailing slash
$limit = 20; // limit log length for performance reasons, advice users to run svn client for more entries 

// === configuration done ===
if (strstr($repo,'@@')) die('The log script must be configured with a root URL');
is_numeric($limit) or die('The log script must be configured with a numeric limit');

isset($_REQUEST['target']) or die("Parameter 'target' is required");
$url = $repo . $_REQUEST['target'];

// command line, injection safe
$cmd = 'svn log --xml --verbose --incremental --non-interactive';
$cmd .= ' --limit '.escapeshellarg($limit);
$cmd .= ' '.escapeshellarg($url);
$cmd .= ' 2>&1';

header('Content-Type: text/xml');
echo('<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="/repos-web/view/log.xsl"?>
<log limit="'.$limit.'">
');
passthru($cmd);
echo('</log>
');
?>
