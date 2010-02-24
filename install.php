<?php
  defined('SYSPATH') OR die('No direct access allowed.');

  $config_error = null;

  if ( ! file_exists( MODPATH ) ) {
    mkdir( MODPATH );
  }

  $local_config_dir = LOCALPATH.'config/';

  if ( ! file_exists( LOCALPATH ) ) {
    mkdir( LOCALPATH );
  }

  if ( ! file_exists( $local_config_dir ) ) {
    mkdir( $local_config_dir );
  }

  if (!empty($_POST['hostname'])) {
    $config_error = null;
    
    if (preg_match('/[^A-Za-z0-9\-\.]/', $_POST['hostname'])) {
      $config_error = 'Did you really mean to set hostname to '
                      . htmlentities($_POST['hostname']) . '? If so, please '
                      . 'copy application/config/kohana.php to local/config/'
                      . 'and set $config[\'site_domain\'] manually.';
    }
    else {
      $fixed_hostname = trim($_POST['hostname'], '/') . '/';
      $config_lines = "<?php\n\n"
                      .'$config[\'site_domain\'] = \'' . $fixed_hostname . '\';';
      $fh = fopen( $local_config_dir . 'kohana.php', 'wb' );
      fwrite( $fh, $config_lines );
    }
    
    if ( ! file_exists( APPPATH.'logs' ) ) {
      mkdir( APPPATH.'logs' );
    }
    
    if ( ! isset( $config_error ) ) {
      echo '<h1>Installation Complete</h1>';
      echo '<p>Congratulations!  EmeraldView installation appears successful. '
           . 'If your Greenstone demo collection is in place, you may now '
           . 'reload the page to see EmeraldView in action.  See '
           . '<a href="http://yitznewton.org/emeraldview/index.php/Customization">Customization</a> '
           . 'for more options.</p>';
      exit;
    }
  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title>EmeraldView Installation</title>

<style type="text/css">
body { width: 42em; margin: 0 auto; font-family: sans-serif; font-size: 90%; }

#tests table { border-collapse: collapse; width: 100%; }
	#tests table th,
	#tests table td { padding: 0.2em 0.4em; text-align: left; vertical-align: top; }
	#tests table th { width: 12em; font-weight: normal; font-size: 1.2em; }
	#tests table tr:nth-child(odd) { background: #eee; }
	#tests table td.pass { color: #191; }
	#tests table td.fail { color: #911; }
		#tests #results { color: #fff; }
		#tests #results p { padding: 0.8em 0.4em; }
		#tests #results p.pass { background: #191; }
		#tests #results p.fail { background: #911; }

.config-form-row { padding: 0.75em 0; clear: both; }
.config-form-row label { float: left; width: 300px; padding-right: 1em; }
#config-form-errors { background: #ffff80; padding: 1em; }
</style>

</head>
<body>

<h1>Environment Tests</h1>

<p>The following tests have been run to determine if your environment is ready for EmeraldView. If any of the tests have failed, consult the <a href="http://docs.kohanaphp.com/installation">Kohana documentation</a> for more information on how to correct the problem.</p>

<div id="tests">
<?php $failed = FALSE ?>
<table cellspacing="0">
<tr>
<th>PHP Version</th>
<?php if (version_compare(PHP_VERSION, '5.2', '>=')): ?>
<td class="pass"><?php echo PHP_VERSION ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">Kohana requires PHP 5.2 or newer, this version is <?php echo PHP_VERSION ?>.</td>
<?php endif ?>
</tr>
<tr>
<th>System Directory</th>
<?php if (is_dir(SYSPATH) AND is_file(SYSPATH.'core/Bootstrap'.EXT)): ?>
<td class="pass"><?php echo SYSPATH ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured <code>system</code> directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>
<tr>
<th>System Directory Writable</th>
<?php if (is_writable(SYSPATH)): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured <code>system</code> directory is not writable.</td>
<?php endif ?>
</tr>
<tr>
<th>Application Directory</th>
<?php if (is_dir(APPPATH) AND is_file(APPPATH.'config/kohana'.EXT) AND is_file(APPPATH.'config/emeraldview.yml')): ?>
<td class="pass"><?php echo APPPATH ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured <code>application</code> directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>
<tr>
<th>Modules Directory</th>
<?php if (is_dir(MODPATH)): ?>
<td class="pass"><?php echo MODPATH ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured <code>modules</code> directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>
<tr>
<th>PCRE UTF-8</th>
<?php if ( !function_exists('preg_match')): $failed = TRUE ?>
<td class="fail"><a href="http://php.net/pcre">PCRE</a> support is missing.</td>
<?php elseif ( ! @preg_match('/^.$/u', 'ñ')): $failed = TRUE ?>
<td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>
<?php elseif ( ! @preg_match('/^\pL$/u', 'ñ')): $failed = TRUE ?>
<td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.</td>
<?php else: ?>
<td class="pass">Pass</td>
<?php endif ?>
</tr>
<tr>
<th>Reflection Enabled</th>
<?php if (class_exists('ReflectionClass')): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">PHP <a href="http://www.php.net/reflection">reflection</a> is either not loaded or not compiled in.</td>
<?php endif ?>
</tr>
<tr>
<th>Filters Enabled</th>
<?php if (function_exists('filter_list')): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">The <a href="http://www.php.net/filter">filter</a> extension is either not loaded or not compiled in.</td>
<?php endif ?>
</tr>
<tr>
<th>Iconv Extension Loaded</th>
<?php if (extension_loaded('iconv')): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">The <a href="http://php.net/iconv">iconv</a> extension is not loaded.</td>
<?php endif ?>
<tr>
<th>PDO SQLite Extension loaded</th>
<?php if (extension_loaded('pdo_sqlite')): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">The <a href="http://php.net/manual/en/ref.pdo-sqlite.php">pdo_sqlite</a> extension is not loaded.</td>
<?php endif ?>
</tr>

<tr>
<th>SPL Enabled</th>
<?php if (function_exists('spl_autoload_register')): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail"><a href="http://php.net/spl">SPL</a> is not enabled.</td>
<?php endif ?>
</tr>

<?php if (extension_loaded('mbstring')): ?>
<tr>
<th>Mbstring Not Overloaded</th>
<?php if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING): $failed = TRUE ?>
<td class="fail">The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP's native string functions.</td>
<?php else: ?>
<td class="pass">Pass</td>
</tr>
<?php endif ?>
<?php else: // check for utf8_[en|de]code when mbstring is not available ?>
<tr>
<th>XML support</th>
<?php if ( ! function_exists('utf8_encode')): $failed = TRUE ?>
<td class="fail">PHP is compiled without <a href="http://php.net/xml">XML</a> support, thus lacking support for <code>utf8_encode()</code>/<code>utf8_decode()</code>.</td>
<?php else: ?>
<td class="pass">Pass</td>
<?php endif ?>
</tr>
<?php endif ?>
<tr>
<th>URI Determination</th>
<?php if (isset($_SERVER['REQUEST_URI']) OR isset($_SERVER['PHP_SELF'])): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code> or <code>$_SERVER['PHP_SELF']</code> is available.</td>
<?php endif ?>
</tr>

</table>

<div id="results">
<?php if ($failed === TRUE): ?>
<p class="fail">EmeraldView will not work correctly with your environment.</p>
<?php elseif ($config_error): ?>
<p class="fail">There was an error in the form below:</p>
<?php else: ?>
<p class="pass">Your environment passed all requirements.</p>
<?php endif ?>
</div>

</div>

<?php if (!$failed): ?>
<h2>Now, to set up your installation:</h2>

<?php if ($config_error): ?>
  <div id="config-form-errors"><?php echo $config_error ?></div>
<?php endif; ?>

<form id="config-form" method="post">
  <div class="config-form-row">
    <label for="config-domain">Hostname for your installation (e.g. <code>emeraldview.example.org</code>)</label>
    <input type="text" name="hostname" value="<?php echo isset($_POST['hostname']) ? $_POST['hostname'] : $_SERVER['SERVER_NAME'] ?>" />
  </div>
  <div class="config-form-row">
    <input type="submit" value="Submit" />
  </div>
</form>
<?php endif; ?>

</body>
</html>
