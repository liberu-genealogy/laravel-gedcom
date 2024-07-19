    public static function exportDataProvider()
    {
        return [
            'individuals' => [
                ['type' => 'individuals', 'data' => ['name' => 'John Doe']],
                "0 @I1@ INDI\n1 NAME John Doe\n"
            ],
            'families' => [
                ['type' => 'families', 'data' => ['id' => 'F1']],
                "0 @F1@ FAM\n"
            ],
            'notes' => [
                ['type' => 'notes', 'data' => ['content' => 'Note for individual']],
                "0 @N1@ NOTE Note for individual\n"
            ],
            'media' => [
                ['type' => 'media_objects', 'data' => ['title' => 'Photo of John Doe']],
                "0 @M1@ OBJE\n1 TITL Photo of John Doe\n"
            ],
        ];
    }