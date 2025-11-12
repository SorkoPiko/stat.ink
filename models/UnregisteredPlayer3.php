<?php

/**
 * @copyright Copyright (C) 2025 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

use function array_map;
use function array_merge;
use function count;
use function explode;
use function implode;
use function preg_match;
use function trim;
use function vsprintf;

use const SORT_DESC;

/**
 * Virtual model representing an unregistered player identified by ref_id
 * Aggregates data from all battles where this player appeared
 */
final class UnregisteredPlayer3
{
    public ?string $ref_id = null;
    public ?string $name = null;
    public ?string $number = null;
    public int $total_battles = 0;
    public int $total_wins = 0;
    public int $total_disconnects = 0;
    public array $weapon_stats = [];
    public array $performance_stats = [];
    public array $lobby_stats = [];

    /**
     * Find an unregistered player by ref_id
     */
    public static function findByRefId(string $ref_id): ?self
    {
        Yii::info("Looking up unregistered player by ref_id: {$ref_id}", __METHOD__);

        $player = new self();
        $player->ref_id = $ref_id;

        // Get basic info from any battle_played_with record
        $basicInfo = (new Query())
            ->select(['name', 'number'])
            ->from('{{%battle3_played_with}}')
            ->where(['ref_id' => $ref_id])
            ->limit(1)
            ->one();

        if (!$basicInfo) {
            Yii::warning("No player found with ref_id: {$ref_id}", __METHOD__);
            return null;
        }

        Yii::info("Found player: {$basicInfo['name']}#{$basicInfo['number']} (ref_id: {$ref_id})", __METHOD__);

        $player->name = $basicInfo['name'];
        $player->number = $basicInfo['number'];

        // Load aggregated stats
        $player->loadAggregatedStats();

        return $player;
    }

    /**
     * Find an unregistered player by name and number (splashtag)
     */
    public static function findBySplashtag(string $name, string $number): ?self
    {
        Yii::info("Looking up unregistered player by splashtag: {$name}#{$number}", __METHOD__);

        // Get player info from battle_played_with record
        $playerInfo = (new Query())
            ->select(['name', 'number'])
            ->from('{{%battle_player3}}')
            ->innerJoin('{{%battle3}}', '{{%battle_player3}}.[[battle_id]] = {{%battle3}}.[[id]]')
            ->where([
                '{{%battle_player3}}.[[name]]' => $name,
                '{{%battle_player3}}.[[number]]' => $number,
                '{{%battle_player3}}.[[is_me]]' => false,
                '{{%battle3}}.[[is_deleted]]' => false,
            ])
            ->limit(1)
            ->one();

        if (!$playerInfo) {
            Yii::warning("No player found with splashtag: {$name}#{$number}", __METHOD__);
            return null;
        }

        Yii::info("Found player: {$name}#{$number} (ref_id: {$playerInfo['ref_id']})", __METHOD__);

        $refIdResult = (new Query())
            ->select(['ref_id' => 'calc_played_with3_id(:name, :number)'])
            ->addParams([':name' => $name, ':number' => $number])
            ->one();

        $player = new self();
        $player->ref_id = $refIdResult['ref_id'];
        $player->name = $playerInfo['name'];
        $player->number = $playerInfo['number'];

        // Load aggregated stats
        $player->loadAggregatedStats();

        return $player;
    }

    /**
     * Parse splashtag string and find player
     * Accepts formats: "username#1234" or "username #1234"
     */
    public static function findBySplashtagString(string $splashtag): ?self
    {
        Yii::info("Parsing splashtag string: '{$splashtag}'", __METHOD__);

        // Clean up the input and split by #
        $splashtag = trim($splashtag);
        $parts = explode('#', $splashtag, 2);
        
        if (count($parts) !== 2) {
            Yii::warning("Invalid splashtag format: '{$splashtag}' - expected format: 'username#1234'", __METHOD__);
            return null;
        }

        $name = trim($parts[0]);
        $number = trim($parts[1]);

        Yii::info("Parsed splashtag: name='{$name}', number='{$number}'", __METHOD__);

        // Basic validation
        if (empty($name) || empty($number)) {
            Yii::warning("Empty name or number after parsing: name='{$name}', number='{$number}'", __METHOD__);
            return null;
        }

        // Validate number format (should be numeric)
        if (!preg_match('/^\d+$/', $number)) {
            Yii::warning("Invalid number format: '{$number}' - must be numeric", __METHOD__);
            return null;
        }

        return self::findBySplashtag($name, $number);
    }

