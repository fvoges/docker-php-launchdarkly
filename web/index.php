<?php
require_once 'vendor/autoload.php';

if (!isset($_ENV['LD_SDK_KEY'])) {
  print "Missing API key";
  die();
}

$ld_sdk_key         = $_ENV['LD_SDK_KEY'];
$targeted_flag_key  = 'targeted_flag';
$extra_flag_key     = 'extra_content_flag';

if (isset($_POST['uname']) && $_POST['uname'] != '') {
  $uname = $_POST['uname'];
  $ld_context_key = $uname;
} else {
  $uname = '';
  $ld_context_key = 'anonymous';
}

$twig_loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($twig_loader);

$ld_client = new LaunchDarkly\LDClient($ld_sdk_key);

if ($ld_context_key == 'anonymous') {
  $ld_context = LaunchDarkly\LDContext::builder($ld_context_key)
  ->anonymous(true)
  ->build();
} else {
  $ld_context = LaunchDarkly\LDContext::builder($ld_context_key)
  ->name($uname)
  ->build();
}

$extra_flag_value     = $ld_client->variation($extra_flag_key, $ld_context, false);
$targeted_flag_value  = $ld_client->variation($targeted_flag_key, $ld_context, false);

// $extra_flag_value_str = $extra_flag_value ? 'true' : 'false';
// echo "*** Feature flag 'extra_content_flag' is {$extra_flag_value_str} for this context<br />";
// $targeted_flag_value_str = $targeted_flag_value ? 'true' : 'false';
// echo "*** Feature flag 'targeted_flag' is {$targeted_flag_value_str} for this context<br />";
// $new_login_flag_value_str = $new_login_flag_value ? 'true' : 'false';
// echo "*** Feature flag 'new_login_flag' is {$new_login_flag_value_str} for this context<br />";

$index_template_params = [
  'debug'                    => true,
  'uname'                    => $uname,
  'targeted_content_enabled' => $targeted_flag_value,
  'extra_content_enabled'    => $extra_flag_value,
  'extra_content'            => "Some extra content controlled by a flag",
];

echo $twig->render('index.html.twig', $index_template_params);

?>