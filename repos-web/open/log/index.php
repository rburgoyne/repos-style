<?php
/**
 * Repos Style log reader (c) 2007-2008 Staffan Olsson reposstyle.com
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

// Set either repository root url OR the url to SVNParentPath for multi-repo
// accepts any subversion url that does not require authentication, including file:///
// no trailing slash
$repo = '@@Repository@@';
// OR set a parent url instead, corresponding to SVNParentPath, no trailing slash
$repoparent = '@@RepoParent@@';

// limit log length for performance reasons (users should run svn client for more entries)
$limit = 20;

// svn executable, command name in PATH or absolute path
$svn = 'svn';

// === configuration done, get parameters ===

isset($_REQUEST['target']) or die("Parameter 'target' is required");
$target = $_REQUEST['target'];

// Repo may be set as server environment variable
// This allows configuration without editing this file,
// as well as very flexible url resolution from mod_rewrite
if (strstr($repo,'@@') && isset($_SERVER['ReposRepo'])) $repo = $_SERVER['ReposRepo'];

// For multi-repo, try to resolve url to be used in svn command (it is not known by the xslt)
if (!strstr($repoparent,'@@') && strstr($repo,'@@')) {
	if (isset($_REQUEST['reponame'])) {
		$repo = $repoparent.'/'.$_REQUEST['reponame'];
	} else if (isset($_SERVER['HTTP_REFERER'])) {
		$referer = $_SERVER['HTTP_REFERER'];
		if (preg_match('/\/([^\/]+)'.preg_quote($target,'/').'\/?$/',$referer,$matches)) {
			$reponame = $matches[1];
			// make bookmarkable if possible
			if (isset($_SERVER['SCRIPT_URI'])) {
				header('Location: '.$_SERVER['SCRIPT_URI'].'?target='.rawurlencode($target).'&reponame='.$reponame);
				exit;
			}
			// not bookmarkable, continue with referer
			$repo = $repoparent.'/'.$reponame;
		}
	}
	if (strstr($repo,'@@')) die('Unable to resolve repository name for this request, reponame must be set');
}

// === validate and run svn ===
if (strstr($repo,'@@')) die('The log script must be configured with a root URL');
is_numeric($limit) or die('The log script must be configured with a numeric limit');

$url = $repo . $target;

// command line, injection safe, svn must be in path
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
