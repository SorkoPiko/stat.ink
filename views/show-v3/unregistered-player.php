<?php

/**
 * @copyright Copyright (C) 2025 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

use app\components\widgets\AdWidget;
use app\components\widgets\Icon;
use app\models\UnregisteredPlayer3;
use yii\helpers\Html;
use yii\web\View;

use function vsprintf;

/**
 * @var UnregisteredPlayer3 $player
 * @var View $this
 */

$formatter = Yii::$app->formatter;

$this->context->layout = 'main';

$title = Yii::t('app', 'Unregistered Player: {splashtag}', [
    'splashtag' => $player->getSplashtag(),
]);

$this->title = vsprintf('%s | %s', [
    Yii::$app->name,
    $title,
]);

?>
<div class="container-fluid">
  <div class="row">
    <div class="col-xs-12">
      <h1>
        <?= Icon::splatoon3() ?>
        <?= Html::encode($title) ?>
      </h1>
      <p class="text-muted">
        <?= Html::encode(
          Yii::t('app', 'Statistics for unregistered player based on {count} public battles', [
            'count' => $formatter->asInteger($player->total_battles),
          ])
        ) ?>
      </p>
    </div>
  </div>
  
  <div class="row">
    <div class="col-xs-12 col-sm-8 col-lg-9">
      
      <!-- Overview Stats -->
      <div class="row">
        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asInteger($player->total_battles) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Total Battles')) ?>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asPercent($player->getWinRate() / 100, 1) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Win %')) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?php
                $killRatio = $player->getKillRatio();
                if ($killRatio === null) {
                  echo '∞';
                } elseif ($killRatio === 0.0 && empty($player->performance_stats)) {
                  echo Html::encode(Yii::t('app', 'N/A'));
                } else {
                  echo $formatter->asDecimal($killRatio, 2);
                }
                ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Kill Ratio')) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asPercent($player->getDisconnectRate() / 100, 1) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Disconnect Rate')) ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Overview Stats -->
      <div class="row">
        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asDecimal($player->performance_stats['avg_kill'] ?? 0, 1) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Avg Kills')) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asDecimal($player->performance_stats['avg_death'] ?? 0, 1) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Avg Deaths')) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asInteger($player->getTotalKills()) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Total Kills')) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asInteger($player->getTotalDeaths()) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Total Deaths')) ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Extended Performance Stats -->
      <div class="row">
        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asDecimal($player->performance_stats['avg_assist'] ?? 0, 1) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Avg Assists')) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asDecimal($player->performance_stats['avg_special'] ?? 0, 1) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Avg Specials')) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asInteger($player->performance_stats['max_kill'] ?? 0) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Max Kills')) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-6 col-sm-3">
          <div class="panel panel-default text-center">
            <div class="panel-body">
              <div style="font-size: 2em; font-weight: bold;">
                <?= $formatter->asDecimal($player->performance_stats['avg_inked'] ?? 0, 0) ?>
              </div>
              <div class="text-muted">
                <?= Html::encode(Yii::t('app', 'Avg Inked')) ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Weapon Usage -->
      <?php if (!empty($player->weapon_stats)): ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">
              <?= Icon::s3Weapon(null) ?>
              <?= Html::encode(Yii::t('app', 'Weapon Usage')) ?>
            </h3>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
              <thead>
                <tr>
                  <th><?= Html::encode(Yii::t('app', 'Weapon')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Battles')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Win %')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Avg K')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Avg D')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Avg A')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Avg Inked')) ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice($player->weapon_stats, 0, 10) as $weapon): ?>
                  <tr>
                    <td>
                      <?= Icon::s3Weapon((string)($weapon['weapon_key'] ?? '')) ?>
                      <?= Html::encode((string)($weapon['weapon_name'] ?? 'Unknown Weapon')) ?>
                    </td>
                    <td class="text-center">
                      <?= $formatter->asInteger((int)($weapon['battles'] ?? 0)) ?>
                    </td>
                    <td class="text-center">
                      <?php
                      $battles = (int)($weapon['battles'] ?? 0);
                      $wins = (int)($weapon['wins'] ?? 0);
                      $winRate = $battles > 0 ? ($wins / $battles) * 100 : 0;
                      echo $formatter->asPercent($winRate / 100, 1);
                      ?>
                    </td>
                    <td class="text-center">
                      <?= $formatter->asDecimal((float)($weapon['avg_kill'] ?? 0), 1) ?>
                    </td>
                    <td class="text-center">
                      <?= $formatter->asDecimal((float)($weapon['avg_death'] ?? 0), 1) ?>
                    </td>
                    <td class="text-center">
                      <?= $formatter->asDecimal((float)($weapon['avg_assist'] ?? 0), 1) ?>
                    </td>
                    <td class="text-center">
                      <?= $formatter->asDecimal((float)($weapon['avg_inked'] ?? 0), 0) ?>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif ?>

      <!-- Lobby Stats -->
      <?php if (!empty($player->lobby_stats)): ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">
              <?= Icon::s3Lobby(null) ?>
              <?= Html::encode(Yii::t('app', 'Lobby Statistics')) ?>
            </h3>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
              <thead>
                <tr>
                  <th><?= Html::encode(Yii::t('app', 'Lobby')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Battles')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Wins')) ?></th>
                  <th class="text-center"><?= Html::encode(Yii::t('app', 'Win Rate')) ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($player->lobby_stats as $lobby): ?>
                  <tr>
                    <td>
                      <?= Icon::s3Lobby((string)($lobby['lobby_key'] ?? '')) ?>
                      <?= Html::encode((string)($lobby['lobby_name'] ?? 'Unknown Lobby')) ?>
                    </td>
                    <td class="text-center">
                      <?= $formatter->asInteger((int)($lobby['battles'] ?? 0)) ?>
                    </td>
                    <td class="text-center">
                      <?= $formatter->asInteger((int)($lobby['wins'] ?? 0)) ?>
                    </td>
                    <td class="text-center">
                      <?php
                      $battles = (int)($lobby['battles'] ?? 0);
                      $wins = (int)($lobby['wins'] ?? 0);
                      $winRate = $battles > 0 ? ($wins / $battles) * 100 : 0;
                      echo $formatter->asPercent($winRate / 100, 1);
                      ?>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif ?>

    </div>
    
    <div class="col-xs-12 col-sm-4 col-lg-3">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">
            <?= Icon::info() ?>
            <?= Html::encode(Yii::t('app', 'About This Data')) ?>
          </h3>
        </div>
        <div class="panel-body">
          <p class="small">
            <?= Html::encode(
              Yii::t('app', 'This data is aggregated from public battles where this unregistered player appeared. Only non-private battles are included to respect player privacy.')
            ) ?>
          </p>
          <p class="small">
            <?= Html::encode(
              Yii::t('app', 'Statistics may not be complete as they depend on registered users uploading battle data to stat.ink.')
            ) ?>
          </p>
        </div>
      </div>
      
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <?= Icon::search() ?>
            <?= Html::encode(Yii::t('app', 'Search Other Players')) ?>
          </h4>
        </div>
        <div class="panel-body">
          <p class="small">
            <?= Html::encode(Yii::t('app', 'Looking for stats of another unregistered player?')) ?>
          </p>
          <?= Html::a(
            implode(' ', [
              Icon::search(),
              Html::encode(Yii::t('app', 'Search by Splashtag')),
            ]),
            ['show-v3/unregistered-player-search'],
            ['class' => 'btn btn-sm btn-default']
          ) ?>
        </div>
      </div>
      
      <?= AdWidget::widget() ?>
    </div>
  </div>
</div>