    /**
     * Load aggregated statistics for this unregistered player
     */
    private function loadAggregatedStats(): void
    {
        Yii::info("Loading aggregated stats for player: {$this->name}#{$this->number}", __METHOD__);

        // Get battle statistics - only from public battles
        $battleStats = (new Query())
            ->select([
                'battles' => 'COUNT(*)',
                'wins' => vsprintf('SUM(CASE %s END)', [
                    implode(' ', [
                        'WHEN {{%result3}}.[[is_win]] = {{%battle_player3}}.[[is_our_team]]',
                        'THEN 1 ELSE 0',
                    ]),
                ]),
                'disconnects' => 'SUM(CASE WHEN {{%battle_player3}}.[[is_disconnected]] THEN 1 ELSE 0 END)',
            ])
            ->from('{{%battle_player3}}')
            ->innerJoin('{{%battle3}}', '{{%battle_player3}}.[[battle_id]] = {{%battle3}}.[[id]]')
            ->innerJoin('{{%result3}}', '{{%battle3}}.[[result_id]] = {{%result3}}.[[id]]')
            ->andWhere([
                '{{%battle_player3}}.[[name]]' => $this->name,
                '{{%battle_player3}}.[[number]]' => $this->number,
                '{{%battle_player3}}.[[is_me]]' => false,
                '{{%battle3}}.[[is_deleted]]' => false,
                '{{%battle3}}.[[is_private]]' => false,
            ])
            ->one();

        $this->total_battles = (int)($battleStats['battles'] ?? 0);
        $this->total_wins = (int)($battleStats['wins'] ?? 0);
        $this->total_disconnects = (int)($battleStats['disconnects'] ?? 0);

        Yii::info("Battle stats loaded: battles={$this->total_battles}, wins={$this->total_wins}, disconnects={$this->total_disconnects}", __METHOD__);

        // Load weapon usage statistics
        $this->loadWeaponStats();

        // Load performance statistics  
        $this->loadPerformanceStats();

        // Load lobby statistics
        $this->loadLobbyStats();
    }

    /**
     * Load weapon usage statistics
     */
    private function loadWeaponStats(): void
    {
        Yii::info("Loading weapon stats for player: {$this->name}#{$this->number}", __METHOD__);

        $weaponQuery = (new Query())
            ->select([
                'weapon_id' => '{{%battle_player3}}.[[weapon_id]]',
                'weapon_name' => '{{%weapon3}}.[[name]]',
                'weapon_key' => '{{%weapon3}}.[[key]]',
                'battles' => 'COUNT(*)',
                'wins' => vsprintf('SUM(CASE %s END)', [
                    implode(' ', [
                        'WHEN {{%result3}}.[[is_win]] = {{%battle_player3}}.[[is_our_team]]',
                        'THEN 1 ELSE 0',
                    ]),
                ]),
                'avg_kill' => 'AVG({{%battle_player3}}.[[kill]])',
                'avg_death' => 'AVG({{%battle_player3}}.[[death]])',
                'avg_assist' => 'AVG({{%battle_player3}}.[[assist]])',
                'avg_special' => 'AVG({{%battle_player3}}.[[special]])',
                'avg_inked' => 'AVG({{%battle_player3}}.[[inked]])',
            ])
            ->from('{{%battle_player3}}')
            ->innerJoin('{{%battle3}}', '{{%battle_player3}}.[[battle_id]] = {{%battle3}}.[[id]]')
            ->innerJoin('{{%result3}}', '{{%battle3}}.[[result_id]] = {{%result3}}.[[id]]')
            ->innerJoin('{{%weapon3}}', '{{%battle_player3}}.[[weapon_id]] = {{%weapon3}}.[[id]]')
            ->andWhere([
                '{{%battle_player3}}.[[name]]' => $this->name,
                '{{%battle_player3}}.[[number]]' => $this->number,
                '{{%battle_player3}}.[[is_me]]' => false,
                '{{%battle3}}.[[is_deleted]]' => false,
                '{{%battle3}}.[[is_private]]' => false,
            ])
            ->andWhere(['not', ['{{%battle_player3}}.[[weapon_id]]' => null]])
            ->groupBy('{{%battle_player3}}.[[weapon_id]]')
            ->orderBy(['battles' => SORT_DESC])
            ->all();

        $this->weapon_stats = $weaponQuery;

        Yii::info("Loaded weapon stats: " . count($this->weapon_stats) . " weapons found", __METHOD__);
        if (!empty($this->weapon_stats)) {
            $topWeapon = $this->weapon_stats[0];
            Yii::info("Top weapon: {$topWeapon['weapon_name']} ({$topWeapon['battles']} battles)", __METHOD__);
        }
    }

    /**
     * Load overall performance statistics
     */
    private function loadPerformanceStats(): void
    {
        Yii::info("Loading performance stats for player: {$this->name}#{$this->number}", __METHOD__);

        $performanceQuery = (new Query())
            ->select([
                'avg_kill' => 'AVG({{%battle_player3}}.[[kill]])',
                'avg_death' => 'AVG({{%battle_player3}}.[[death]])',
                'avg_assist' => 'AVG({{%battle_player3}}.[[assist]])',
                'avg_special' => 'AVG({{%battle_player3}}.[[special]])',
                'avg_inked' => 'AVG({{%battle_player3}}.[[inked]])',
                'max_kill' => 'MAX({{%battle_player3}}.[[kill]])',
                'max_assist' => 'MAX({{%battle_player3}}.[[assist]])',
                'max_special' => 'MAX({{%battle_player3}}.[[special]])',
                'max_inked' => 'MAX({{%battle_player3}}.[[inked]])',
            ])
            ->from('{{%battle_player3}}')
            ->innerJoin('{{%battle3}}', '{{%battle_player3}}.[[battle_id]] = {{%battle3}}.[[id]]')
            ->andWhere([
                '{{%battle_player3}}.[[name]]' => $this->name,
                '{{%battle_player3}}.[[number]]' => $this->number,
                '{{%battle_player3}}.[[is_me]]' => false,
                '{{%battle3}}.[[is_deleted]]' => false,
                '{{%battle3}}.[[is_private]]' => false,
            ])
            ->andWhere(['not', ['{{%battle_player3}}.[[kill]]' => null]])
            ->one();

        $this->performance_stats = $performanceQuery ?: [];

        Yii::info("Performance stats loaded: " . json_encode($this->performance_stats), __METHOD__);
    }

