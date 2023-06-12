use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
//    $rectorConfig->sets([LevelSetList::UP_TO_PHP_82])
$rectorConfig->import(LevelSetList::UP_TO_PHP_82);
    $rectorConfig->paths([__DIR__ . '/src']);
};
