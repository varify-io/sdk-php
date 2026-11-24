<?php

declare(strict_types=1);

use Varify\Core\Varify;
use Varify\Core\VarifyError;
use Varify\Core\VarifyOptions;

require __DIR__ . '/../../vendor/autoload.php';

function env(string $name): string
{
    $value = getenv($name);

    return $value === false ? '' : $value;
}

function html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES);
}

$experimentId = (int) env('VARIFY_EXPERIMENT_ID');
$variationId = (int) env('VARIFY_VARIATION_ID');
$scriptUrl = env('VARIFY_SCRIPT_URL');
$errors = [];

$options = VarifyOptions::forTeam(env('VARIFY_TEAM_ID'))->withOnError(
    static function (VarifyError $error) use (&$errors): void {
        $errors[] = "{$error->reason->value}: {$error->getMessage()}";
    },
);
if (env('VARIFY_CONFIG_URL') !== '') {
    $options = $options->withConfigUrl(env('VARIFY_CONFIG_URL'));
}

$varify = Varify::fromGlobals($options);
$assignment = $varify->assignment(max(1, $experimentId));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Varify plain PHP example</title>
    <?php if ($scriptUrl !== '') : ?>
        <script>
            window.varify = Object.assign(window.varify || {}, { iid: <?= (int) env('VARIFY_IID') ?> });
            window.dataLayer = window.dataLayer || [];
        </script>
        <script src="<?= html($scriptUrl) ?>"></script>
    <?php endif ?>
</head>
<body style="font-family: system-ui; margin: 32px">
<nav style="display: flex; gap: 16px; margin-bottom: 24px">
    <a href="/">Reload</a>
    <a href="/?varify-force=<?= $experimentId ?>-<?= $variationId ?>">Force variation</a>
    <a href="/?varify-force=<?= $experimentId ?>-0">Force original</a>
</nav>

<?php if ($assignment->is($variationId)) : ?>
    <button style="background: #16a34a; color: white">Variation <?= $variationId ?></button>
<?php else : ?>
    <button>Original</button>
<?php endif ?>

<pre><?= html(print_r([
    'experiment' => $experimentId,
    'variation' => $assignment->isOriginal() ? 'original' : $assignment->variationId,
    'source' => $assignment->source->value,
    'tracked' => $assignment->tracking->isEnabled(),
    'visitorId' => $varify->visitorId()->isNone() ? '(none, bot)' : $varify->visitorId()->value,
    'cookieWritten' => $varify->needsCookieWrite(),
    'errors' => $errors,
], true)) ?></pre>

<?= $varify->trackingSnippet() ?>
</body>
</html>