    /**
     * Load lobby/mode statistics
     */
    private function loadLobbyStats(): void
    {
        Yii::info("Loading lobby stats for player: {$this->name}#{$this->number}", __METHOD__);

        $lobbyQuery = (new Query())
            ->select([
                'lobby_key' => '{{%lobby3}}.[[key]]',
                'lobby_name' => '{{%lobby3}}.[[name]]',
                'battles' => 'COUNT(*)',
                'wins' => vsprintf('SUM(CASE %s END)', [
                    implode(' ', [
                        'WHEN {{%result3}}.[[is_win]] = {{%battle_player3}}.[[is_our_team]]',
                        'THEN 1 ELSE 0',
                    ]),
                ]),
            ])
            ->from('{{%battle_player3}}')
            ->innerJoin('{{%battle3}}', '{{%battle_player3}}.[[battle_id]] = {{%battle3}}.[[id]]')
            ->innerJoin('{{%result3}}', '{{%battle3}}.[[result_id]] = {{%result3}}.[[id]]')
            ->innerJoin('{{%lobby3}}', '{{%battle3}}.[[lobby_id]] = {{%lobby3}}.[[id]]')
            ->andWhere([
                '{{%battle_player3}}.[[name]]' => $this->name,
                '{{%battle_player3}}.[[number]]' => $this->number,
                '{{%battle_player3}}.[[is_me]]' => false,
                '{{%battle3}}.[[is_deleted]]' => false,
                '{{%battle3}}.[[is_private]]' => false,
            ])
            ->groupBy('{{%lobby3}}.[[id]]')
            ->orderBy(['battles' => SORT_DESC])
            ->all();

        $this->lobby_stats = $lobbyQuery;

        Yii::info("Loaded lobby stats: " . count($this->lobby_stats) . " lobbies found", __METHOD__);
        foreach ($this->lobby_stats as $lobby) {
            Yii::info("Lobby {$lobby['lobby_name']}: {$lobby['battles']} battles, {$lobby['wins']} wins", __METHOD__);
        }
    }

    /**
     * Get win rate as percentage
     */
    public function getWinRate(): float
    {
        return $this->total_battles > 0 
            ? ($this->total_wins / $this->total_battles) * 100 
            : 0.0;
    }

    /**
     * Get disconnect rate as percentage
     */
    public function getDisconnectRate(): float
    {
        return $this->total_battles > 0 
            ? ($this->total_disconnects / $this->total_battles) * 100 
            : 0.0;
    }

    /**
     * Get most used weapon
     */
    public function getMostUsedWeapon(): ?array
    {
        return $this->weapon_stats[0] ?? null;
    }

    /**
     * Check if player has enough data to show meaningful stats
     */
    public function hasSignificantData(): bool
    {
        return $this->total_battles >= 5; // Require at least 5 battles for meaningful stats
    }

    /**
     * Get splashtag representation
     */
    public function getSplashtag(): string
    {
        return vsprintf('%s #%s', [
            $this->name ?? '???',
            $this->number ?? '????',
        ]);
    }

    /**
     * Debug method to check data availability in the database
     */
    public static function debugDataAvailability(): array
    {
        Yii::info("Checking data availability for unregistered players", __METHOD__);

        // Check if battle3_played_with table has data
        $playedWithCount = (new Query())
            ->from('{{%battle3_played_with}}')
            ->count();

        // Check if battle_player3 table has data for non-registered players
        $battlePlayerCount = (new Query())
            ->from('{{%battle_player3}}')
            ->where(['is_me' => false])
            ->count();

        // Check total battles
        $totalBattles = (new Query())
            ->from('{{%battle3}}')
            ->where(['is_deleted' => false])
            ->count();

        // Sample of available player names
        $samplePlayers = (new Query())
            ->select(['name', 'number', 'ref_id'])
            ->from('{{%battle3_played_with}}')
            ->limit(5)
            ->all();

        $debug = [
            'battle3_played_with_count' => $playedWithCount,
            'battle_player3_non_me_count' => $battlePlayerCount,
            'total_battles_count' => $totalBattles,
            'sample_players' => $samplePlayers,
        ];

        Yii::info("Data availability: " . json_encode($debug), __METHOD__);

        return $debug;
    }
}