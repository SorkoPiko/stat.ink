# Unregistered Player Stats Feature Implementation

## Overview

This implementation adds functionality to view aggregated statistics for unregistered players in Splatoon 3 battles. The system can now show performance data for players who don't have accounts on stat.ink but appear in battles uploaded by registered users.

## Key Features

### 1. Player Statistics Aggregation
- **Comprehensive Data**: Aggregates win rates, weapon usage, performance metrics, and lobby statistics
- **Privacy-Conscious**: Only uses data from public (non-private) battles
- **Minimum Threshold**: Requires at least 5 battles for meaningful statistics
- **Cross-User Data**: Combines data from multiple registered users who played with the same unregistered player

### 2. Accessible URLs
- Clean URL structure: `/unregistered-player-v3/{ref_id}`
- Example: `/unregistered-player-v3/a1b2c3d4e5f6789012345678901234567890abcd`
- **Splashtag search**: `/unregistered-player-v3/by-splashtag/Username%231234`
- **Search page**: `/unregistered-player-v3/search` - Interactive form to search by splashtag
- Direct linking from player names in battle displays

### 3. Rich Statistics Display
- **Overview Stats**: Total battles, win rate, disconnect rate
- **Performance Metrics**: Average kills, deaths, assists, specials, and inked territory
- **Weapon Usage**: Most used weapons with individual performance stats
- **Lobby Statistics**: Performance across different game modes

## Implementation Details

### Files Created/Modified

#### New Files:
1. **`/models/UnregisteredPlayer3.php`** - Core model for aggregating player data
2. **`/actions/show/v3/UnregisteredPlayerAction.php`** - Controller action for showing stats
3. **`/actions/show/v3/UnregisteredPlayerSearchAction.php`** - Controller action for search functionality
4. **`/views/show-v3/unregistered-player.php`** - View template for stats display
5. **`/views/show-v3/unregistered-player-search.php`** - View template for search form

#### Modified Files:
1. **`/controllers/ShowV3Controller.php`** - Added new action
2. **`/views/show-v3/battle/players/player/name.php`** - Added links to player stats
3. **`/config/web/urlRules/spl3.php`** - Added URL routing rule

### Database Usage

The implementation leverages existing database tables:
- **`battle3_played_with`** - For player identification via ref_id
- **`battle_player3`** - For individual battle performance data
- **`battle3`** - For battle context and privacy filtering
- **`weapon3`**, **`lobby3`**, **`result3`** - For descriptive data

### Privacy and Data Protection

- **Public Data Only**: Only aggregates data from battles marked as non-private
- **No Personal Information**: Uses only gameplay statistics, not personal data
- **Minimum Data Requirement**: Requires sufficient data before showing statistics
- **Transparent Display**: Clearly indicates the data source and limitations

### Performance Considerations

- **Efficient Queries**: Uses grouped SQL queries to minimize database load
- **Cached Results**: Statistics are computed on-demand (could be cached if needed)
- **Limited Display**: Shows only top 10 weapons to avoid performance issues

## Usage

### For Players
1. **Discovery**: Click on any unregistered player name in battle results
2. **Search by Splashtag**: Visit `/unregistered-player-v3/search` and enter "Username#1234"
3. **Direct Access**: Use the URL with a known ref_id or splashtag
4. **Statistics View**: See comprehensive performance data

### For Developers
The `UnregisteredPlayer3` model provides a clean interface:

```php
// Find player by ref_id
$player = UnregisteredPlayer3::findByRefId($refId);

// Find player by splashtag
$player = UnregisteredPlayer3::findBySplashtag('Username', '1234');

// Find player by splashtag string
$player = UnregisteredPlayer3::findBySpashtagString('Username#1234');

// Access statistics
$winRate = $player->getWinRate();
$mostUsedWeapon = $player->getMostUsedWeapon();
$hasEnoughData = $player->hasSignificantData();
```

## Security Features

- **Input Validation**: ref_id format is strictly validated (32-character hex)
- **Data Sanitization**: All output is properly escaped
- **Privacy Compliance**: Respects battle privacy settings
- **Access Control**: No authentication required (public data only)

## Benefits

1. **Enhanced Community Features**: Allows tracking of frequently encountered players
2. **Performance Insights**: Helps players understand opponents and teammates
3. **Data Utilization**: Makes better use of existing battle data
4. **Privacy Respectful**: Balances transparency with privacy concerns

## Future Enhancements

Potential improvements could include:
- **Caching Layer**: Cache computed statistics for better performance
- **Time-based Filtering**: Show statistics for specific time periods
- **Comparison Tools**: Compare multiple unregistered players
- **Export Functionality**: Allow downloading statistics as JSON/CSV

## Technical Notes

- **Ref ID Generation**: Uses existing `calc_played_with3_id()` function
- **Data Freshness**: Statistics update automatically as new battles are added
- **Cross-Platform**: Works across different lobby types and game modes
- **Scalable Design**: Can handle large numbers of unregistered players efficiently

This implementation provides a valuable community feature while maintaining stat.ink's commitment to data privacy and performance.