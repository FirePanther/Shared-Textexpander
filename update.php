#!/usr/bin/php
<?php
/**
 * @author			Suat Secmen
 * @copyright		FirePanther (http://firepanther.pro/)
 * @description		Share your Groups automatically
 * @date			2015
 */

### CONFIGURATIONS


// pick your username and password, your email address is for recovering your password
// after your first request it's not possible to change the password
// if you change your username a new user will be created (the old user still remains)
// you can change your email address and update or remove the groups, when your username and password are correct
// if you forgot your password or want to remove your old user please contact me: textexpander [at] suat [dot] be
$email = "";
$username = ""; // only use a-z, A-Z, 0-9
$password = "";
// you can find and share your snippets at http://suat.be/textexpander/USERNAME

// all groups you want to export comma separated; case sensitive
$groups = "";

// the file location (e.g. in Dropbox)
$file = "~/Dropbox/TextExpander/Settings.textexpander";

// you don't have to change that
$tmpDir = "/tmp";




### SCRIPT


// load config
$file = str_replace("~", $_SERVER["HOME"], $file);

$configDir = "$tmpDir/firepantherTextExpander.json";
if (is_file($configDir)) $config = @json_decode(file_get_contents($configDir), 1);
else $config = [
	"last" => 0,
	"hash" => []
];

// contains the still existing hashes
$hashes = [];

// don't run the script, if the file has not been changed
if (filemtime($file) <= $config["last"] && filemtime(__FILE__) <= $config["last"]) exit;

// load file
$source = file_get_contents($file);
$source = preg_replace("~(<array>)\s*(<dict>)~", '$1$2', $source);
$source = substr($source, strpos($source, "<array><dict>"));

// subdictionaries
$source = preg_replace("~<dict>(\s*<key>RTF</key>.*?)</dict>~s", "<fp:dict>$1</fp:dict>", $source);

// get dictionaries
preg_match_all("~<dict>(.*?)</dict>~s", $source, $matches);
$dicts = $matches[0];

// parse groups
if (!is_array($groups)) {
	if (empty($groups)) $groups = [];
	elseif (strpos($groups, ",") === false) $groups = [$groups];
	else $groups = explode(",", $groups);
}

// get all groups and upload them
foreach ($groups as $group) {
	$group = trim($group);
	$dict = findDict("name", $group);
	if ($dict !== false) {
		$snippetUUIDs = getSnippetUUIDs($dict);
		if ($snippetUUIDs !== false) {
			$snippets = [];
			foreach ($snippetUUIDs as $snippetUUID) {
				$snippets[] = findDict("uuidString", $snippetUUID);
			}
		}
		upload($group, $dict, $snippets);
	} else echo "Group $group couldn't be found.\n";
}

// remove locally removed groups from the server
/*foreach ($config["hash"] as $uuid => $hash) {
	if (!in_array($uuid, $hashes)) {
		echo post("remove", [
				"username" => $username,
				"password" => hash("crc32b", $password),
				"uuid" => $uuid
			])."\n";
	}
}*/
echo post("remove", [
		"username" => $username,
		"password" => hash("crc32b", $password),
		"except" => implode(",", $hashes)
	])."\n";
$config["hash"] = $hashes;

// update config
$config["last"] = time();
file_put_contents($configDir, json_encode($config));




### FUNCTIONS


/**
 * finds a dictionary by key and string
 */
function findDict($key, $string) {
	global $dicts;
	
	foreach ($dicts as $dict) {
		if (preg_match("~<key>".htmlspecialchars($key)."</key>\s*<string>".htmlspecialchars($string)."</string>~s", $dict)) return $dict;
	}
	return false;
}

/**
 * returns the snippet UUIDs
 */
function getSnippetUUIDs($dict) {
	if (preg_match("~<key>snippetUUIDs</key>(.*?)<key>~s", $dict, $strings)) {
		if (preg_match_all("~<string>(.*?)</string>~", $strings[1], $uuids)) {
			return $uuids[1];
		} else return [];
	} else return false;
}

/**
 * returns the string from a key
 */
function getStringFromKey($dict, $key, $default = null) {
	if (preg_match("~<key>".htmlspecialchars($key)."</key>\s*<string>(.*?)</string>~s", $dict, $string)) return $string[1];
	else return $default;
}

/**
 * update this group, if anything has changed since the last upload
 */
function upload($name, $dict, $snippets) {
	global $config, $email, $username, $password, $hashes;
	
	// for identification of the group (even if you rename it)
	$uuid = getStringFromKey($dict, "uuidString", $name);
	
	// xml header
	$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>groupInfo</key>
	<dict>';
	// some configs
	$xml .= "
		<key>expandAfterMode</key>
		<integer>".getStringFromKey($dict, "expandAfterMode", "0")."</integer>
		<key>groupName</key>
		<string>$name</string>
	</dict>
	<key>snippetsTE2</key>
	<array>";
	foreach ($snippets as $snippet) {
		$xml .= "
		$snippet";
	}
	$xml .= "	</array>
</dict>
</plist>
";
	// only upload, if something changed
	$hash = md5($xml);
	$hashes[$uuid] = $hash;
	if (!isset($config["hash"][$uuid]) || $config["hash"][$uuid] !== $hash) {
		// post data
		echo post("update", [
				"email" => $email,
				"username" => $username,
				"password" => hash("crc32b", $password),
				"uuid" => $uuid,
				"file" => $xml
			])."\n";
	}
}

function post($file, $postData) {
	$opts = [
		"http" => [
			"method" => "POST",
			"header" => "Content-type: application/x-www-form-urlencoded",
			"content" => is_array($postData) ? http_build_query($postData) : $postData
		]
	];
	$context  = stream_context_create($opts);
	return file_get_contents("http://suat.be/textexpander/a/".$file, false, $context);
}
