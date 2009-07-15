<?php
/**
 * Repos Style log reader (c) 2007-2009 Staffan Olsson reposstyle.com
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

// Set the URL to the stylesheet, must be same host or absolute path from root
$xslt = '/repos-web/view/log.xsl';

// URL or path to repository, no trailing slash
// (note that the log viewer may bypass access control)
$repo = '@@Repository@@';
// For SVNParentPath set $repo to parent and this to true
$isParent = false;

// limit log length for performance reasons (users should run svn client for more entries)
$limit = 20;

// svn executable, command name in PATH or absolute path
$svn = 'svn';

// === configuration done, get parameters ===

isset($_REQUEST['target']) or die("Parameter 'target' is required");
$target = $_REQUEST['target'];

// === validate and run svn ===
if (strstr($repo,'@@')) die('The log script must be configured with a root URL');
is_numeric($limit) or die('The log script must be configured with a numeric limit');

if ($isParent) {
	isset($_REQUEST['base']) && strlen($_REQUEST['base'])>0
		or die("Parameter 'base' (Subversion 1.5 or later) required for SVNParentPath");
	$repo = $repo.'/'.$_REQUEST['base'];
}

$url = $repo . $target;

// command line, injection safe, svn must be in path, assumes utf-8 shell
$cmd = $svn.' log --xml --verbose --incremental --non-interactive';
$cmd .= ' --limit '.escapeshellarg($limit);
$cmd .= ' '.escapeshellarg($url);
$cmd .= ' 2>&1';

header('Content-Type: text/xml');
echo('<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="'.$xslt.'"?>
<log limit="'.$limit.'">
');
passthru($cmd);
echo('</log>
');
?>
